<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\StripeWebhookController;

Route::prefix('v1')->group(function () {
    Route::get('activities', [ActivityController::class, 'index']);
    Route::get('activities/{slug}', [ActivityController::class, 'show']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{reference}/status', [BookingController::class, 'status']);
});

Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');