<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Stripe\StripeClient;

class DashboardController extends Controller
{
    public function index()
    {
        $provider = auth()->user()->provider;

        $stats = ['total' => 0, 'confirmed' => 0, 'revenue' => 0, 'pending' => 0];
        $grouped = collect();

        if ($provider) {
            $activityIds = $provider->activities()->pluck('id');

            $bookings = Booking::whereHas('schedule', fn($q) => $q->whereIn('activity_id', $activityIds))
                ->with('schedule.activity')
                ->whereIn('status', ['confirmed', 'cancelled'])
                ->orderBy('created_at', 'desc')
                ->get();

            $confirmed          = $bookings->where('status', 'confirmed');
            $stats['total']     = $confirmed->count();
            $stats['confirmed'] = $confirmed->count();
            $stats['pending']   = 0;
            $stats['revenue']   = $confirmed->sum('total_price');

            // Group: activity name → date string → bookings
            $grouped = $bookings
                ->groupBy(fn($b) => $b->schedule->activity->name)
                ->map(fn($byActivity) => $byActivity
                    ->groupBy(fn($b) => $b->schedule->date->format('Y-m-d'))
                    ->sortKeys()
                );
        }

        return view('provider.dashboard', compact('provider', 'stats', 'grouped'));
    }

    public function bookings()
    {
        $provider = auth()->user()->provider;
        abort_unless($provider, 403);

        $activityIds = $provider->activities()->pluck('id');

        $bookings = Booking::whereHas('schedule', fn($q) => $q->whereIn('activity_id', $activityIds))
            ->with('schedule.activity')
            ->orderByDesc('created_at')
            ->get();

        return view('provider.bookings', compact('provider', 'bookings'));
    }

    public function activities()
    {
        $provider = auth()->user()->provider;
        abort_unless($provider, 403);

        $activities = $provider->activities()->with('category')->orderBy('name')->get();

        return view('provider.activities', compact('provider', 'activities'));
    }

    public function payouts()
    {
        $provider = auth()->user()->provider;
        abort_unless($provider, 403);

        $payouts = Payout::with(['schedule.activity'])
            ->where('provider_id', $provider->id)
            ->orderByDesc('paid_at')
            ->get();

        return view('provider.payouts', compact('provider', 'payouts'));
    }

    public function downloadPayout(Payout $payout)
    {
        $provider = auth()->user()->provider;
        abort_unless($payout->provider_id === $provider->id && $payout->confirmation_path, 403);

        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';

        if ($disk === 's3') {
            return redirect(Storage::disk('s3')->temporaryUrl($payout->confirmation_path, now()->addMinutes(5)));
        }

        abort_unless(Storage::disk('local')->exists($payout->confirmation_path), 404);
        return Storage::disk('local')->download($payout->confirmation_path);
    }

    public function cancelBookings(Request $request)
    {
        $request->validate([
            'booking_ids'   => 'required|array|min:1',
            'booking_ids.*' => 'integer|exists:bookings,id',
            'reason'        => 'required|string|max:500',
        ]);

        $provider    = auth()->user()->provider;
        $activityIds = $provider->activities()->pluck('id');
        $reason      = $request->input('reason');
        $ids         = $request->input('booking_ids');

        $bookings = Booking::whereIn('id', $ids)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereHas('schedule', fn($q) => $q->whereIn('activity_id', $activityIds))
            ->with('schedule.activity')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['error' => 'No valid bookings found'], 422);
        }

        $stripe   = new StripeClient(config('services.stripe.secret'));
        $refunded = [];
        $failed   = [];

        foreach ($bookings as $booking) {
            $booking->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);

            // Stripe refund
            if ($booking->payment_ref) {
                try {
                    $stripe->refunds->create(['payment_intent' => $booking->payment_ref]);
                    $refunded[] = $booking->id;
                } catch (\Throwable $e) {
                    Log::error("Stripe refund failed for booking {$booking->id}: " . $e->getMessage());
                    $failed[] = $booking->id;
                }
            }

            // Email guest
            try {
                Mail::to($booking->guest_email)->send(new BookingCancelledMail($booking));
            } catch (\Throwable $e) {
                Log::error("Cancel email failed for booking {$booking->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'ok'       => true,
            'cancelled' => $bookings->count(),
            'refunded'  => count($refunded),
            'failed'    => count($failed),
        ]);
    }
}
