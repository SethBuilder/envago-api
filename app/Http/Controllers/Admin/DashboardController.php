<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'providers_total'   => Provider::count(),
            'providers_pending' => Provider::where('status', 'pending')->count(),
            'bookings_total'    => Booking::count(),
            'revenue'           => Booking::where('status', 'confirmed')->sum('total_price'),
        ];

        $pendingProviders = Provider::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        $recentBookings = Booking::with('schedule.activity.provider.user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'pendingProviders', 'recentBookings'));
    }
}
