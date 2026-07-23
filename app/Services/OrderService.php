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
            $shippingResult  = $this->resolveShippingCost(
                $customerData['shipping_cache_key'],
                $customerData['shipping_courier'],
                $customerData['shipping_service']
            );
            $shippingCost    = $shippingResult['cost'];
            $courierService  = $shippingResult['courier_service'];
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
                'destination_lat' => $customerData['destination_lat'] ?? null,
                'destination_lng' => $customerData['destination_lng'] ?? null,
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'coupon_discount' => $couponDiscount,
                'coupon_code'     => $couponCode,
                'total'           => $total,
                'status'          => OrderStatus::Pending,
                'payment_method'  => $customerData['payment_method'] ?? 'bank_transfer',
                'selected_bank'   => $customerData['selected_bank'] ?? null,
                'courier'         => $courierName ?: null,
                'courier_service' => $courierService ?? null,
                'tracking_url'    => $courierUrl,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });
    }

    public function markAsPaid(Order $order, string $paymentMethod = 'whatsapp'): void
    {
        Log::info('markAsPaid: START', [
            'order' => $order->order_number,
            'current_status' => $order->status->value,
            'payment_method' => $paymentMethod,
            'total' => $order->total,
        ]);

        DB::transaction(function () use ($order, $paymentMethod) {
            $order->update([
                'status'         => OrderStatus::Paid,
                'payment_method' => $paymentMethod,
                'paid_at'        => now(),
            ]);

            Log::debug('markAsPaid: status updated to Paid', ['order' => $order->order_number]);

            try {
                $this->stockService->decrementForOrder($order);
                Log::debug('markAsPaid: stock decremented', ['order' => $order->order_number]);
            } catch (\Throwable $e) {
                Log::error('markAsPaid: stock decrement failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            Log::info('markAsPaid: order confirmed', ['order' => $order->order_number]);

        });

        // Panggil Biteship DI LUAR transaction — HTTP timeout tidak boleh rollback DB order
        $this->createBiteshipShipment($order->fresh());

        Log::info('markAsPaid: COMPLETED', ['order' => $order->order_number]);
    }

    /**
     * Buat order pengiriman di Biteship.
     * Dipanggil setelah order dibayar agar saldo Biteship tidak terpotong jika order dibatalkan.
     */
    public function createBiteshipShipment(Order $order): void
    {
        Log::info('createBiteshipShipment: START', [
            'order' => $order->order_number,
            'shipping_area_id' => $order->shipping_area_id,
            'courier' => $order->courier,
            'biteship_order_id' => $order->biteship_order_id,
        ]);

        try {
            // Skip jika tidak ada area_id atau courier
            if (empty($order->shipping_area_id) || empty($order->courier)) {
                Log::warning('createBiteshipShipment: SKIP - missing area_id or courier', [
                    'order' => $order->order_number,
                    'area_id' => $order->shipping_area_id,
                    'courier' => $order->courier,
                ]);
                return;
            }

            // Skip jika sudah ada biteship_order_id (idempotent)
            if (!empty($order->biteship_order_id)) {
                Log::info('createBiteshipShipment: SKIP - already exists', [
                    'order' => $order->order_number,
                    'biteship_id' => $order->biteship_order_id,
                ]);
                return;
            }

            // Siapkan items dari order items — eager load relasi untuk ambil weight
            $items      = [];
            $itemValue  = 0; // total nilai barang untuk insurance
            $order->loadMissing(['items.product', 'items.variant', 'items.size']);

            foreach ($order->items as $item) {
                $itemName = $item->product_name;
                if ($item->variant_name) $itemName .= " ({$item->variant_name})";
                if ($item->size_name)    $itemName .= " - {$item->size_name}";

                // Prioritas weight: size → variant → product → default 300g
                $weight = $item->size?->weight
                    ?? $item->variant?->weight
                    ?? $item->product?->weight
                    ?? 300;

                $itemValue += (int) $item->price;

                $items[] = [
                    'name'     => $itemName,
                    'value'    => (int) $item->price,
                    'quantity' => $item->quantity,
                    'weight'   => max(1, (int) $weight),
                ];
            }

            // Map courier canonical name → Biteship code
            // Canonical name disimpan di $order->courier (contoh: 'JNE', 'J&T Express', 'POS Indonesia')
            $courierNameMap = [
                'JNE'           => 'jne',
                'JNT Express'   => 'jnt',
                'J&T Express'   => 'jnt',
                'POS Indonesia' => 'pos',
                'Pos Indonesia' => 'pos',
            ];
            $courierCompany = $courierNameMap[$order->courier]
                ?? strtolower(explode(' ', $order->courier)[0]);

            // Courier type: pakai courier_service yang tersimpan di order (dari cache ongkir saat checkout),
            // fallback ke COURIER_SERVICE_MAP agar benar per kurir (J&T = 'ez', bukan 'reg').
            $courierType = $order->courier_service
                ?? BiteshipService::COURIER_SERVICE_MAP[$courierCompany]
                ?? 'reg';

            Log::debug('createBiteshipShipment: calling API', [
                'order'           => $order->order_number,
                'courier_company' => $courierCompany,
                'courier_type'    => $courierType,
                'item_count'      => count($items),
                'item_value'      => $itemValue,
                'area_id'         => $order->shipping_area_id,
            ]);

            $isCod    = ($order->payment_method === 'cod');
            $codAmount = $isCod ? (int) $order->total : null;

            $result = $this->biteshipService->createOrder([
                'order_number'     => $order->order_number,
                'customer_name'    => $order->customer_name,
                'customer_phone'   => $order->customer_phone,
                'customer_email'   => $order->customer_email,
                'shipping_address' => $order->shipping_address,
                'shipping_area_id' => $order->shipping_area_id,
                'destination_lat'  => $order->destination_lat,
                'destination_lng'  => $order->destination_lng,
                'courier_company'  => $courierCompany,
                'courier_type'     => $courierType,
                'item_value'       => $itemValue,
                'items'            => $items,
                // COD: kirim is_cod + cod_amount agar BiteshipService pasang destination_cash_on_delivery
                'is_cod'           => $isCod,
                'cod_amount'       => $codAmount,
            ]);

            Log::debug('createBiteshipShipment: API response', [
                'order' => $order->order_number,
                'result' => $result,
            ]);

            // Simpan data Biteship ke order
            $order->update([
                'biteship_order_id'    => $result['biteship_order_id'],
                'biteship_tracking_id' => $result['biteship_tracking_id'],
                'biteship_waybill_id'  => $result['biteship_waybill_id'],
                'biteship_status'      => $result['biteship_status'],
                'tracking_number'      => $result['biteship_waybill_id'] ?? $order->tracking_number,
                'tracking_url'         => $result['tracking_url'] ?? $order->tracking_url,
            ]);

            Log::info('createBiteshipShipment: SUCCESS', [
                'order'        => $order->order_number,
                'biteship_id'  => $result['biteship_order_id'],
                'waybill_id'   => $result['biteship_waybill_id'],
                'status'       => $result['biteship_status'],
                'price'        => $result['price'],
            ]);

        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan order — admin bisa manual create shipment
            Log::error('createBiteshipShipment: FAILED', [
                'order'  => $order->order_number,
                'error'  => $e->getMessage(),
                'file'   => $e->getFile(),
                'line'   => $e->getLine(),
                'trace'  => $e->getTraceAsString(),
            ]);
        }
    }

    public function cancel(Order $order, string $reason = ''): void
    {
        DB::transaction(function () use ($order, $reason) {
            $restorableStatuses = [
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::Shipped,
                OrderStatus::Completed,
            ];

            if (in_array($order->status, $restorableStatuses)) {
                $order->loadMissing(['items.variant', 'items.size']);

                foreach ($order->items as $item) {
                    // Prioritas restock: size → variant → product
                    if ($item->size_id && $item->size) {
                        $this->stockService->adjustSizeStock(
                            $item->size_id,
                            $item->quantity,
                            StockMovementType::Return,
                            "Cancelled order #{$order->order_number}: {$reason}"
                        );
                    } elseif ($item->variant_id && $item->variant) {
                        $this->stockService->adjustVariantStock(
                            $item->variant_id,
                            $item->quantity,
                            StockMovementType::Return,
                            "Cancelled order #{$order->order_number}: {$reason}"
                        );
                    } else {
                        $this->stockService->adjustStock(
                            $item->product_id,
                            $item->quantity,
                            StockMovementType::Return,
                            "Cancelled order #{$order->order_number}: {$reason}"
                        );
                    }
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
        });
    }

    /**
     * Resolve ongkir dari cache server-side berdasarkan cache_key, courier, dan service.
     * Mencegah manipulasi ongkir oleh client (CRIT-2).
     *
     * Return array: ['cost' => int, 'courier_service' => string]
     *
     * @throws \RuntimeException jika cache tidak ditemukan atau kombinasi courier/service tidak valid
     */
    private function resolveShippingCost(string $cacheKey, string $courier, string $service): array
    {
        $cachedCosts = cache()->get($cacheKey);

        if (!$cachedCosts) {
            throw new \RuntimeException(
                'Data ongkir tidak ditemukan atau sudah kadaluarsa. Silakan hitung ulang ongkir sebelum checkout.'
            );
        }

        // Format cache: array of ['courier' => 'jne', 'service' => 'REG', 'cost' => 12000, ...]
        $courierLower = strtolower(trim($courier));
        $serviceUpper = strtoupper(trim($service));

        foreach ($cachedCosts as $option) {
            if (
                strtolower($option['courier'] ?? '') === $courierLower &&
                strtoupper($option['service'] ?? '') === $serviceUpper
            ) {
                return [
                    'cost'           => (int) $option['cost'],
                    // Kode service Biteship (reg, ez, sps) — disimpan di orders.courier_service
                    // agar createBiteshipShipment tahu courier_type yang benar tanpa hardcode
                    'courier_service' => strtolower($option['service'] ?? ''),
                ];
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
            // Format: ORD-YYYYMMDD-XXXX-SUFFIX — ambil segment ke-3 (XXXX)
            $parts = explode('-', $lastOrder->order_number);
            $lastNumber = isset($parts[2]) ? (int) $parts[2] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Tambah random suffix 3 karakter untuk cegah race condition saat concurrent orders
        $suffix = strtoupper(\Illuminate\Support\Str::random(3));

        return sprintf('ORD-%s-%04d-%s', $date, $newNumber, $suffix);
    }
}
