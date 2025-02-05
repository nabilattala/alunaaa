<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']); // Menampilkan semua user
    Route::get('/{id}', [UserController::class, 'show']); // Menampilkan user berdasarkan ID
    Route::post('/', [UserController::class, 'store']); // Menambahkan user baru
    Route::put('/{id}', [UserController::class, 'update']); // Mengupdate user berdasarkan ID
    Route::delete('/{id}', [UserController::class, 'destroy']); // Menghapus user berdasarkan ID
});

Route::post('/register', [AuthController::class, 'register']); // Register user
Route::post('/login', [AuthController::class, 'login']); // Login user

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); // Logout user
    Route::get('/user', [AuthController::class, 'user']); // Get logged-in user
});
