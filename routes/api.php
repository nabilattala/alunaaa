<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    UserController,
    DiscountController,
    SocialiteController,
    AboutController,
    OrderController,
    ChatController,
    CartController,
    FavoriteController,
    BannerController,
    ProductController,
    CategoryController,
    LandingPageController,
    DashboardController,
    RatingController,
    OrderExportController,
};

use App\Exports\OrderExport;

// ======================
// Public Routes
// ======================
Route::get('/landing-page', [LandingPageController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);

// Public Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ======================
// Protected Routes (jwt.verify)
// ======================
Route::middleware('jwt.verify')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile & User Info
    Route::put('/user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/set-username', [UserController::class, 'setUsername']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/current-user', [UserController::class, 'currentUser']);

    // User Management (Admin Only)
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::get('/detail/{id}', [UserController::class, 'show']);
        Route::post('/create', [UserController::class, 'store']);
        Route::put('/update/{id}', [UserController::class, 'update']); 
        // Route::post('/delete/{id}', [UserController::class, 'update']); 
        Route::delete('/delete/{id}', [UserController::class, 'destroy']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });
    });

    // Products (Admin & Kelas)
    Route::middleware('role:admin,kelas')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // Product Price & Discount (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::put('/products/{id}/update-price', [ProductController::class, 'updatePrice']);
        Route::post('/products/{id}/apply-discount', [ProductController::class, 'applyDiscount']);
        Route::post('/price-requests/{id}/approve', [ProductController::class, 'approvePriceRequest']);
    });

    // Product Price Request (Kelas Only)
    Route::middleware('role:kelas')->post('/products/{id}/request-price', [ProductController::class, 'requestPrice']);

    // Cart (Pengguna)
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Favorites (Pengguna)
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{id}', [FavoriteController::class, 'destroy']);
    });

    // Chat
    Route::post('/chat/start', [ChatController::class, 'startChat']);
    Route::get('/chat', [ChatController::class, 'getChats']);
    Route::get('/chat/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::middleware('role:admin,kelas,pengguna')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{order}', [OrderController::class, 'show']);
        });

        Route::middleware('role:pengguna')->post('/', [OrderController::class, 'store']);
        Route::middleware('role:admin')->group(function () {
            Route::put('/{order}/status', [OrderController::class, 'updateStatus']);
            Route::delete('/{order}', [OrderController::class, 'destroy']);
        });
    });

    // Checkout (Pengguna)
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');

    // Discounts
    Route::get('/discounts', [DiscountController::class, 'index']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
    });
});
