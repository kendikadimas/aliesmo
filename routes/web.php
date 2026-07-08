<?php

use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Support\Facades\Route;

// Google OAuth — harus di-register sebelum SPA catch-all
Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

Route::get('/auth/callback', function () {
    return view('welcome');
})->name('auth.callback');

// SPA catch-all — semua route non-API dan non-admin di-handle Vue Router
// Urutan penting: Filament /admin dan /api sudah di-register sebelum ini
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api|admin|storage|build|auth).*$');
