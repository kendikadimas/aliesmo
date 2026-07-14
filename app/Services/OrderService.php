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
        private StockService $stockService
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

            // Normalize nama kurir dari RajaOngkir ke format display
            // Normalize kurir — cover kode mentah (jne, jnt) dan nama display (J&T Express, AnterAja, dll)
            $courierTracking = [
                'JNE'           => 'https://jne.co.id/tracking-package',
                'JNT Express'   => 'https://jet.co.id/track',
                'SiCepat'       => 'https://www.sicepat.com/',
                'Anteraja'      => 'https://anteraja.id/id/tracking',
                'Ninja'         => 'https://www.ninjaxpress.co/en-id/tracking',
                'Pos Indonesia' => 'https://www.posindonesia.co.id/id/tracking',
                'Lion Parcel'   => 'https://lionparcel.com/track',
            ];
            // Map dari lowercase (kode atau nama display) ke canonical name
            $courierMap = [
                'jne'           => 'JNE',
                'jnt'           => 'JNT Express',
                'j&t'           => 'JNT Express',
                'j&t express'   => 'JNT Express',
                'jnt express'   => 'JNT Express',
                'sicepat'       => 'SiCepat',
                'anteraja'      => 'Anteraja',
                'anterAja'      => 'Anteraja',
                'ninja'         => 'Ninja',
                'ninja express' => 'Ninja',
                'ninja xpress'  => 'Ninja',
                'pos'           => 'Pos Indonesia',
                'pos indonesia' => 'Pos Indonesia',
                'lion'          => 'Lion Parcel',
                'lion parcel'   => 'Lion Parcel',
                'lionparcel'    => 'Lion Parcel',
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
        });
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
