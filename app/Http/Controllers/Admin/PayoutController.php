<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    public function index()
    {
        // All schedules that have at least one confirmed booking, with accumulator + history
        $schedules = Schedule::with(['activity.provider'])
            ->whereHas('bookings', fn($q) => $q->where('status', 'confirmed'))
            ->orderByDesc('date')
            ->get()
            ->map(function ($schedule) {
                $provider = $schedule->activity->provider;

                $unpaid = Booking::where('schedule_id', $schedule->id)
                    ->where('status', 'confirmed')
                    ->whereNull('payout_id')
                    ->selectRaw('COUNT(*) as count, SUM(total_price * 0.9) as total')
                    ->first();

                $history = Payout::where('schedule_id', $schedule->id)
                    ->where('status', 'paid')
                    ->orderByDesc('paid_at')
                    ->get();

                return [
                    'schedule'       => $schedule,
                    'provider'       => $provider,
                    'pending_count'  => (int) $unpaid->count,
                    'pending_amount' => round((float) $unpaid->total, 2),
                    'history'        => $history,
                ];
            });

        return view('admin.payouts', compact('schedules'));
    }

    public function pay(Request $request, Schedule $schedule)
    {
        $request->validate([
            'confirmation' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $bookings = Booking::where('schedule_id', $schedule->id)
            ->where('status', 'confirmed')
            ->whereNull('payout_id')
            ->get();

        abort_if($bookings->isEmpty(), 422, 'Nenhum pagamento pendente para este passeio.');

        $amount   = round($bookings->sum('total_price') * 0.9, 2);
        $provider = $schedule->activity->provider;

        try { DB::transaction(function () use ($request, $schedule, $bookings, $amount, $provider) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'local';
            $path = $request->file('confirmation')->store(
                'payouts/confirmations',
                ['disk' => $disk, 'visibility' => 'private']
            );

            $payout = Payout::create([
                'schedule_id'       => $schedule->id,
                'provider_id'       => $provider->id,
                'amount'            => $amount,
                'booking_count'     => $bookings->count(),
                'currency'          => 'BRL',
                'status'            => 'paid',
                'paid_at'           => now(),
                'confirmation_path' => $path,
            ]);

            Booking::whereIn('id', $bookings->pluck('id'))->update(['payout_id' => $payout->id]);
        }); } catch (\Throwable $e) {
            Log::error('Payout pay() failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return back()->withErrors(['error' => 'Erro: ' . $e->getMessage()]);
        }

        return back()->with('success', "Pagamento de R$ " . number_format($amount, 0, ',', '.') . " para {$provider->business_name} registrado.");
    }

    public function download(Payout $payout)
    {
        abort_unless($payout->confirmation_path, 404);

        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';
        $url  = Storage::disk($disk)->temporaryUrl($payout->confirmation_path, now()->addMinutes(5));

        return redirect($url);
    }
}
