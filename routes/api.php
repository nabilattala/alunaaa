<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiscountController;
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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\OrderExportController;

// ======================
// Public Routes
// ======================
Route::get('/landing-page', [LandingPageController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Socialite
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);

// Public Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ======================
// Protected Routes (with jwt.verify middleware)
// ======================
Route::middleware('jwt.verify')->group(function () {

    // Authenticated User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/set-username', [UserController::class, 'setUsername']);

    // Tambahan: 
    // Route untuk get semua users (dengan pagination)
    Route::get('/users', [UserController::class, 'index']); // untuk semua authenticated user
    // Route untuk get current authenticated user info
    Route::get('/current-user', [UserController::class, 'currentUser']); // untuk current user

    // ======================
    // User Management (Admin Only)
    // ======================
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // ======================
    // Category Routes
    // ======================
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });
    });

    // ======================
    // Product Routes (Admin & Kelas)
    // ======================
    Route::middleware('role:admin,kelas')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // Update harga produk (Admin Only)
    Route::middleware('role:admin')->put('/products/{id}/update-price', [ProductController::class, 'updatePrice']);

    // Apply Discount ke Produk (Admin Only)
    Route::middleware('role:admin')->post('/products/{id}/apply-discount', [ProductController::class, 'applyDiscount']);

    // Request & Approve Harga Produk
    Route::middleware('role:pengguna')->post('/products/{id}/request-price', [ProductController::class, 'requestPrice']);
    Route::middleware('role:admin')->post('/price-requests/{id}/approve', [ProductController::class, 'approvePriceRequest']);

    // ======================
    // Cart Routes (Pengguna)
    // ======================
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // ======================
    // Favorite Routes (Pengguna)
    // ======================
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{id}', [FavoriteController::class, 'destroy']);
    });

    // ======================
    // Chat Routes
    // ======================
    Route::post('/chat/start', [ChatController::class, 'startChat']);
    Route::get('/chat', [ChatController::class, 'getChats']);
    Route::get('/chat/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);

    // ======================
    // Order Routes
    // ======================
    Route::prefix('orders')->group(function () {
        // View Order (Admin, Kelas, Pengguna)
        Route::middleware('role:admin,kelas,pengguna')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{order}', [OrderController::class, 'show']);
        });

        // Create Order (Pengguna Only)
        Route::middleware('role:pengguna')->post('/', [OrderController::class, 'store']);

        // Update & Delete Order (Admin Only)
        Route::middleware('role:admin')->group(function () {
            Route::put('/{order}/status', [OrderController::class, 'updateStatus']);
            Route::delete('/{order}', [OrderController::class, 'destroy']);
        });
    });

    // ======================
    // Discount Routes
    // ======================
    Route::get('/discounts', [DiscountController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
    });

    // ======================
    // Rating Routes
    // ======================
    Route::middleware('auth:api')->group(function () {
        Route::post('/products/{product}/ratings', [RatingController::class, 'store']);
    });
    
    Route::get('/products/{product}/ratings', [RatingController::class, 'index']);

    // ======================
    // OTP Routes
    // ======================
    Route::post('/forgot-password', [UserController::class, 'sendOtpForgotPassword']);
    Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);

    // ======================
    // Export to excel Routes
    // ======================
    Route::get('/export/orders', [OrderExportController::class, 'export']);

    // ======================
    // Dashboard Routes
    // ======================
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::middleware('role:pengguna')->post('/checkout', [OrderController::class, 'checkout']);



});
