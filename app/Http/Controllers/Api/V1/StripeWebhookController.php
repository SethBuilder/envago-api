<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
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

        $date  = $booking->schedule->date->format('d/m/Y');
        $time  = substr($booking->schedule->time, 0, 5);
        $seats = $booking->seats;
        $total = 'R$ ' . number_format($booking->total_price, 0, ',', '.');
        $name  = $booking->guest_name;
        $ref   = $booking->reference;

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
                        'subject' => "Nova reserva confirmada – {$activity->name}",
                        'html'    => "
                            <h2>Nova reserva! 🎉</h2>
                            <p><strong>Passeio:</strong> {$activity->name}</p>
                            <p><strong>Turista:</strong> {$name}</p>
                            <p><strong>Data:</strong> {$date} às {$time}</p>
                            <p><strong>Pessoas:</strong> {$seats}</p>
                            <p><strong>Total:</strong> {$total}</p>
                            <p><strong>Código:</strong> {$ref}</p>
                        ",
                    ]);
                } catch (\Throwable) {}
            }
        }

        // ── WhatsApp via Z-API ───────────────────────────────────────────
        $instanceId = config('services.zapi.instance_id');
        $zapiToken  = config('services.zapi.token');
        $phone      = $provider?->whatsapp_number;

        if ($instanceId && $zapiToken && $phone) {
            try {
                $message = "🎉 *Nova reserva confirmada!*\n\n"
                    . "📌 *{$activity->name}*\n"
                    . "👤 {$name}\n"
                    . "📅 {$date} às {$time}\n"
                    . "👥 {$seats} pessoa" . ($seats > 1 ? 's' : '') . "\n"
                    . "💰 {$total}\n"
                    . "🔖 {$ref}";

                \Illuminate\Support\Facades\Http::post(
                    "https://api.z-api.io/instances/{$instanceId}/token/{$zapiToken}/send-text",
                    ['phone' => $phone, 'message' => $message]
                );
            } catch (\Throwable) {}
        }
    }
}