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
}
