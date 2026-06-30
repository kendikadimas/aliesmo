<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);

    // Orders - guest checkout
    Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::get('orders/{orderNumber}/status', [OrderController::class, 'status']);

    // Payment callback (no CSRF, no auth - server-to-server)
    Route::post('payments/callback/midtrans', [PaymentController::class, 'callback'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('throttle:20,1');

    // Shipping (RajaOngkir)
    Route::get('shipping/provinces', [ShippingController::class, 'provinces']);
    Route::get('shipping/cities/{provinceId}', [ShippingController::class, 'cities']);
    Route::post('shipping/cost', [ShippingController::class, 'cost']);
    Route::get('shipping/couriers', [ShippingController::class, 'couriers']);

    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me/orders', [OrderController::class, 'myOrders']);
    });
});
