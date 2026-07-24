<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);
        $this->product = Product::factory()->create(['stock' => 0]);
    }

    public function test_stock_can_be_adjusted(): void
    {
        $admin = User::factory()->admin()->create();

        $movement = $this->stockService->adjustStock(
            $this->product->id,
            50,
            StockMovementType::Restock,
            'Initial restock',
            $admin
        );

        $this->assertEquals(50, $this->product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'quantity' => 50,
            'type' => StockMovementType::Restock->value,
        ]);
    }

    public function test_stock_movement_is_logged(): void
    {
        $this->stockService->adjustStock(
            $this->product->id,
            10,
            StockMovementType::Initial,
            'Initial stock'
        );

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'type' => StockMovementType::Initial->value,
        ]);
    }

    public function test_negative_stock_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->stockService->adjustStock(
            $this->product->id,
            -10,
            StockMovementType::Sale,
            'Test oversell'
        );
    }

    public function test_decrement_for_order_is_idempotent(): void
    {
        $this->product->update(['stock' => 10]);

        $order = \App\Models\Order::factory()->create([
            'status' => \App\Enums\OrderStatus::Pending,
            'stock_decremented_at' => null,
        ]);
        $order->items()->create([
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'price'        => 10000,
            'quantity'     => 2,
            'subtotal'     => 20000,
        ]);

        $this->stockService->decrementForOrder($order->fresh(['items']));
        $this->stockService->decrementForOrder($order->fresh(['items'])); // second call no-op

        $this->assertEquals(8, $this->product->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_decremented_at);

        $this->stockService->restockForOrder($order->fresh(['items']));
        $this->assertEquals(10, $this->product->fresh()->stock);
        $this->assertNull($order->fresh()->stock_decremented_at);
    }
}
