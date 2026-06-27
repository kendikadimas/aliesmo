<?php
namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjustStock(
        int $productId,
        int $quantity,
        StockMovementType $type,
        ?string $note = null,
        ?User $user = null
    ): StockMovement {
        return DB::transaction(function () use ($productId, $quantity, $type, $note, $user) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($product->stock + $quantity < 0) {
                throw new \RuntimeException(
                    "Insufficient stock for product '{$product->name}'. Available: {$product->stock}, requested: " . abs($quantity) . "."
                );
            }

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'note' => $note,
                'user_id' => $user?->id,
            ]);

            $product->increment('stock', $quantity);

            return $movement;
        });
    }

    public function decrementForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for product '{$product->name}'. Available: {$product->stock}, requested: {$item->quantity}."
                    );
                }

                $this->adjustStock(
                    $product->id,
                    -$item->quantity,
                    StockMovementType::Sale,
                    "Order #{$order->order_number}"
                );
            }
        });
    }
}
