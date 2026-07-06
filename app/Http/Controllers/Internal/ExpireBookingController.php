<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ExpireBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->header('X-Internal-Secret') !== config('services.sqs.internal_secret')) {
            abort(403);
        }

        $bookingId = $request->input('booking_id');
        $booking   = Booking::find($bookingId);

        if (!$booking) {
            return response()->json(['status' => 'not_found']);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['status' => 'already_settled']);
        }

        if ($booking->expires_at && $booking->expires_at->isFuture()) {
            return response()->json(['status' => 'not_expired_yet']);
        }

        try {
            if ($booking->payment_ref) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $pi = PaymentIntent::retrieve($booking->payment_ref);
                if (!in_array($pi->status, ['canceled', 'succeeded'])) {
                    PaymentIntent::cancel($booking->payment_ref);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('expire_booking: stripe cancel failed', ['booking_id' => $bookingId, 'error' => $e->getMessage()]);
        }

        $booking->schedule->decrement('seats_booked', $booking->seats);
        $booking->delete();

        Log::info('expire_booking: expired', ['booking_id' => $bookingId]);

        return response()->json(['status' => 'expired']);
    }
}
