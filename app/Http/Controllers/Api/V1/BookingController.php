<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'seats'       => ['required', 'integer', 'min:1', 'max:20'],
            'guest_name'      => ['required', 'string', 'max:120'],
            'guest_email'     => ['required', 'email', 'max:180'],
            'guest_cpf'       => ['nullable', 'string', 'min:11', 'max:14'],
            'guest_whatsapp'  => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $booking = DB::transaction(function () use ($data) {
                $schedule = Schedule::lockForUpdate()->findOrFail($data['schedule_id']);

                // Clean up orphaned pending bookings (Stripe failed before payment_ref was set)
                $orphaned = Booking::where('schedule_id', $schedule->id)
                    ->where('status', 'pending')
                    ->whereNull('payment_ref')
                    ->get();
                foreach ($orphaned as $o) {
                    $schedule->decrement('seats_booked', $o->seats);
                    $o->delete();
                }
                $schedule->refresh();

                $seatsLeft = $schedule->seats_total - $schedule->seats_booked;
                if ($data['seats'] > $seatsLeft) {
                    abort(422, 'Não há vagas suficientes para este horário.');
                }

                $activity   = $schedule->activity;
                $totalPrice = $activity->price * $data['seats'];

                $booking = Booking::create([
                    'schedule_id' => $schedule->id,
                    'seats'       => $data['seats'],
                    'total_price' => $totalPrice,
                    'guest_name'  => $data['guest_name'],
                    'guest_email' => $data['guest_email'],
                    'guest_cpf'       => isset($data['guest_cpf']) ? preg_replace('/\D/', '', $data['guest_cpf']) : null,
                    'guest_whatsapp'  => isset($data['guest_whatsapp']) ? preg_replace('/\D/', '', $data['guest_whatsapp']) : null,
                    'reference'   => 'ENV-' . strtoupper(Str::random(6)),
                    'status'      => 'pending',
                ]);

                $schedule->increment('seats_booked', $data['seats']);

                return $booking->load('schedule.activity');
            });

            // ── Create Stripe PaymentIntent (card + PIX) ─────────────────
            Stripe::setApiKey(config('services.stripe.secret'));

            $activity = $booking->schedule->activity;

            $paymentMethods = ['card'];
            if (config('services.stripe.pix_enabled')) {
                $paymentMethods[] = 'pix';
            }

            $pi = PaymentIntent::create([
                'amount'               => (int) ($booking->total_price * 100),
                'currency'             => 'brl',
                'payment_method_types' => $paymentMethods,
                'metadata'             => [
                    'booking_reference' => $booking->reference,
                    'booking_id'        => $booking->id,
                ],
            ]);

            $booking->update(['payment_ref' => $pi->id]);

            return response()->json([
                'data' => [
                    'id'            => $booking->id,
                    'reference'     => $booking->reference,
                    'guest_name'    => $booking->guest_name,
                    'guest_email'   => $booking->guest_email,
                    'seats'         => $booking->seats,
                    'total_price'   => $booking->total_price,
                    'status'        => $booking->status,
                    'client_secret' => $pi->client_secret,
                    'activity'      => [
                        'name'         => $activity->name,
                        'slug'         => $activity->slug,
                        'city'         => $activity->city,
                        'pricing_type' => $activity->pricing_type,
                        'pricing_unit' => $activity->pricing_unit,
                    ],
                    'date' => $booking->schedule->date->toDateString(),
                    'time' => substr($booking->schedule->time, 0, 5),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Throwable $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage());
            // Roll back seats if booking was created but Stripe failed
            if (isset($booking) && !$booking->payment_ref) {
                $booking->schedule->decrement('seats_booked', $booking->seats);
                $booking->delete();
            }
            return response()->json(['message' => 'Erro ao processar reserva. Tente novamente.'], 500);
        }
    }

    public function status(string $reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        return response()->json(['status' => $booking->status]);
    }

}