<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::get('products', [ProductController::class, 'index'])->middleware('throttle:60,1');
    Route::get('products/{slug}', [ProductController::class, 'show'])->middleware('throttle:60,1');
    Route::get('categories', [CategoryController::class, 'index'])->middleware('throttle:60,1');

    // Orders - guest checkout
    Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::get('orders/{orderNumber}/status', [OrderController::class, 'status'])->middleware('throttle:30,1');
    Route::post('orders/track', [OrderController::class, 'track'])->middleware('throttle:5,1');
    Route::get('orders/token/{token}', [OrderController::class, 'trackByToken'])->middleware('throttle:10,1');

    // Coupon validation (public - needed at checkout)
    Route::post('coupons/validate', [CouponController::class, 'validate'])->middleware('throttle:10,1');

    // Reviews (public read)
    Route::get('products/{slug}/reviews', [ReviewController::class, 'index']);

    // Payment callback (no CSRF, no auth - server-to-server)
    Route::post('payments/callback/midtrans', [PaymentController::class, 'callback'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('throttle:20,1');

    // Shipping (RajaOngkir)
    Route::get('shipping/provinces', [ShippingController::class, 'provinces'])->middleware('throttle:30,1');
    Route::get('shipping/cities/{provinceId}', [ShippingController::class, 'cities'])->middleware('throttle:30,1');
    Route::post('shipping/cost', [ShippingController::class, 'cost'])->middleware('throttle:20,1');
    Route::get('shipping/couriers', [ShippingController::class, 'couriers'])->middleware('throttle:30,1');
    Route::get('shipping/search', [ShippingController::class, 'search'])->middleware('throttle:30,1');

    // Auth - dengan rate limiting ketat untuk prevent brute force
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // 5 req/menit
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 5 req/menit
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1'); // 3 req/menit
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Orders
        Route::get('me/orders', [OrderController::class, 'myOrders']);
        Route::delete('me/orders/{orderNumber}', [OrderController::class, 'cancel']);

        // Profile
        Route::get('me/profile', [ProfileController::class, 'show']);
        Route::put('me/profile', [ProfileController::class, 'update']);
        Route::put('me/password', [ProfileController::class, 'updatePassword']);

        // Reviews (write)
        Route::post('products/{slug}/reviews', [ReviewController::class, 'store']);
    });
});
