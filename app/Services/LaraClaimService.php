<?php

namespace App\Services;

use App\Mail\ClaimEscalationMail;
use App\Mail\ClaimOtpMail;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LaraClaimService
{
    public function handle(string $message, array $session): array
    {
        $step = $session['claim_step'] ?? 'intro';

        return match ($step) {
            'intro'         => $this->intro($session),
            'awaiting_email' => $this->handleEmail($message, $session),
            'awaiting_otp'  => $this->handleOtp($message, $session),
            default         => $this->intro($session),
        };
    }

    private function intro(array $session): array
    {
        $provider = Provider::with(['activities', 'user'])->find($session['claim_provider_id']);

        if (!$provider) {
            return ['reply' => 'Não encontrei essa página. Tente novamente ou acesse envago.live.', 'session' => $session];
        }

        $count = $provider->activities->count();
        $trips = $count === 1 ? '1 passeio' : "{$count} passeios";

        $reply = "Oi! 👋 Sua página já está no Envago com {$trips} cadastrados.\n\n"
            . "Ao reivindicar, você passa a receber reservas online, controla seus preços e fotos, e a gente roda mais anúncios pra você. É de graça — só paga 10% sobre o que vender.\n\n"
            . "Qual é o e-mail cadastrado na sua conta?";

        $session['claim_step'] = 'awaiting_email';

        return ['reply' => $reply, 'session' => $session];
    }

    private function handleEmail(string $message, array $session): array
    {
        $email    = trim(strtolower($message));
        $provider = Provider::with('user')->find($session['claim_provider_id']);

        if (!$provider || !$provider->user) {
            return $this->escalate($session, $email, 'Provider or user not found during email step.');
        }

        $onFile = strtolower($provider->user->email);

        if ($email !== $onFile) {
            $session['claim_email_mismatches'] = ($session['claim_email_mismatches'] ?? 0) + 1;

            if ($session['claim_email_mismatches'] >= 2) {
                return $this->escalate($session, $email, "Email mismatch after 2 attempts. Tried: {$email}");
            }

            return [
                'reply'   => "Esse e-mail não corresponde ao cadastro. Pode tentar outro?",
                'session' => $session,
            ];
        }

        // Match — generate OTP
        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('claim_verifications')->where('provider_id', $provider->id)->delete();
        DB::table('claim_verifications')->insert([
            'provider_id' => $provider->id,
            'email'       => $email,
            'otp'         => $otp,
            'expires_at'  => now()->addMinutes(10),
            'attempts'    => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        try {
            Mail::to($email)->send(new ClaimOtpMail($otp, $provider->business_name));
        } catch (\Throwable $e) {
            Log::error('claim: OTP email failed', ['error' => $e->getMessage()]);
            return ['reply' => 'Tive um problema ao enviar o e-mail. Tente novamente em instantes.', 'session' => $session];
        }

        $session['claim_step']  = 'awaiting_otp';
        $session['claim_email'] = $email;

        return [
            'reply'   => "Enviei um código de 4 dígitos para {$email}. Qual é o código?",
            'session' => $session,
        ];
    }

    private function handleOtp(string $message, array $session): array
    {
        $input    = trim($message);
        $provider = Provider::with('user')->find($session['claim_provider_id']);

        if (!$provider) {
            return $this->escalate($session, $input, 'Provider not found during OTP step.');
        }

        $verification = DB::table('claim_verifications')
            ->where('provider_id', $provider->id)
            ->where('email', $session['claim_email'] ?? '')
            ->first();

        if (!$verification) {
            return ['reply' => 'O código expirou. Vamos começar de novo — qual é o seu e-mail?', 'session' => array_merge($session, ['claim_step' => 'awaiting_email', 'claim_email_mismatches' => 0])];
        }

        if (now()->isAfter($verification->expires_at)) {
            DB::table('claim_verifications')->where('id', $verification->id)->delete();
            return ['reply' => 'O código expirou (válido por 10 minutos). Vamos tentar de novo — qual é o seu e-mail?', 'session' => array_merge($session, ['claim_step' => 'awaiting_email', 'claim_email_mismatches' => 0])];
        }

        $attempts = $verification->attempts + 1;
        DB::table('claim_verifications')->where('id', $verification->id)->update(['attempts' => $attempts]);

        if ($input !== $verification->otp) {
            if ($attempts >= 2) {
                DB::table('claim_verifications')->where('id', $verification->id)->delete();
                return $this->escalate($session, $input, "Wrong OTP {$attempts}x. Expected: {$verification->otp}");
            }

            return [
                'reply'   => 'Código incorreto. Tente mais uma vez.',
                'session' => $session,
            ];
        }

        // OTP correct — claim the provider
        DB::table('claim_verifications')->where('id', $verification->id)->delete();

        try {
            $this->completeClaim($provider);
        } catch (\Throwable $e) {
            Log::error('claim: completeClaim failed', ['error' => $e->getMessage()]);
            return $this->escalate($session, $input, 'completeClaim exception: ' . $e->getMessage());
        }

        $session['claim_step'] = 'done';

        return [
            'reply'   => "Conta verificada! ✅ Enviamos um e-mail para você ativar sua senha e acessar o painel. Qualquer dúvida, é só chamar.",
            'session' => $session,
        ];
    }

    private function completeClaim(Provider $provider): void
    {
        $user = $provider->user;

        DB::transaction(function () use ($provider, $user) {
            $token = Str::random(64);

            $user->update([
                'activation_token'            => $token,
                'activation_token_expires_at' => now()->addDays(7),
                'email_verified_at'           => null,
            ]);

            $provider->update(['claimed' => true]);
        });

        Mail::to($user->email)->send(new \App\Mail\AccountActivationMail($user));
    }

    private function escalate(array $session, string $attempted, string $reason): array
    {
        $provider = Provider::find($session['claim_provider_id']);

        try {
            Mail::to('envagobrasil@gmail.com')->send(new ClaimEscalationMail(
                providerName: $provider?->business_name ?? 'Desconhecido',
                providerSlug: $provider?->slug ?? '',
                emailAttempted: $attempted,
                reason: $reason,
            ));
        } catch (\Throwable $e) {
            Log::error('claim: escalation email failed', ['error' => $e->getMessage()]);
        }

        $session['claim_step'] = 'escalated';

        return [
            'reply'   => "Não consegui verificar sua conta agora. Vou avisar a equipe e alguém entra em contato em até 24h. 🙏",
            'session' => $session,
        ];
    }
}
