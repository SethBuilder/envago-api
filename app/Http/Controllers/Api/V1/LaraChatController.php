<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LaraOnboardService;
use App\Services\LaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaraChatController extends Controller
{
    public function message(Request $request, LaraService $lara)
    {
        $request->validate([
            'session_id'    => 'required|string|max:64',
            'message'       => 'required|string|max:2000',
            'context'       => 'nullable|string|in:website,direct,provider_dashboard',
            'provider_info' => 'nullable|string|max:2000',
        ]);

        $sessionId    = $request->input('session_id');
        $userMessage  = $request->input('message');
        $context      = $request->input('context', 'direct');
        $providerInfo = $request->input('provider_info');
        $isInit       = $userMessage === '__init__';

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        $triggerMessage = $isInit ? 'Olá' : $userMessage;
        $reply = $lara->respond($history->all(), $triggerMessage, $context, $providerInfo);

        // Detect onboarding JSON and auto-create provider account
        if (preg_match('/```json\s*(\{.*?"action"\s*:\s*"onboard".*?\})\s*```/s', $reply, $m)
            || preg_match('/(\{"action"\s*:\s*"onboard".*?\})/s', $reply, $m)) {
            $payload = json_decode($m[1], true);
            if ($payload && isset($payload['operator'], $payload['tours'])) {
                try {
                    $result = app(LaraOnboardService::class)->onboard($payload);
                    $reply  = $this->formatOnboardingMessage($result, $payload['operator']);
                } catch (\Throwable $e) {
                    Log::error('Lara onboard failed: ' . $e->getMessage());
                }
            }
        }

        // Detect escalation JSON and email support
        if (preg_match('/(\{"action"\s*:\s*"escalate".*?\})/s', $reply, $m)) {
            $payload = json_decode($m[1], true);
            $reply   = trim(str_replace($m[1], '', $reply));
            if ($payload && isset($payload['summary'])) {
                $this->sendEscalationEmail($payload['summary'], $providerInfo, $userMessage);
            }
        }

        $rows = [];
        if (!$isInit) {
            $rows[] = ['session_id' => $sessionId, 'role' => 'user', 'message' => $userMessage, 'created_at' => now(), 'updated_at' => now()];
        }
        $rows[] = ['session_id' => $sessionId, 'role' => 'assistant', 'message' => $reply, 'created_at' => now(), 'updated_at' => now()];
        DB::table('lara_conversations')->insert($rows);

        return response()->json(['reply' => $reply]);
    }

    private function sendEscalationEmail(string $summary, ?string $providerInfo, string $lastMessage): void
    {
        $resendKey = config('services.resend.key');
        if (!$resendKey) return;

        $from = env('RESEND_FROM', 'Envago <onboarding@resend.dev>');

        try {
            \Resend::client($resendKey)->emails->send([
                'from'    => $from,
                'to'      => ['envagobrasil@gmail.com'],
                'subject' => 'Lara escalation: ' . Str::limit($summary, 60),
                'html'    => "
                    <h2>Lara escalated an issue</h2>
                    <p><strong>Summary:</strong> {$summary}</p>
                    <p><strong>Last message from operator:</strong> " . e($lastMessage) . "</p>
                    <hr>
                    <pre style='background:#f5f5f3;padding:12px;border-radius:8px;font-size:13px;'>" . e($providerInfo ?? 'No provider context') . "</pre>
                ",
            ]);
        } catch (\Throwable $e) {
            Log::error('Lara escalation email failed: ' . $e->getMessage());
        }
    }

    private function formatOnboardingMessage(array $result, array $op): string
    {
        $slug    = $result['provider_slug'];
        $pageUrl = "envago.live/operadores/{$slug}";

        $msg = "Pronto. Sua página está no ar em {$pageUrl}.\n\n";

        if ($result['temp_password']) {
            $msg .= "Credenciais de acesso ao painel:\n";
            $msg .= "E-mail: {$op['email']}\n";
            $msg .= "Senha: {$result['temp_password']}\n\n";
        }

        $msg .= "Para receber os repasses das reservas, cadastre seus dados bancários no painel em envago.live/provider/settings.\n\n";
        $msg .= "Os valores são transferidos para sua conta bancária em até 1 dia útil após cada reserva confirmada.";

        return $msg;
    }

    public function reset(Request $request)
    {
        $request->validate(['session_id' => 'required|string|max:64']);
        DB::table('lara_conversations')->where('session_id', $request->input('session_id'))->delete();
        return response()->json(['ok' => true]);
    }
}
