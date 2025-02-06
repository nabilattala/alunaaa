<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ProductController;
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
Route::get('/landing-page', [LandingPageController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Category Routes
    Route::prefix('categories')->middleware(['role:admin'])->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // Product Routes
    Route::prefix('products')->group(function () {
        // Kelas role only sees their products
        Route::middleware(['role:admin|kelas'])->group(function () {
            Route::get('/', [ProductController::class, 'index']);  // Show all products for admin
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::post('/store', [ProductController::class, 'store']);  // Kelas can add products
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });

        // Pengguna can only view products
        Route::middleware(['role:admin|kelas|pengguna'])->group(function () {
            Route::get('/', [ProductController::class, 'index']);  // Users can see all products
            Route::get('/{id}', [ProductController::class, 'show']);  // Users can see product details
        });
    });

    // Banner Routes
    Route::prefix('banners')->middleware(['role:admin'])->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::post('/store', [BannerController::class, 'store']);
        Route::get('/{id}', [BannerController::class, 'show']);
        Route::put('/{id}', [BannerController::class, 'update']);
        Route::delete('/{id}', [BannerController::class, 'destroy']);
    });

    // About Routes
    Route::prefix('abouts')->middleware(['role:admin'])->group(function () {
        Route::get('/', [AboutController::class, 'index']);
        Route::post('/store', [AboutController::class, 'store']);
        Route::put('/{id}', [AboutController::class, 'update']);
        Route::delete('/{id}', [AboutController::class, 'destroy']);
    });
});
