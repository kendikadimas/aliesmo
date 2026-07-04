<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Coupon;
use App\Notifications\OrderConfirmationNotification;
use App\Services\OrderService;
use App\Http\Requests\Api\StoreOrderRequest;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function track(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email'        => 'required|email|max:255',
            'order_number' => 'required|string|max:50',
        ]);

        // Intentional delay — mencegah timing attack untuk email enumeration
        usleep(random_int(100000, 300000));

        $order = Order::where('order_number', strtoupper(trim($request->order_number)))
            ->where('customer_email', strtolower(trim($request->email)))
            ->with('items')
            ->first();

        // Pesan error seragam — tidak membedakan "email salah" vs "order number salah"
        // Mencegah email enumeration attack
        if (!$order) {
            \Illuminate\Support\Facades\Log::warning('Order track failed', [
                'ip'           => $request->ip(),
                'order_number' => $request->order_number,
                'at'           => now()->toIso8601String(),
            ]);
            return response()->json(['message' => 'Pesanan tidak ditemukan. Pastikan email dan nomor pesanan sudah benar.'], 404);
        }

        return response()->json(['data' => new OrderResource($order)]);
    }

    public function trackByToken(string $token)
    {
        // Validasi format token — hanya huruf & angka, panjang 32
        if (!preg_match('/^[a-zA-Z0-9]{32}$/', $token)) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $order = Order::where('lookup_token', $token)
            ->where('lookup_token_expires_at', '>', now())
            ->with('items')
            ->first();

        if (!$order) {
            \Illuminate\Support\Facades\Log::warning('Order token lookup failed', [
                'ip'    => request()->ip(),
                'token' => substr($token, 0, 8) . '...', // Jangan log token penuh
                'at'    => now()->toIso8601String(),
            ]);
            return response()->json(['message' => 'Link pesanan tidak valid atau sudah kadaluarsa.'], 404);
        }

        return response()->json(['data' => new OrderResource($order)]);
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $couponDiscount = 0;
            $coupon = null;

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if (!$coupon || !$coupon->isValid()) {
                    return response()->json(['message' => 'Kode kupon tidak valid atau sudah kadaluarsa.'], 422);
                }
            }

            $order = $this->orderService->createFromCart(
                $request->items,
                $request->only(['customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'shipping_cost']),
                $coupon
            );

            $order->load(['items']);

            // Kirim order confirmation email jika ada user login
            if ($order->user_id) {
                $order->user->notify(new OrderConfirmationNotification($order));
            } else {
                // Guest checkout — kirim ke customer_email
                $guestUser = (object) ['email' => $order->customer_email, 'routeNotificationForMail' => fn() => $order->customer_email];
                \Illuminate\Support\Facades\Notification::route('mail', $order->customer_email)
                    ->notify(new OrderConfirmationNotification($order));
            }

            $whatsappNumber = config('services.whatsapp.number');
            $message = $this->generateWhatsAppMessage($order);

            return response()->json([
                'order'             => new OrderResource($order),
                'whatsapp_number'   => $whatsappNumber,
                'whatsapp_message'  => $message,
                'coupon_discount'   => (float) ($order->coupon_discount ?? 0),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function generateWhatsAppMessage(Order $order): string
    {
        $message = "Halo, saya ingin konfirmasi pesanan:\n\n";
        $message .= "*Order #{$order->order_number}*\n\n";
        $message .= "*Detail Pesanan:*\n";

        foreach ($order->items as $item) {
            $message .= "• {$item->product_name} x{$item->quantity} - Rp" . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $message .= "\n*Subtotal:* Rp" . number_format($order->subtotal, 0, ',', '.') . "\n";
        if ($order->coupon_discount > 0) {
            $message .= "*Diskon:* -Rp" . number_format($order->coupon_discount, 0, ',', '.') . "\n";
        }
        $message .= "*Ongkir:* Rp" . number_format($order->shipping_cost, 0, ',', '.') . "\n";
        $message .= "*Total:* Rp" . number_format($order->total, 0, ',', '.') . "\n\n";
        $message .= "*Alamat Pengiriman:*\n{$order->shipping_address}\n\n";
        $message .= "*Data Pemesan:*\n";
        $message .= "Nama: {$order->customer_name}\n";
        $message .= "Email: {$order->customer_email}\n";
        $message .= "Telepon: {$order->customer_phone}";

        return urlencode($message);
    }

    public function status(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'payment'])
            ->firstOrFail();

        $user = auth('sanctum')->user();

        // Order milik user tertentu — wajib login sebagai owner
        if ($order->user_id && (!$user || $order->user_id !== $user->id)) {
            abort(403, 'Unauthorized access to order.');
        }

        // Guest order — return data tapi TANPA field sensitif (IDOR fix)
        // Field sensitif hanya dikembalikan kalau user login sebagai owner
        $isOwner = $user && $order->user_id === $user->id;

        $data = [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'status'           => $order->status->value,
            'subtotal'         => (float) $order->subtotal,
            'shipping_cost'    => (float) $order->shipping_cost,
            'coupon_code'      => $order->coupon_code,
            'coupon_discount'  => (float) ($order->coupon_discount ?? 0),
            'total'            => (float) $order->total,
            'created_at'       => $order->created_at,
            'items'            => $order->items->map(fn($i) => [
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'price'        => (float) $i->price,
                'subtotal'     => (float) $i->subtotal,
            ]),
            // Field sensitif hanya untuk owner yang login
            'customer_name'    => $isOwner || !$order->user_id ? $order->customer_name    : null,
            'customer_email'   => $isOwner ? $order->customer_email   : $this->maskEmail($order->customer_email),
            'customer_phone'   => $isOwner ? $order->customer_phone   : null,
            'shipping_address' => $isOwner || !$order->user_id ? $order->shipping_address : null,
        ];

        return response()->json([
            'data'            => $data,
            'whatsapp_number' => config('services.whatsapp.number'),
        ]);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return $masked . '@' . $domain;
    }

    public function myOrders()
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $user  = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($order->status->value, ['pending'])) {
            return response()->json(['message' => 'Pesanan hanya bisa dibatalkan saat status Pending.'], 422);
        }

        $this->orderService->cancel($order, 'Dibatalkan oleh customer');

        return response()->json(['message' => 'Pesanan berhasil dibatalkan.']);
    }
}
