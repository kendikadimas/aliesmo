<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\BiteshipWebhookController;
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
    // local: longgar buat dev/test; prod tetap ketat
    $isLocal = app()->environment('local');
    $t = fn (int $prod, int $local = 120) => 'throttle:'.($isLocal ? $local : $prod).',1';

    // Public
    Route::get('banners', [BannerController::class, 'index'])->middleware($t(60));
    Route::get('testimonials', [TestimonialController::class, 'index'])->middleware($t(60));
    Route::get('homepage-videos', [HomepageVideoController::class, 'index'])->middleware($t(60));
    Route::get('settings', [SiteSettingController::class, 'index'])->middleware($t(60));
    Route::get('settings/group/{group}', [SiteSettingController::class, 'group'])->middleware($t(60));
    Route::get('products', [ProductController::class, 'index'])->middleware($t(60));
    Route::get('products/{slug}', [ProductController::class, 'show'])->middleware($t(60));
    Route::get('categories', [CategoryController::class, 'index'])->middleware($t(60));

    // Articles
    Route::get('articles', [ArticleController::class, 'index'])->middleware($t(60))->name('api.articles.index');
    Route::get('articles/{slug}', [ArticleController::class, 'show'])->middleware($t(60))->name('api.articles.show');

    // Orders - guest checkout (prod: 10 order/menit per IP)
    Route::post('orders', [OrderController::class, 'store'])->middleware($t(10));
    Route::get('orders/{orderNumber}/status', [OrderController::class, 'status'])->middleware($t(30));
    Route::post('orders/track', [OrderController::class, 'track'])->middleware($t(5));
    Route::get('orders/token/{token}', [OrderController::class, 'trackByToken'])->middleware($t(10));

    // Upload bukti pembayaran — bisa diakses guest dengan lookup_token
    Route::post('orders/{orderNumber}/payment-proof', [OrderController::class, 'uploadProof'])->middleware($t(10));

    // Coupon validation (public - needed at checkout)
    Route::post('coupons/validate', [CouponController::class, 'validate'])->middleware($t(10));

    // Reviews (public read)
    Route::get('products/{slug}/videos', [ProductVideoController::class, 'index'])->middleware($t(60));
    Route::get('products/{slug}/reviews', [ReviewController::class, 'index'])->middleware($t(30));

    // Shipping (Biteship only) — cost sering dipanggil berulang saat pilih alamat
    Route::post('shipping/cost', [ShippingController::class, 'cost'])->middleware($t(20, 180));
    Route::get('shipping/couriers', [ShippingController::class, 'couriers'])->middleware($t(30));
    Route::get('shipping/search', [ShippingController::class, 'search'])->middleware($t(30));

    // Auth - rate limit ketat di prod (brute force)
    Route::post('auth/register', [AuthController::class, 'register'])->middleware($t(5, 30));
    Route::post('auth/login', [AuthController::class, 'login'])->middleware($t(5, 30));
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware($t(3, 20));
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware($t(5, 30));

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

// Biteship Webhook — outside auth, protected by webhook secret
Route::post('v1/webhooks/biteship', [BiteshipWebhookController::class, 'handle'])
    ->middleware('throttle:60,1');
