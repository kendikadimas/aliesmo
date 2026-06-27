<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private array $customerData;

    protected function setUp(): void
    {
        parent::setUp();

        $gatewayMock = $this->createMock(PaymentGatewayInterface::class);
        $gatewayMock->method('createTransaction')
            ->willReturn(['token' => 'test-token', 'redirect_url' => 'https://test.midtrans.com']);
        $this->instance(PaymentGatewayInterface::class, $gatewayMock);

        $this->product = Product::factory()->create([
            'stock' => 10,
            'price' => 100000,
            'is_active' => true,
        ]);

        $this->customerData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 123',
        ];
    }

    public function test_order_creation_fails_if_quantity_exceeds_stock(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 20],
            ],
            ...$this->customerData,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.' . $this->product->id . '.quantity']);
    }

    public function test_order_creation_succeeds_with_valid_stock(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
            ...$this->customerData,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'order' => [
                'order_number', 'customer_name', 'total', 'status',
            ],
            'payment' => ['token', 'redirect_url'],
        ]);

        $this->assertEquals(OrderStatus::Pending->value, $response->json('order.status'));
    }

    public function test_stock_does_not_decrement_on_order_creation(): void
    {
        $this->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
            ...$this->customerData,
        ]);

        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    public function test_order_status_can_be_queried(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->order_number}/status");

        $response->assertStatus(200);
        $response->assertJsonPath('data.order_number', $order->order_number);
        $response->assertJsonPath('data.status', OrderStatus::Pending->value);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/payments/callback/midtrans', [
            'order_id' => 'ORD-20260627-0001',
            'status_code' => '200',
            'gross_amount' => '100000',
            'signature_key' => 'invalid_signature',
            'transaction_status' => 'settlement',
            'transaction_id' => 'TRX-123',
        ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_view_their_orders(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }
}
