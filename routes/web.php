<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

// Socialite Callback (Gunakan di Web Routes)
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
Route::get('/order/finish/{invoice}', [OrderController::class, 'finish'])->name('order.finish');
