<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteController;

Route::get('/', function () {
    return view('welcome');
});

// Socialite Callback (Gunakan di Web Routes)
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
