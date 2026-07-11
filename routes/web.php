<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParceirosController;
use App\Http\Controllers\Admin\AdminLaraController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\LaraConversationsController;
use App\Http\Controllers\Admin\LaraInstructionsController;
use App\Http\Controllers\Admin\PayoutController as AdminPayout;
use App\Http\Controllers\Admin\ProviderController as AdminProvider;
use App\Http\Controllers\Admin\ScopeLeadsController;
use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminWebStoryController;
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
    Route::get('/forgot-password',   [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',  [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',   [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                          [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/providers',                 [AdminProvider::class,  'index'])->name('providers');
    Route::get('/providers/{provider}',      [AdminProvider::class,  'show'])->name('providers.show')->withTrashed();
    Route::post('/providers/{provider}/approve',          [AdminProvider::class, 'approve'])->name('providers.approve');
    Route::post('/providers/{provider}/reject',           [AdminProvider::class, 'reject'])->name('providers.reject');
    Route::post('/providers/{provider}/impersonate',      [AdminProvider::class, 'impersonate'])->name('providers.impersonate');
    Route::post('/providers/{provider}/resend-activation',             [AdminProvider::class, 'resendActivation'])->name('providers.resend-activation');
    Route::patch('/providers/{provider}/email',                          [AdminProvider::class, 'updateEmail'])->name('providers.update-email');
    Route::delete('/providers/{provider}',                              [AdminProvider::class, 'destroy'])->name('providers.destroy');
    Route::delete('/providers/{provider}/force',                        [AdminProvider::class, 'forceDestroy'])->name('providers.force-destroy')->withTrashed();
    Route::delete('/providers/{provider}/activities/{activity}',        [AdminProvider::class, 'destroyActivity'])->name('providers.activities.destroy');
    Route::get('/payouts',                              [AdminPayout::class, 'index'])->name('payouts');
    Route::post('/payouts/{schedule}/pay',              [AdminPayout::class, 'pay'])->name('payouts.pay');
    Route::get('/payouts/{payout}/download',            [AdminPayout::class, 'download'])->name('payouts.download');

    // Lara admin
    Route::get('/lara',                                    [AdminLaraController::class,         'show'])->name('lara.chat');
    Route::post('/lara/message',                           [AdminLaraController::class,         'message'])->name('lara.message');
    Route::post('/lara/reset',                             [AdminLaraController::class,         'reset'])->name('lara.reset');
    Route::get('/lara/conversations',                      [LaraConversationsController::class, 'index'])->name('lara.conversations');
    Route::get('/lara/conversations/{sessionId}',          [LaraConversationsController::class, 'show'])->name('lara.conversations.show');
    Route::get('/scope-leads',                             [ScopeLeadsController::class,        'index'])->name('scope-leads');
    Route::get('/articles',                                [AdminArticleController::class,      'index'])->name('articles');
    Route::get('/articles/{article}/edit',                 [AdminArticleController::class,      'edit'])->name('articles.edit');
    Route::post('/articles/{article}/update',              [AdminArticleController::class,      'update'])->name('articles.update');
    Route::post('/articles/{article}/publish',             [AdminArticleController::class,      'publish'])->name('articles.publish');
    Route::post('/articles/{article}/unpublish',           [AdminArticleController::class,      'unpublish'])->name('articles.unpublish');
    Route::delete('/articles/{article}',                   [AdminArticleController::class,      'destroy'])->name('articles.destroy');
    Route::post('/articles/{id}/restore',                  [AdminArticleController::class,      'restore'])->name('articles.restore');
    Route::get('/stories',                                [AdminWebStoryController::class, 'index'])->name('stories');
    Route::get('/stories/{story}/edit',                   [AdminWebStoryController::class, 'edit'])->name('stories.edit');
    Route::post('/stories/{story}/update',                [AdminWebStoryController::class, 'update'])->name('stories.update');
    Route::post('/stories/{story}/publish',               [AdminWebStoryController::class, 'publish'])->name('stories.publish');
    Route::post('/stories/{story}/unpublish',             [AdminWebStoryController::class, 'unpublish'])->name('stories.unpublish');

    Route::get('/lara-instructions',                       [LaraInstructionsController::class,  'index'])->name('lara.instructions');
    Route::post('/lara-instructions',                      [LaraInstructionsController::class,  'store'])->name('lara.instructions.store');
    Route::post('/lara-instructions/{instruction}/toggle', [LaraInstructionsController::class,  'toggle'])->name('lara.instructions.toggle');
    Route::delete('/lara-instructions/{instruction}',      [LaraInstructionsController::class,  'destroy'])->name('lara.instructions.destroy');
});

// Parceiros — public provider onboarding chat
Route::get('/parceiros',          [ParceirosController::class, 'show'])->name('parceiros');
Route::post('/parceiros/message', [ParceirosController::class, 'message'])->name('parceiros.message')->middleware('throttle:20,1');


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
    Route::post('/ai/enhance-description',       [ProviderActivity::class,  'aiEnhanceDescription'])->name('activities.ai.enhance');
    Route::post('/ai/extract-highlights',        [ProviderActivity::class,  'aiExtractHighlights'])->name('activities.ai.highlights');
    Route::post('/bookings/cancel',              [ProviderDashboard::class, 'cancelBookings'])->name('bookings.cancel');
    Route::get('/pagamentos',                    [ProviderDashboard::class, 'payouts'])->name('payouts');
    Route::get('/pagamentos/{payout}/comprovante', [ProviderDashboard::class, 'downloadPayout'])->name('payouts.download');
    Route::get('/settings',        [ProviderSettings::class, 'show'])->name('settings');
    Route::put('/settings',        [ProviderSettings::class, 'update'])->name('settings.update');
});
