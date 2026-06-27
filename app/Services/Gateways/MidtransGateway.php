<?php
namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Order $order): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round($order->total),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'item_details' => $order->items->map(fn($item) => [
                'id' => $item->product_id,
                'price' => (int) round($item->price),
                'quantity' => $item->quantity,
                'name' => $item->product_name,
            ])->toArray(),
        ];

        try {
            $snapResponse = Snap::createTransaction($params);

            $payment = $order->payment()->create([
                'gateway' => 'midtrans',
                'gateway_reference' => $snapResponse->token,
                'amount' => $order->total,
                'status' => PaymentStatus::Pending,
            ]);

            Log::channel('payment')->info('Midtrans transaction created', [
                'order' => $order->order_number,
                'token' => $snapResponse->token,
            ]);

            return [
                'token' => $snapResponse->token,
                'redirect_url' => $snapResponse->redirect_url,
            ];
        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans create transaction failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function handleCallback(array $payload): Payment
    {
        $orderNumber = $payload['order_id'];
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $payment = $order->payment;
        if (!$payment) {
            $payment = $order->payment()->create([
                'gateway' => 'midtrans',
                'amount' => $order->total,
                'status' => PaymentStatus::Pending,
            ]);
        }
        $payment->update([
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'raw_payload' => $payload,
        ]);

        $status = match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => PaymentStatus::Success,
            $transactionStatus === 'settlement' => PaymentStatus::Success,
            in_array($transactionStatus, ['deny', 'cancel']) => PaymentStatus::Failed,
            $transactionStatus === 'expire' => PaymentStatus::Expired,
            $transactionStatus === 'refund' || $transactionStatus === 'partial_refund' => PaymentStatus::Refunded,
            default => PaymentStatus::Pending,
        };

        $payment->update(['status' => $status]);

        Log::channel('payment')->info('Midtrans callback processed', [
            'order' => $orderNumber,
            'status' => $status->value,
            'transaction_id' => $payload['transaction_id'] ?? null,
        ]);

        return $payment->fresh();
    }

    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $serverKey = config('services.midtrans.server_key');
        $signature = $payload['signature_key'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return $expected === $signature;
    }
}
