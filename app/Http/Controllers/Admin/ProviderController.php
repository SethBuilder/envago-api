<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Provider;
use App\Mail\AccountActivationMail;
use App\Mail\StripeOnboardingMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::withTrashed()->with('user')
            ->orderByRaw('deleted_at IS NOT NULL')
            ->latest()
            ->paginate(25);

        return view('admin.providers', compact('providers'));
    }

    public function show(Provider $provider)
    {
        $provider->loadMissing('user');
        $provider->load(['activities' => fn($q) => $q->withTrashed()->orderByRaw('deleted_at IS NOT NULL')->orderBy('id')]);

        $activityIds = $provider->activities->pluck('id');

        $bookings = Booking::whereHas('schedule', fn($q) => $q->whereIn('activity_id', $activityIds))
            ->with('schedule.activity')
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'total'     => $bookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'pending'   => $bookings->where('status', 'pending')->count(),
            'revenue'   => $bookings->where('status', 'confirmed')->sum('total_price'),
        ];

        return view('admin.provider-detail', compact('provider', 'bookings', 'stats'));
    }

    public function approve(Provider $provider)
    {
        $provider->update(['status' => 'approved']);
        $provider->load('user');
        Mail::to($provider->user->email)->send(new StripeOnboardingMail($provider));

        return back()->with('success', "Operador {$provider->business_name} aprovado. E-mail de onboarding enviado.");
    }

    public function reject(Provider $provider)
    {
        $provider->update(['status' => 'rejected']);

        return back()->with('success', "Operador {$provider->business_name} rejeitado.");
    }

    public function impersonate(Provider $provider)
    {
        session(['impersonating_from' => auth()->id()]);
        auth()->loginUsingId($provider->user_id);

        return redirect()->route('provider.dashboard');
    }

    public function stopImpersonating()
    {
        $adminId = session()->pull('impersonating_from');
        auth()->loginUsingId($adminId);

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Provider $provider)
    {
        $name = $provider->business_name;
        $provider->delete();

        return redirect()->route('admin.providers')->with('success', "Operador \"{$name}\" arquivado.");
    }

    public function destroyActivity(Provider $provider, Activity $activity)
    {
        $name = $activity->name;
        $activity->delete();

        return back()->with('success', "Passeio \"{$name}\" arquivado.");
    }

    public function resendActivation(Provider $provider)
    {
        $user = $provider->user;

        if ($user->email_verified_at) {
            return back()->with('success', "Conta já verificada — nenhum e-mail enviado.");
        }

        $user->update([
            'activation_token'            => \Illuminate\Support\Str::random(64),
            'activation_token_expires_at' => now()->addDays(7),
        ]);

        Mail::to($user->email)->send(new AccountActivationMail($user));

        return back()->with('success', "E-mail de ativação reenviado para {$user->email}.");
    }
}
