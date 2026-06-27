<?php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private StockService $stockService,
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function createFromCart(array $items, array $customerData): Order
    {
        return DB::transaction(function () use ($items, $customerData) {
            $subtotal = 0;
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
                $subtotal += $lineSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineSubtotal,
                ];
            }

            $shippingCost = $customerData['shipping_cost'] ?? 0;
            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => auth()->id(),
                'customer_name' => $customerData['customer_name'],
                'customer_email' => $customerData['customer_email'],
                'customer_phone' => $customerData['customer_phone'] ?? null,
                'shipping_address' => $customerData['shipping_address'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => OrderStatus::Pending,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });
    }

    public function processPayment(Order $order): array
    {
        return $this->paymentGateway->createTransaction($order);
    }

    public function markAsPaid(Order $order, Payment $payment): void
    {
        DB::transaction(function () use ($order, $payment) {
            $order->update([
                'status' => OrderStatus::Paid,
                'payment_method' => $payment->gateway,
                'paid_at' => now(),
            ]);

            $this->stockService->decrementForOrder($order);

            Log::channel('payment')->info('Order marked as paid', [
                'order' => $order->order_number,
            ]);
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

    public function handlePaymentCallback(array $payload): Payment
    {
        if (!$this->paymentGateway->verifySignature($payload)) {
            Log::channel('payment')->warning('Invalid payment signature', $payload);
            throw new \RuntimeException('Invalid payment signature');
        }

        $payment = $this->paymentGateway->handleCallback($payload);

        $order = $payment->order;

        if ($payment->status === PaymentStatus::Success && $order->status !== OrderStatus::Paid) {
            $this->markAsPaid($order, $payment);
        }

        if ($payment->status === PaymentStatus::Failed) {
            $order->update(['status' => OrderStatus::Cancelled]);
        } elseif ($payment->status === PaymentStatus::Expired) {
            $order->update(['status' => OrderStatus::Expired]);
        }

        return $payment;
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

        return sprintf('ORD-%s-%04d', $date, $newNumber);
    }
}
