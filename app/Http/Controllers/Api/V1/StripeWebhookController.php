<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payout;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $pi      = $event->data->object;
            $booking = Booking::where('payment_ref', $pi->id)
                ->with('schedule.activity.provider.user')
                ->first();

            if ($booking && $booking->status === 'pending') {
                $booking->update([
                    'status'       => 'confirmed',
                    'confirmed_at' => now(),
                ]);


                $this->notifyOperator($booking);
            }
        }

        return response()->json(['received' => true]);
    }

    private function notifyOperator(Booking $booking): void
    {
        $activity = $booking->schedule->activity;
        $provider = $activity->provider;
        $operator = $provider?->user;

        $date      = $booking->schedule->date->format('d/m/Y');
        $time      = substr($booking->schedule->time, 0, 5);
        $seats     = $booking->seats;
        $total     = 'R$ ' . number_format($booking->total_price, 0, ',', '.');
        $name      = $booking->guest_name;
        $ref       = $booking->reference;
        $guestWa   = $booking->guest_whatsapp;
        $waDigits  = $guestWa ? (strlen($guestWa) <= 11 ? '55' . $guestWa : $guestWa) : null;
        $waLink    = $waDigits ? "https://wa.me/{$waDigits}" : null;

        $resendKey = config('services.resend.key');
        // Use verified domain in prod; onboarding@resend.dev always works in dev
        $from = env('RESEND_FROM', 'Envago <onboarding@resend.dev>');

        if ($resendKey) {
            $resend = \Resend::client($resendKey);

            // ── Guest confirmation email ──────────────────────────────────
            try {
                $resend->emails->send([
                    'from'    => $from,
                    'to'      => [$booking->guest_email],
                    'bcc'     => ['envagobrasil@gmail.com'],
                    'subject' => "Reserva confirmada – {$activity->name} ({$ref})",
                    'html'    => "
                        <h2>Sua reserva foi confirmada! 🎉</h2>
                        <p>Olá, <strong>{$name}</strong>!</p>
                        <br>
                        <p><strong>Passeio:</strong> {$activity->name}</p>
                        <p><strong>Data:</strong> {$date} às {$time}</p>
                        <p><strong>Pessoas:</strong> {$seats}</p>
                        <p><strong>Total pago:</strong> {$total}</p>
                        <p><strong>Código da reserva:</strong> {$ref}</p>
                        <br>
                        <p>Nos vemos no passeio! 🌊</p>
                        <p>— Equipe Envago</p>
                    ",
                ]);
            } catch (\Throwable $e) {
                \Log::error('Guest email failed: ' . $e->getMessage());
            }

            // ── Operator notification email ───────────────────────────────
            if ($operator) {
                try {
                    $resend->emails->send([
                        'from'    => $from,
                        'to'      => [$operator->email],
                        'bcc'     => ['envagobrasil@gmail.com'],
                        'subject' => "Nova reserva confirmada – {$activity->name}",
                        'html'    => "
                            <h2>Nova reserva! 🎉</h2>
                            <p><strong>Passeio:</strong> {$activity->name}</p>
                            <p><strong>Turista:</strong> {$name}</p>
                            <p><strong>Data:</strong> {$date} às {$time}</p>
                            <p><strong>Pessoas:</strong> {$seats}</p>
                            <p><strong>Total:</strong> {$total}</p>
                            <p><strong>Código:</strong> {$ref}</p>
                            " . ($waLink ? "<p><strong>WhatsApp do turista:</strong> <a href=\"{$waLink}\">{$guestWa}</a></p>" : "") . "
                        ",
                    ]);
                } catch (\Throwable) {}
            }
        }

        // ── WhatsApp via Twilio ──────────────────────────────────────────
        $twilioSid   = config('services.twilio.account_sid');
        $twilioToken = config('services.twilio.auth_token');
        $twilioFrom  = config('services.twilio.from');
        $phone       = $provider?->whatsapp_number;

        if ($twilioSid && $twilioToken && $twilioFrom && $phone) {
            try {
                $digits = preg_replace('/\D/', '', $phone);
                if (strlen($digits) <= 11) {
                    $digits = '55' . $digits; // add BR country code if missing
                }

                $message = "🎉 *Nova reserva confirmada!*\n\n"
                    . "📌 *{$activity->name}*\n"
                    . "👤 {$name}\n"
                    . "📅 {$date} às {$time}\n"
                    . "👥 {$seats} pessoa" . ($seats > 1 ? 's' : '') . "\n"
                    . "💰 {$total}\n"
                    . "🔖 {$ref}"
                    . ($waLink ? "\n📱 wa.me/{$waDigits}" : '');

                \Illuminate\Support\Facades\Http::withBasicAuth($twilioSid, $twilioToken)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json", [
                        'From' => $twilioFrom,
                        'To'   => 'whatsapp:+' . $digits,
                        'Body' => $message,
                    ]);
            } catch (\Throwable) {}
        }
    }
}