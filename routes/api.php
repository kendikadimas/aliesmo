<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\HomepageVideoController;
use App\Http\Controllers\Api\ProductVideoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SiteSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::get('banners', [BannerController::class, 'index'])->middleware('throttle:60,1');
    Route::get('homepage-videos', [HomepageVideoController::class, 'index'])->middleware('throttle:60,1');
    Route::get('settings', [SiteSettingController::class, 'index'])->middleware('throttle:60,1');
    Route::get('settings/group/{group}', [SiteSettingController::class, 'group'])->middleware('throttle:60,1');
    Route::get('products', [ProductController::class, 'index'])->middleware('throttle:60,1');
    Route::get('products/{slug}', [ProductController::class, 'show'])->middleware('throttle:60,1');
    Route::get('categories', [CategoryController::class, 'index'])->middleware('throttle:60,1');

    // Articles
    Route::get('articles', [ArticleController::class, 'index'])->middleware('throttle:60,1')->name('api.articles.index');
    Route::get('articles/{slug}', [ArticleController::class, 'show'])->middleware('throttle:60,1')->name('api.articles.show');

    // Orders - guest checkout
    Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::get('orders/{orderNumber}/status', [OrderController::class, 'status'])->middleware('throttle:30,1');
    Route::post('orders/track', [OrderController::class, 'track'])->middleware('throttle:5,1');
    Route::get('orders/token/{token}', [OrderController::class, 'trackByToken'])->middleware('throttle:10,1');

    // Coupon validation (public - needed at checkout)
    Route::post('coupons/validate', [CouponController::class, 'validate'])->middleware('throttle:10,1');

    // Reviews (public read)
    Route::get('products/{slug}/videos', [ProductVideoController::class, 'index'])->middleware('throttle:60,1');
    Route::get('products/{slug}/reviews', [ReviewController::class, 'index'])->middleware('throttle:30,1');

    // Shipping (RajaOngkir)
    Route::get('shipping/provinces', [ShippingController::class, 'provinces'])->middleware('throttle:30,1');
    Route::get('shipping/cities/{provinceId}', [ShippingController::class, 'cities'])->middleware('throttle:30,1');
    Route::post('shipping/cost', [ShippingController::class, 'cost'])->middleware('throttle:20,1');
    Route::get('shipping/couriers', [ShippingController::class, 'couriers'])->middleware('throttle:30,1');
    Route::get('shipping/search', [ShippingController::class, 'search'])->middleware('throttle:30,1');

    // Auth - dengan rate limiting ketat untuk prevent brute force
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');   // 5 req/menit
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');         // 5 req/menit
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1'); // 3 req/menit
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);

        // Email verification
        Route::post('auth/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
            $request->fulfill();
            return response()->json(['message' => 'Email berhasil diverifikasi.']);
        })->middleware('signed')->name('verification.verify');

        Route::post('auth/email/resend', function (\Illuminate\Http\Request $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email sudah terverifikasi.'], 422);
            }
            $request->user()->sendEmailVerificationNotification();
            return response()->json(['message' => 'Link verifikasi sudah dikirim ke email kamu.']);
        })->middleware('throttle:3,1')->name('verification.send');

        // Orders
        Route::get('me/orders', [OrderController::class, 'myOrders']);
        Route::post('me/orders/claim', [OrderController::class, 'claimGuestOrders']);
        Route::get('me/orders/claimable-count', [OrderController::class, 'countClaimableOrders']);
        Route::get('me/orders/{orderNumber}', [OrderController::class, 'myOrder']);
        Route::delete('me/orders/{orderNumber}', [OrderController::class, 'cancel']);

        // Profile
        Route::get('me/profile', [ProfileController::class, 'show']);
        Route::put('me/profile', [ProfileController::class, 'update']);
        Route::put('me/password', [ProfileController::class, 'updatePassword']);

        // Reviews (write) — wajib email terverifikasi
        // Reviews (write) — proteksi via ownership check order + status completed di controller
        Route::post('products/{slug}/reviews', [ReviewController::class, 'store']);
    });
});
