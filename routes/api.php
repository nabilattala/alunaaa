<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LandingPageController;

// Public Routes (tanpa autentikasi)
Route::get('/landing-page', [LandingPageController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Socialite Routes (Redirect, Callback di Web.php, Register With Google)
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);
Route::middleware('auth:sanctum')->post('/set-username', [UserController::class, 'setUsername']);

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

    // Update Profile Route
    Route::post('/user/update-profile', [UserController::class, 'updateProfile']);

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

    // Product Routes (Admin dan Kelas)
    Route::middleware(['auth:sanctum', 'role:admin,kelas'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // Route khusus admin untuk mengubah harga produk
    Route::middleware('role:admin')->group(function () {
        Route::put('{id}/update-price', [ProductController::class, 'updatePrice']);
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Favorite Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{id}', [FavoriteController::class, 'destroy']);
    });

    // Chat Routes
    Route::post('/chat/start', [ChatController::class, 'startChat']); // Pembeli memulai chat
    Route::get('/chat', [ChatController::class, 'getChats']); // Ambil semua chat berdasarkan peran
    Route::get('/chat/{chatId}/messages', [ChatController::class, 'getMessages']); // Ambil pesan dalam chat
    Route::post('/chat/send', [ChatController::class, 'sendMessage']); // Kirim pesan

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

    // 
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/discounts', [DiscountController::class, 'index']);
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
    
        Route::post('/products/{id}/apply-discount', [ProductController::class, 'applyDiscount']);
    });
    
});
