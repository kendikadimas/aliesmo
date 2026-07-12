<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect ke Google OAuth consent screen.
     * Dipanggil dari browser langsung (bukan AJAX).
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Callback dari Google setelah user authorize.
     * Buat/temukan user, issue Sanctum token, redirect ke frontend dengan token.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            $frontendUrl = rtrim(config('app.url'), '/');
            return redirect("{$frontendUrl}/login?error=google_failed");
        }

        // Cari user existing berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Update google_id dan avatar jika belum ada
            $user->update([
                'google_id' => $user->google_id ?? $googleUser->getId(),
                'avatar'    => $user->avatar ?? $googleUser->getAvatar(),
            ]);
        } else {
            // Buat user baru — email_verified_at di-set via direct assignment
            // karena tidak ada di $fillable (cegah bypass via mass assignment)
            $user = User::create([
                'name'     => $googleUser->getName(),
                'email'    => $googleUser->getEmail(),
                'google_id'=> $googleUser->getId(),
                'avatar'   => $googleUser->getAvatar(),
                'password' => null,
            ]);
            // Google sudah verifikasi email — set langsung, bukan via mass assignment
            $user->email_verified_at = now();
            $user->save();
        }

        // Hapus token lama, issue token baru
        $user->tokens()->where('name', 'google-auth')->delete();
        $token = $user->createToken('google-auth')->plainTextToken;
        $this->claimGuestOrders($user);

        // Redirect ke frontend dengan token di query string
        // Frontend akan ambil token ini dan simpan ke localStorage
        $frontendUrl = rtrim(config('app.url'), '/');
        return redirect("{$frontendUrl}/auth/callback?token=" . urlencode($token));
    }

    private function claimGuestOrders(User $user): int
    {
        return DB::transaction(function () use ($user) {
            $orders = Order::where('customer_email', $user->email)
                ->whereNull('user_id')
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                $order->update(['user_id' => $user->id]);
            }

            if ($orders->isNotEmpty()) {
                Log::info('Guest orders claimed on Google auth', [
                    'user_id' => $user->id,
                    'count' => $orders->count(),
                    'orders' => $orders->pluck('order_number'),
                ]);
            }

            return $orders->count();
        });
    }
}
