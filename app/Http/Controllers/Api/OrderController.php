<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PublicOrderResource;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\NewOrderAdminNotification;
use App\Services\OrderService;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        return response()->json(['data' => new PublicOrderResource($order)]);
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

        return response()->json(['data' => new PublicOrderResource($order)]);
    }

    /**
     * Upload bukti pembayaran oleh customer.
     * Endpoint ini bisa diakses dengan lookup_token (guest) atau authenticated user.
     */
    public function uploadProof(\Illuminate\Http\Request $request, string $orderNumber)
    {
        Log::info('Upload proof started', [
            'order_number' => $orderNumber,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $request->validate([
                'proof_image' => ['required', 'image', 'max:5120'], // max 5MB
                'proof_note'  => ['nullable', 'string', 'max:500'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Upload proof validation failed', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
                'errors' => method_exists($e, 'errors') ? $e->errors() : null,
            ]);
            throw $e;
        }

        // Cari order — bisa via auth atau lookup_token
        $order = null;
        
        // Coba via auth
        if ($request->user('sanctum')) {
            Log::debug('Upload proof: trying auth user', [
                'user_id' => $request->user('sanctum')->id,
                'order_number' => $orderNumber,
            ]);
            $order = Order::where('order_number', $orderNumber)
                ->where('user_id', $request->user('sanctum')->id)
                ->first();
        }
        
        // Coba via lookup_token di header/query
        if (!$order) {
            $token = $request->header('X-Lookup-Token') ?? $request->query('token');
            Log::debug('Upload proof: trying lookup token', [
                'order_number' => $orderNumber,
                'has_token' => !empty($token),
                'token_valid' => $token ? preg_match('/^[a-zA-Z0-9]{32}$/', $token) : false,
            ]);
            if ($token && preg_match('/^[a-zA-Z0-9]{32}$/', $token)) {
                $order = Order::where('order_number', $orderNumber)
                    ->where('lookup_token', $token)
                    ->where('lookup_token_expires_at', '>', now())
                    ->first();
            }
        }

        if (!$order) {
            Log::warning('Upload proof: order not found', [
                'order_number' => $orderNumber,
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        Log::debug('Upload proof: order found', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'has_payment' => $order->payment !== null,
        ]);

        // Hanya bisa upload jika status pending
        if ($order->status !== OrderStatus::Pending) {
            Log::warning('Upload proof: order not pending', [
                'order_number' => $order->order_number,
                'current_status' => $order->status->value,
            ]);
            return response()->json(['message' => 'Bukti pembayaran sudah dikonfirmasi atau pesanan sudah diproses.'], 422);
        }

        try {
            // Simpan gambar
            $file = $request->file('proof_image');
            Log::debug('Upload proof: storing file', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_kb' => round($file->getSize() / 1024, 2),
            ]);
            
            // Disk local (private) — bukan public, cegah akses langsung via /storage
            $path = $file->store('payment-proofs', 'local');
            
            Log::debug('Upload proof: file stored', ['path' => $path]);

            // Update atau create payment record
            $payment = $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'gateway'   => 'manual',
                    'amount'    => $order->total,
                    'status'    => PaymentStatus::Pending,
                    'proof_image' => $path,
                    'proof_note'  => $request->input('proof_note'),
                ]
            );

            Log::info('Upload proof: SUCCESS', [
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
                'proof_path' => $path,
                'amount' => $order->total,
            ]);

            // Ubah status order ke processing — bukti sudah diterima, menunggu verifikasi admin
            $order->update(['status' => OrderStatus::Processing]);

            return response()->json([
                'message' => 'Bukti pembayaran berhasil diunggah. Admin akan memverifikasi pembayaran Anda.',
                'order'   => new OrderResource($order->fresh()),
            ]);
        } catch (\Throwable $e) {
            Log::error('Upload proof: FAILED', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Gagal mengunggah bukti pembayaran. Silakan coba lagi.'], 500);
        }
    }

    public function store(StoreOrderRequest $request)
    {
        Log::info('Order creation started', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'item_count' => count($request->items ?? []),
            'payment_method' => $request->input('payment_method'),
            'shipping_courier' => $request->input('shipping_courier'),
            'has_coupon' => !empty($request->coupon_code),
        ]);

        try {
            $couponDiscount = 0;
            $coupon = null;

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if (!$coupon || !$coupon->isValid()) {
                    Log::warning('Order creation: invalid coupon', [
                        'coupon_code' => $request->coupon_code,
                        'found' => $coupon !== null,
                        'is_valid' => $coupon?->isValid(),
                    ]);
                    return response()->json(['message' => 'Kode kupon tidak valid atau sudah kadaluarsa.'], 422);
                }
                Log::debug('Order creation: coupon applied', ['coupon_code' => $coupon->code]);
            }

            $customerData = $request->only([
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_address',
                'shipping_area_id',
                'shipping_cache_key',
                'shipping_courier',
                'shipping_service',
                'selected_bank',
                'destination_lat',
                'destination_lng',
            ]);
            $customerData['payment_method'] = $request->input('payment_method', 'bank_transfer');

            Log::debug('Order creation: calling createFromCart', [
                'customer_email' => $customerData['customer_email'] ?? null,
                'shipping_courier' => $customerData['shipping_courier'] ?? null,
                'shipping_area_id' => $customerData['shipping_area_id'] ?? null,
            ]);

            $order = $this->orderService->createFromCart(
                $request->items,
                $customerData,
                $coupon
            );

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'item_count' => $order->items->count(),
            ]);

            $order->load(['items.product']);

            // Queue email notifications (async) — doesn't block response
            try {
                \App\Jobs\SendOrderNotifications::dispatch($order);
                Log::debug('Order notifications queued', ['order' => $order->order_number]);
            } catch (\Throwable $e) {
                Log::error('Failed to queue notifications', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }

            $whatsappNumber = $this->whatsappNumber();
            $message = $this->generateWhatsAppMessage($order);

            // Kirim info pembayaran sesuai metode yang dipilih
            $paymentInfo = $this->getPaymentInfo($order->payment_method ?? 'bank_transfer', $order->selected_bank);

            Log::info('Order store completed', [
                'order_number' => $order->order_number,
                'whatsapp_number' => $whatsappNumber,
            ]);

            return response()->json([
                'order'             => new OrderResource($order),
                'whatsapp_number'   => $whatsappNumber,
                'whatsapp_message'  => $message,
                'coupon_discount'   => (float) ($order->coupon_discount ?? 0),
                'payment_info'      => $paymentInfo,
            ], 201);
        } catch (\RuntimeException $e) {
            Log::error('Order creation failed (Runtime)', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Order creation failed (Unexpected)', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.'], 500);
        }
    }

    /**
     * Ambil info pembayaran dari site_settings berdasarkan metode.
     */
    private function getPaymentInfo(?string $method, ?string $selectedBank = null): array
    {
        $method ??= 'bank_transfer';

        return match ($method) {
            'bank_transfer' => [
                'method'       => 'bank_transfer',
                'label'        => 'Transfer Bank',
                'banks'        => $this->resolveBanks($selectedBank),
                'instruction'  => 'Setelah transfer, kirim bukti pembayaran via WhatsApp.',
            ],
            'qris' => [
                'method'      => 'qris',
                'label'       => 'QRIS',
                'qris_image'  => ($img = \App\Models\SiteSetting::get('payment_qris_image', null)) ? asset('storage/' . $img) : null,
                'qris_name'   => \App\Models\SiteSetting::get('payment_qris_name', 'Aliesmo'),
                'instruction' => 'Scan QRIS di atas, lalu kirim bukti pembayaran via WhatsApp.',
            ],
            'cod' => [
                'method'      => 'cod',
                'label'       => 'COD (Bayar di Tempat)',
                'instruction' => 'Bayar langsung saat pesanan tiba. Konfirmasi via WhatsApp untuk penjadwalan.',
            ],
            default => [],
        };
    }

    private function resolveBanks(?string $selectedBank): array
    {
        $allBanks = array_values(\App\Models\SiteSetting::get('payment_banks', []));
        if (!$selectedBank) return $allBanks;
        $filtered = array_filter($allBanks, fn($b) => strtolower($b['bank_name'] ?? '') === strtolower($selectedBank));
        return array_values($filtered) ?: $allBanks;
    }

    private function generateWhatsAppMessage(Order $order): string
    {
        $paymentLabels = [
            'bank_transfer' => 'Transfer Bank',
            'qris'          => 'QRIS',
            'cod'           => 'COD (Bayar di Tempat)',
        ];
        $paymentLabel = $paymentLabels[$order->payment_method] ?? 'Transfer Bank';

        $message = "Halo, saya ingin konfirmasi pesanan:\n\n";
        $message .= "*Order #{$order->order_number}*\n";
        $message .= "*Metode Pembayaran: {$paymentLabel}*\n\n";
        $message .= "*Detail Pesanan:*\n";

        foreach ($order->items as $item) {
            $variantInfo = $item->variant_name ? " ({$item->variant_name})" : '';
            $message .= "• {$item->product_name}{$variantInfo} x{$item->quantity} - Rp" . number_format($item->subtotal, 0, ',', '.') . "\n";
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

        if ($order->payment_method === 'bank_transfer') {
            $banks = array_values(\App\Models\SiteSetting::get('payment_banks', []));
            $message .= "\n\n*Info Transfer:*";
            foreach ($banks as $bank) {
                $message .= "\nBank: {$bank['bank_name']}\n";
                $message .= "No. Rekening: {$bank['account_no']}\n";
                $message .= "Atas Nama: {$bank['account_name']}\n";
            }
            $message .= "_Mohon lampirkan bukti transfer._";
        } elseif ($order->payment_method === 'qris') {
            $message .= "\n\n_Mohon lampirkan bukti pembayaran QRIS._";
        }

        return urlencode($message);
    }

    public function status(string $orderNumber)
    {
        Log::debug('Order status check', [
            'order_number' => $orderNumber,
            'ip' => request()->ip(),
        ]);

        // Validasi format order number sebelum hit DB (MED-4)
        // Terima format normal (ORD-YYYYMMDD-XXXX-XXX) dan format lama dengan double dash
        if (!preg_match('/^ORD-\d{8}--?\d{4}-[A-Z0-9]{3}$/', strtoupper($orderNumber))) {
            Log::warning('Order status: invalid format', ['order_number' => $orderNumber]);
            abort(404);
        }

        $order = Order::where('order_number', strtoupper($orderNumber))
            ->with(['items.product', 'payment'])
            ->first();

        if (!$order) {
            Log::warning('Order status: not found', ['order_number' => $orderNumber]);
            abort(404);
        }

        $user = auth('sanctum')->user();

        // Order milik user tertentu — wajib login sebagai owner
        if ($order->user_id && (!$user || $order->user_id !== $user->id)) {
            Log::warning('Order status: unauthorized access attempt', [
                'order_number' => $orderNumber,
                'order_user_id' => $order->user_id,
                'request_user_id' => $user?->id,
                'ip' => request()->ip(),
            ]);
            abort(403, 'Unauthorized access to order.');
        }

        // Guest order — return data tapi TANPA field sensitif (IDOR fix)
        // Field sensitif hanya dikembalikan kalau user login sebagai owner
        $isOwner = $user && $order->user_id === $user->id;

        Log::debug('Order status: returning data', [
            'order_number' => $orderNumber,
            'status' => $order->status->value,
            'is_owner' => $isOwner,
            'has_payment' => $order->payment !== null,
            'has_proof' => $order->payment?->proof_image !== null,
        ]);

        $hasProof = $order->payment?->proof_image !== null;

        $data = [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'status'           => $order->status->value,
            'subtotal'         => (float) $order->subtotal,
            'shipping_cost'    => (float) $order->shipping_cost,
            'coupon_code'      => $order->coupon_code,
            'coupon_discount'  => (float) ($order->coupon_discount ?? 0),
            'total'            => (float) $order->total,
            'payment_method'   => $order->payment_method,
            'courier'          => $order->courier,
            'tracking_number'  => $order->tracking_number,
            'tracking_url'     => $order->tracking_url,
            'created_at'       => $order->created_at,
            'items'            => $order->items->map(fn($i) => [
                'product_name'  => $i->product_name,
                'product_slug'  => $i->product?->slug,
                'product_image' => $i->product?->thumbnail
                    ? (str_starts_with($i->product->thumbnail, 'http')
                        ? $i->product->thumbnail
                        : asset('storage/' . $i->product->thumbnail))
                    : null,
                'variant_name'  => $i->variant_name,
                'quantity'      => $i->quantity,
                'price'         => (float) $i->price,
                'subtotal'      => (float) $i->subtotal,
            ]),
            // Field sensitif hanya untuk owner yang login
            'customer_name'    => $isOwner || !$order->user_id ? $order->customer_name    : null,
            'customer_email'   => $isOwner ? $order->customer_email   : $this->maskEmail($order->customer_email),
            'customer_phone'   => $isOwner ? $order->customer_phone   : null,
            'shipping_address'    => $isOwner || !$order->user_id ? $order->shipping_address : null,
            // Info resi & pengiriman
            'courier'            => $order->courier,
            'courier_service'    => $order->courier_service,
            'tracking_number'    => $order->tracking_number,
            'tracking_url'       => $order->tracking_url,
            'biteship_status'    => $order->biteship_status,
            'biteship_waybill_id'=> $order->biteship_waybill_id,
            // Jangan expose path bukti — cukup flag boolean (file di disk private)
            'payment'            => $order->payment ? [
                'proof_image' => $hasProof, // boolean: sudah upload atau belum
                'status'      => $order->payment->status->value,
            ] : null,
        ];

        return response()->json([
            'data'            => $data,
            'whatsapp_number' => $this->whatsappNumber(),
            'payment_info'    => $this->getPaymentInfo($order->payment_method ?? 'bank_transfer', $order->selected_bank),
        ]);
    }

    private function whatsappNumber(): string
    {
        return (string) SiteSetting::get('whatsapp_number', config('services.whatsapp.number', '628138883345'));
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

    public function myOrder(string $orderNumber)
    {
        $order = auth()->user()->orders()
            ->where('order_number', strtoupper($orderNumber))
            ->with(['items.product', 'payment'])
            ->firstOrFail();

        return response()->json([
            'data'            => new OrderResource($order),
            'whatsapp_number' => $this->whatsappNumber(),
            'payment_info'    => $this->getPaymentInfo($order->payment_method ?? 'bank_transfer', $order->selected_bank),
        ]);
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

    /**
     * Claim semua guest order yang emailnya cocok dengan user yang sedang login.
     * Order dengan user_id = null dan customer_email = email user akan di-assign ke user ini.
     */
    public function claimGuestOrders()
    {
        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Verifikasi email dulu sebelum mengklaim pesanan.',
                'claimed_count' => 0,
            ], 403);
        }

        $claimed = DB::transaction(function () use ($user) {
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

            Log::info('Guest orders claimed', [
                'user_id' => $user->id,
                'count'   => $orders->count(),
                'orders'  => $orders->pluck('order_number'),
            ]);

            return $orders->count();
        });

        return response()->json([
            'message' => $claimed > 0
                ? "{$claimed} pesanan berhasil diklaim ke akun kamu."
                : 'Tidak ada pesanan guest yang bisa diklaim.',
            'claimed_count' => $claimed,
        ]);
    }

    /**
     * Cek berapa banyak guest order yang bisa diklaim — untuk prompt di frontend.
     */
    public function countClaimableOrders()
    {
        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['claimable_count' => 0]);
        }

        $count = Order::where('customer_email', $user->email)
            ->whereNull('user_id')
            ->count();

        return response()->json(['claimable_count' => $count]);
    }
}
