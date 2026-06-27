<?php
namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createTransaction(Order $order): array;
    public function handleCallback(array $payload): Payment;
    public function verifySignature(array $payload): bool;
}
