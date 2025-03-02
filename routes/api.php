<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LandingPageController;

// Public Routes (tanpa autentikasi)
Route::get('/landing-page', [LandingPageController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Perlu Autentikasi)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User Management (Admin Only)
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Category Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });
    });

    // Product Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        
        // Routes untuk admin dan kelas
        Route::middleware('role:admin,kelas')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });

        // Route khusus admin untuk mengubah harga produk
        Route::middleware('role:admin')->group(function () {
            Route::put('/{id}/update-price', [ProductController::class, 'updatePrice']);
        });
    });

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        // Banner Management
        Route::prefix('banners')->group(function () {
            Route::get('/', [BannerController::class, 'index']);
            Route::post('/', [BannerController::class, 'store']);
            Route::get('/{id}', [BannerController::class, 'show']);
            Route::put('/{id}', [BannerController::class, 'update']);
            Route::delete('/{id}', [BannerController::class, 'destroy']);
        });

        // About Management
        Route::prefix('abouts')->group(function () {
            Route::get('/', [AboutController::class, 'index']);
            Route::post('/', [AboutController::class, 'store']);
            Route::put('/{id}', [AboutController::class, 'update']);
            Route::delete('/{id}', [AboutController::class, 'destroy']);
        });
    });

    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::middleware('role:admin,kelas,pengguna')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{order}', [OrderController::class, 'show']);
        });

        Route::middleware('role:pengguna')->group(function () {
            Route::post('/', [OrderController::class, 'store']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::put('/{order}/status', [OrderController::class, 'updateStatus']);
            Route::delete('/{order}', [OrderController::class, 'destroy']);
        });
    });
});
