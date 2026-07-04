<?php
namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Tidak pakai nested DB::transaction di sini karena adjustStock() sudah punya
        // transaction sendiri — nested transaction pada SQLite/MySQL bisa deadlock.
        // Caller (OrderService::createFromCart) sudah wrap dalam transaction.
        foreach ($order->items as $item) {
            $this->adjustStock(
                $item->product_id,
                -$item->quantity,
                StockMovementType::Sale,
                "Order #{$order->order_number}"
            );
        }
    }
}
