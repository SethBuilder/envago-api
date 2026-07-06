<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\LaraChatController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use App\Http\Controllers\Internal\ExpireBookingController;
use App\Http\Controllers\WhatsappController;

Route::prefix('v1')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('activities', [ActivityController::class, 'index']);
    Route::get('activities/{slug}', [ActivityController::class, 'show']);
    Route::get('operadores/{slug}', [ProviderController::class, 'show']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{reference}/status', [BookingController::class, 'status']);
});

Route::post('internal/expire-booking', ExpireBookingController::class);
Route::post('webhooks/stripe',    [StripeWebhookController::class, 'handle']);
Route::get('webhooks/whatsapp',   [WhatsappController::class,        'verify']);
Route::post('webhooks/whatsapp',  [WhatsappController::class,        'webhook']);

Route::post('lara/message', [LaraChatController::class, 'message']);
Route::post('lara/reset',   [LaraChatController::class, 'reset']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');