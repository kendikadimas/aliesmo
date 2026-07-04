<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
        // Error key pakai index array (0), bukan product_id
        $response->assertJsonValidationErrors(['items.0.quantity']);
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
        // WhatsApp flow: response berisi order + whatsapp_number + whatsapp_message
        $response->assertJsonStructure([
            'order' => [
                'order_number', 'customer_name', 'total', 'status',
            ],
            'whatsapp_number',
            'whatsapp_message',
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

        // Stok tidak berkurang saat order dibuat — hanya berkurang setelah paid
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

    public function test_payment_callback_endpoint_is_disabled(): void
    {
        // Endpoint di-abort 410 Gone — payment dihandle via WhatsApp, callback Midtrans dinonaktifkan
        $response = $this->postJson('/api/v1/payments/callback/midtrans', [
            'order_id' => 'ORD-20260627-0001',
            'status_code' => '200',
            'gross_amount' => '100000',
            'signature_key' => 'invalid_signature',
            'transaction_status' => 'settlement',
            'transaction_id' => 'TRX-123',
        ]);

        $response->assertStatus(410);
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
