<?php
namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
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

                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException(
                        "Insufficient stock for '{$product->name}'. Available: {$product->stock}, requested: {$item['quantity']}."
                    );
                }

                $lineSubtotal = $product->price * $item['quantity'];
                $subtotal    += $lineSubtotal;

                $orderItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'price'        => $product->price,
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $lineSubtotal,
                ];
            }

            $shippingCost    = $customerData['shipping_cost'] ?? 0;
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

            $order = Order::create([
                'order_number'           => $this->generateOrderNumber(),
                'lookup_token'           => \Illuminate\Support\Str::random(32),
                'lookup_token_expires_at' => now()->addDays(7),
                'user_id'                => auth()->id(),
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
