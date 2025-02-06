<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LandingPageController;

// User Routes
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Category Routes (Hanya bisa diakses jika user sudah login)
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    
        // CRUD untuk Landing Page
        Route::prefix('landing-pages')->group(function () {
            Route::get('/', [LandingPageController::class, 'index']); // Menampilkan semua landing pages
            Route::get('/{id}', [LandingPageController::class, 'show']); // Menampilkan landing page berdasarkan ID
            Route::post('/', [LandingPageController::class, 'store']); // Menambahkan landing page baru
            Route::put('/{id}', [LandingPageController::class, 'update']); // Mengupdate landing page
            Route::delete('/{id}', [LandingPageController::class, 'destroy']); // Menghapus landing page
        });



});
