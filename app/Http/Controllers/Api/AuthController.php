<?php
namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Models\Order;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => UserRole::Customer,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        // Kirim welcome email (queued, tidak block response)
        $user->notify(new WelcomeNotification($user));

        $claimedCount = $this->claimGuestOrders($user);

        return response()->json([
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'token' => $token,
            'claimed_orders' => $claimedCount,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Blokir admin dari login di frontend — arahkan ke admin panel
        if ($user->role !== UserRole::Customer) {
            return response()->json(['message' => 'Akun ini bukan akun pelanggan. Silakan login melalui halaman admin.'], 403);
        }

        // Revoke semua token lama — cegah akumulasi token aktif (HIGH-2)
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        // Auto-claim guest orders yang emailnya cocok — seamless UX
        $claimedCount = $this->claimGuestOrders($user);

        return response()->json([
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'token' => $token,
            'claimed_orders' => $claimedCount,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        // Revoke semua token aktif di semua perangkat (HIGH-2)
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Berhasil keluar dari semua perangkat.']);
    }

    /**
     * Assign semua guest order dengan email yang sama ke user ini.
     * Dipanggil otomatis saat login.
     */
    private function claimGuestOrders(User $user): int
    {
        return DB::transaction(function () use ($user) {
            $orders = Order::where('customer_email', $user->email)
                ->whereNull('user_id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return 0;
            }

            foreach ($orders as $order) {
                $order->update(['user_id' => $user->id]);
            }

            Log::info('Guest orders auto-claimed on login', [
                'user_id' => $user->id,
                'count'   => $orders->count(),
                'orders'  => $orders->pluck('order_number'),
            ]);

            return $orders->count();
        });
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // Intentional delay — mencegah timing attack untuk email enumeration
        usleep(random_int(100000, 300000));

        $status = Password::sendResetLink($request->only('email'));

        // Pesan seragam — tidak membedakan "email terdaftar" vs "email tidak terdaftar"
        // Mencegah email enumeration attack
        return response()->json(['message' => 'Jika email kamu terdaftar, link reset password sudah dikirim.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
        }

        return response()->json(['message' => 'Token tidak valid atau sudah expired.'], 422);
    }
}
