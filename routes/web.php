<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lara', [App\Http\Controllers\ChatController::class, 'show'])->name('lara.chat');
Route::post('/lara/message', [App\Http\Controllers\ChatController::class, 'message'])->name('lara.message');
Route::post('/lara/reset', [App\Http\Controllers\ChatController::class, 'reset'])->name('lara.reset');
