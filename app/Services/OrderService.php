<?php
namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantSize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private StockService $stockService,
        private BiteshipService $biteshipService
    ) {}

    public function createFromCart(array $items, array $customerData, ?Coupon $coupon = null): Order
    {
        return DB::transaction(function () use ($items, $customerData, $coupon) {
            $subtotal   = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if (!$product->is_active) {
                    throw new \RuntimeException("Product '{$product->name}' is not available.");
                }

                // Jika ada variant_id, validasi dan pakai stok + harga varian
                $variant     = null;
                $size        = null;
                $itemPrice   = $product->price;
                $variantName = null;
                $sizeName    = null;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::lockForUpdate()
                        ->where('id', $item['variant_id'])
                        ->where('product_id', $product->id)
                        ->firstOrFail();

                    if (!$variant->is_active) {
                        throw new \RuntimeException(
                            "Variant '{$variant->name}' of product '{$product->name}' is not available."
                        );
                    }

                    // Jika ada size_id, validasi size dan pakai stok size
                    if (!empty($item['size_id'])) {
                        $size = ProductVariantSize::lockForUpdate()
                            ->where('id', $item['size_id'])
                            ->where('variant_id', $variant->id)
                            ->firstOrFail();

                        if (!$size->is_active) {
                            throw new \RuntimeException(
                                "Size '{$size->name}' of variant '{$variant->name}' is not available."
                            );
                        }

                        if ($size->stock < $item['quantity']) {
                            throw new \RuntimeException(
                                "Insufficient stock for '{$product->name}' {$variant->name} - {$size->name}. Available: {$size->stock}, requested: {$item['quantity']}."
                            );
                        }

                        $sizeName = $size->name;
                    } else {
                        // Variant tanpa size — cek stok varian langsung
                        if ($variant->stock < $item['quantity']) {
                            throw new \RuntimeException(
                                "Insufficient stock for '{$product->name}' variant '{$variant->name}'. Available: {$variant->stock}, requested: {$item['quantity']}."
                            );
                        }
                    }

                    $itemPrice   = $variant->price;
                    $variantName = $variant->name;
                } else {
                    // Tidak ada varian — pakai stok produk langsung
                    if ($product->stock < $item['quantity']) {
                        throw new \RuntimeException(
                            "Insufficient stock for '{$product->name}'. Available: {$product->stock}, requested: {$item['quantity']}."
                        );
                    }
                }

                $lineSubtotal = $itemPrice * $item['quantity'];
                $subtotal    += $lineSubtotal;

                $orderItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'variant_id'   => $variant?->id,
                    'variant_name' => $variantName,
                    'size_id'      => $size?->id,
                    'size_name'    => $sizeName,
                    'price'        => $itemPrice,
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $lineSubtotal,
                ];
            }

            // Resolve shipping cost dari cache server-side — TIDAK dari client input
            // Ini mencegah manipulasi ongkir oleh client (CRIT-2)
            $shippingCost = $this->resolveShippingCost(
                $customerData['shipping_cache_key'],
                $customerData['shipping_courier'],
                $customerData['shipping_service']
            );
            $couponDiscount  = 0;
            $couponCode      = null;

            if ($coupon) {
                // lockForUpdate — cegah race condition coupon double-redeem
                $coupon = Coupon::lockForUpdate()->find($coupon->id);
                if ($coupon && $coupon->isValid()) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $couponCode     = $coupon->code;
                    $coupon->increment('used_count');
                }
            }

            $total = max(0, $subtotal - $couponDiscount + $shippingCost);

            // Normalize kurir — cover kode mentah (jne, jnt) dan nama display (J&T Express, dll)
            $courierTracking = [
                'JNE'           => 'https://jne.co.id/tracking-package',
                'JNT Express'   => 'https://jet.co.id/track',
                'Pos Indonesia' => 'https://www.posindonesia.co.id/id/tracking',
            ];
            // Map dari lowercase (kode atau nama display) ke canonical name
            $courierMap = [
                'jne'           => 'JNE',
                'jnt'           => 'JNT Express',
                'j&t'           => 'JNT Express',
                'j&t express'   => 'JNT Express',
                'jnt express'   => 'JNT Express',
                'pos'           => 'Pos Indonesia',
                'pos indonesia' => 'Pos Indonesia',
            ];
            $rawCourier  = strtolower(trim($customerData['shipping_courier'] ?? ''));
            $courierName = $courierMap[$rawCourier] ?? null;
            // Fallback: jika yang dikirim sudah canonical name yang valid, pakai langsung
            if (!$courierName && isset($courierTracking[$customerData['shipping_courier'] ?? ''])) {
                $courierName = $customerData['shipping_courier'];
            }
            $courierName = $courierName ?: strtoupper($rawCourier);
            $courierUrl  = $courierTracking[$courierName] ?? null;

            $order = Order::create([
                'order_number'           => $this->generateOrderNumber(),
                'lookup_token'           => \Illuminate\Support\Str::random(32),
                'lookup_token_expires_at' => now()->addDays(7),
                'user_id'                => request()->user('sanctum')?->id,
                'customer_name'   => $customerData['customer_name'],
                'customer_email'  => $customerData['customer_email'],
                'customer_phone'  => $customerData['customer_phone'] ?? null,
                'shipping_address'=> $customerData['shipping_address'],
                'shipping_area_id'=> $customerData['shipping_area_id'] ?? null,
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'coupon_discount' => $couponDiscount,
                'coupon_code'     => $couponCode,
                'total'           => $total,
                'status'          => OrderStatus::Pending,
                'payment_method'  => $customerData['payment_method'] ?? 'bank_transfer',
                'selected_bank'   => $customerData['selected_bank'] ?? null,
                'courier'         => $courierName ?: null,
                'tracking_url'    => $courierUrl,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });
    }

    public function markAsPaid(Order $order, string $paymentMethod = 'whatsapp'): void
    {
        DB::transaction(function () use ($order, $paymentMethod) {
            $order->update([
                'status'         => OrderStatus::Paid,
                'payment_method' => $paymentMethod,
                'paid_at'        => now(),
            ]);

            $this->stockService->decrementForOrder($order);

            Log::info('Order marked as paid', ['order' => $order->order_number]);

            // Buat order pengiriman di Biteship setelah pembayaran dikonfirmasi
            $this->createBiteshipShipment($order);
        });
    }

    /**
     * Buat order pengiriman di Biteship.
     * Dipanggil setelah order dibayar agar saldo Biteship tidak terpotong jika order dibatalkan.
     */
    private function createBiteshipShipment(Order $order): void
    {
        try {
            // Skip jika tidak ada area_id atau courier
            if (empty($order->shipping_area_id) || empty($order->courier)) {
                Log::warning('Skip Biteship order: missing area_id or courier', [
                    'order' => $order->order_number,
                    'area_id' => $order->shipping_area_id,
                    'courier' => $order->courier,
                ]);
                return;
            }

            // Skip jika sudah ada biteship_order_id (idempotent)
            if (!empty($order->biteship_order_id)) {
                Log::info('Biteship order already exists', [
                    'order' => $order->order_number,
                    'biteship_id' => $order->biteship_order_id,
                ]);
                return;
            }

            // Siapkan items dari order items
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'name'     => $item->product_name . ($item->variant_name ? " ({$item->variant_name})" : '') . ($item->size_name ? " - {$item->size_name}" : ''),
                    'value'    => $item->price,
                    'quantity' => $item->quantity,
                    'weight'   => 300, // default 300g per item (bisa dihitung dari produk)
                ];
            }

            // Map courier name ke code untuk Biteship
            $courierMap = [
                'JNE'           => ['company' => 'jne', 'type' => 'reg'],
                'JNT Express'   => ['company' => 'jnt', 'type' => 'reg'],
                'Pos Indonesia' => ['company' => 'pos', 'type' => 'reg'],
            ];

            $courierCode = $courierMap[$order->courier] ?? ['company' => strtolower($order->courier), 'type' => 'reg'];

            $result = $this->biteshipService->createOrder([
                'order_number'     => $order->order_number,
                'customer_name'    => $order->customer_name,
                'customer_phone'   => $order->customer_phone,
                'customer_email'   => $order->customer_email,
                'shipping_address' => $order->shipping_address,
                'shipping_area_id' => $order->shipping_area_id,
                'courier_company'  => $courierCode['company'],
                'courier_type'     => $courierCode['type'],
                'items'            => $items,
            ]);

            // Simpan data Biteship ke order
            $order->update([
                'biteship_order_id'    => $result['biteship_order_id'],
                'biteship_tracking_id' => $result['biteship_tracking_id'],
                'biteship_waybill_id'  => $result['biteship_waybill_id'],
                'biteship_status'      => $result['biteship_status'],
                'tracking_number'      => $result['biteship_waybill_id'] ?? $order->tracking_number,
            ]);

            Log::info('Biteship order created successfully', [
                'order'        => $order->order_number,
                'biteship_id'  => $result['biteship_order_id'],
                'waybill_id'   => $result['biteship_waybill_id'],
                'status'       => $result['biteship_status'],
                'price'        => $result['price'],
            ]);

        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan order — admin bisa manual create shipment
            Log::error('Gagal membuat Biteship order', [
                'order'  => $order->order_number,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public function cancel(Order $order, string $reason = ''): void
    {
        DB::transaction(function () use ($order, $reason) {
            if ($order->status === OrderStatus::Paid) {
                foreach ($order->items as $item) {
                    $this->stockService->adjustStock(
                        $item->product_id,
                        $item->quantity,
                        StockMovementType::Return,
                        "Cancelled order #{$order->order_number}: {$reason}"
                    );
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
        });
    }

    /**
     * Resolve ongkir dari cache server-side berdasarkan cache_key, courier, dan service.
     * Mencegah manipulasi ongkir oleh client (CRIT-2).
     *
     * @throws \RuntimeException jika cache tidak ditemukan atau kombinasi courier/service tidak valid
     */
    private function resolveShippingCost(string $cacheKey, string $courier, string $service): int
    {
        $cachedCosts = cache()->get($cacheKey);

        if (!$cachedCosts) {
            throw new \RuntimeException(
                'Data ongkir tidak ditemukan atau sudah kadaluarsa. Silakan hitung ulang ongkir sebelum checkout.'
            );
        }

        // Format cache: array of ['courier' => 'jne', 'service' => 'REG', 'cost' => 12000, ...]
        $courierLower  = strtolower(trim($courier));
        $serviceUpper  = strtoupper(trim($service));

        foreach ($cachedCosts as $option) {
            if (
                strtolower($option['courier'] ?? '') === $courierLower &&
                strtoupper($option['service'] ?? '') === $serviceUpper
            ) {
                return (int) $option['cost'];
            }
        }

        throw new \RuntimeException(
            "Pilihan pengiriman '{$courier} {$service}' tidak valid. Silakan pilih kurir dari opsi yang tersedia."
        );
    }

    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::where('order_number', 'like', "ORD-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Tambah random suffix 3 karakter untuk cegah race condition saat concurrent orders
        $suffix = strtoupper(\Illuminate\Support\Str::random(3));

        return sprintf('ORD-%s-%04d-%s', $date, $newNumber, $suffix);
    }
}
