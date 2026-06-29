<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ParceirosController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\PayoutController as AdminPayout;
use App\Http\Controllers\Admin\ProviderController as AdminProvider;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboard;
use App\Http\Controllers\Provider\SettingsController as ProviderSettings;
use App\Http\Controllers\Provider\ActivityController as ProviderActivity;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return redirect($user->isAdmin() ? route('admin.dashboard') : route('provider.dashboard'));
    }
    return redirect()->route('login');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
    Route::get('/activate/{token}',  [AuthController::class, 'showActivate'])->name('auth.activate');
    Route::post('/activate/{token}', [AuthController::class, 'activate'])->name('auth.activate.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                          [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/providers',                 [AdminProvider::class,  'index'])->name('providers');
    Route::get('/providers/{provider}',      [AdminProvider::class,  'show'])->name('providers.show');
    Route::post('/providers/{provider}/approve',          [AdminProvider::class, 'approve'])->name('providers.approve');
    Route::post('/providers/{provider}/reject',           [AdminProvider::class, 'reject'])->name('providers.reject');
    Route::post('/providers/{provider}/impersonate',      [AdminProvider::class, 'impersonate'])->name('providers.impersonate');
    Route::get('/payouts',                              [AdminPayout::class, 'index'])->name('payouts');
    Route::post('/payouts/{schedule}/pay',              [AdminPayout::class, 'pay'])->name('payouts.pay');
    Route::get('/payouts/{payout}/download',            [AdminPayout::class, 'download'])->name('payouts.download');
});

// Parceiros — public provider onboarding chat
Route::get('/parceiros',          [ParceirosController::class, 'show'])->name('parceiros');
Route::post('/parceiros/message', [ParceirosController::class, 'message'])->name('parceiros.message');

// Lara test — admin only, at /lara
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/lara',          [ChatController::class, 'show'])->name('lara.chat');
    Route::post('/lara/message', [ChatController::class, 'message'])->name('lara.message');
    Route::post('/lara/reset',   [ChatController::class, 'reset'])->name('lara.reset');
});

Route::post('/stop-impersonating', [AdminProvider::class, 'stopImpersonating'])->name('stop.impersonating')->middleware('auth');

// Provider
Route::middleware(['auth', 'provider'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('/',                              [ProviderDashboard::class, 'index'])->name('dashboard');
    Route::get('/reservas',                      [ProviderDashboard::class, 'bookings'])->name('bookings');
    Route::get('/passeios',                      [ProviderDashboard::class, 'activities'])->name('activities');
    Route::get('/passeios/criar',                [ProviderActivity::class,  'create'])->name('activities.create');
    Route::post('/passeios',                     [ProviderActivity::class,  'store'])->name('activities.store');
    Route::get('/passeios/{activity}/editar',    [ProviderActivity::class,  'edit'])->name('activities.edit');
    Route::post('/passeios/{activity}',          [ProviderActivity::class,  'update'])->name('activities.update');
    Route::post('/bookings/cancel',              [ProviderDashboard::class, 'cancelBookings'])->name('bookings.cancel');
    Route::get('/pagamentos',                    [ProviderDashboard::class, 'payouts'])->name('payouts');
    Route::get('/pagamentos/{payout}/comprovante', [ProviderDashboard::class, 'downloadPayout'])->name('payouts.download');
    Route::get('/settings',        [ProviderSettings::class, 'show'])->name('settings');
    Route::put('/settings',        [ProviderSettings::class, 'update'])->name('settings.update');
});
