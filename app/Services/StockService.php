<?php
namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantSize;
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
            if ($item->size_id) {
                // Deduct dari stok ukuran (variant + size)
                $this->adjustSizeStock(
                    $item->size_id,
                    -$item->quantity,
                    StockMovementType::Sale,
                    "Order #{$order->order_number}"
                );
            } elseif ($item->variant_id) {
                // Deduct dari stok varian (tanpa size)
                $this->adjustVariantStock(
                    $item->variant_id,
                    -$item->quantity,
                    StockMovementType::Sale,
                    "Order #{$order->order_number}"
                );
            } else {
                // Produk tanpa varian — deduct dari stok produk langsung
                $this->adjustStock(
                    $item->product_id,
                    -$item->quantity,
                    StockMovementType::Sale,
                    "Order #{$order->order_number}"
                );
            }
        }
    }

    public function adjustVariantStock(
        int $variantId,
        int $quantity,
        StockMovementType $type,
        ?string $note = null,
        ?User $user = null
    ): StockMovement {
        return DB::transaction(function () use ($variantId, $quantity, $type, $note, $user) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            if ($variant->stock + $quantity < 0) {
                throw new \RuntimeException(
                    "Insufficient stock for variant '{$variant->name}'. Available: {$variant->stock}, requested: " . abs($quantity) . "."
                );
            }

            $movement = StockMovement::create([
                'product_id' => $variant->product_id,
                'type'       => $type,
                'quantity'   => $quantity,
                'note'       => $note ? "{$note} [variant: {$variant->name}]" : "[variant: {$variant->name}]",
                'user_id'    => $user?->id,
            ]);

            $variant->increment('stock', $quantity);

            return $movement;
        });
    }

    public function adjustSizeStock(
        int $sizeId,
        int $quantity,
        StockMovementType $type,
        ?string $note = null,
        ?User $user = null
    ): StockMovement {
        return DB::transaction(function () use ($sizeId, $quantity, $type, $note, $user) {
            $size = ProductVariantSize::lockForUpdate()->findOrFail($sizeId);
            $variant = $size->variant;

            if ($size->stock + $quantity < 0) {
                throw new \RuntimeException(
                    "Insufficient stock for size '{$size->name}' of variant '{$variant->name}'. Available: {$size->stock}, requested: " . abs($quantity) . "."
                );
            }

            $movement = StockMovement::create([
                'product_id' => $variant->product_id,
                'type'       => $type,
                'quantity'   => $quantity,
                'note'       => $note ? "{$note} [variant: {$variant->name}, size: {$size->name}]" : "[variant: {$variant->name}, size: {$size->name}]",
                'user_id'    => $user?->id,
            ]);

            $size->increment('stock', $quantity);

            return $movement;
        });
    }
}
