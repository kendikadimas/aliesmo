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
        // Transaction wajib agar lockForUpdate efektif (juga saat dipanggil di luar TX)
        DB::transaction(function () use ($order) {
            $locked = Order::lockForUpdate()->find($order->id);
            if (!$locked || $locked->stock_decremented_at) {
                Log::info('decrementForOrder: SKIP already decremented', [
                    'order' => $order->order_number,
                    'at'    => $locked?->stock_decremented_at,
                ]);
                return;
            }

            $locked->loadMissing('items');

            foreach ($locked->items as $item) {
                if ($item->size_id) {
                    $this->adjustSizeStock(
                        $item->size_id,
                        -$item->quantity,
                        StockMovementType::Sale,
                        "Order #{$locked->order_number}"
                    );
                } elseif ($item->variant_id) {
                    $this->adjustVariantStock(
                        $item->variant_id,
                        -$item->quantity,
                        StockMovementType::Sale,
                        "Order #{$locked->order_number}"
                    );
                } else {
                    $this->adjustStock(
                        $item->product_id,
                        -$item->quantity,
                        StockMovementType::Sale,
                        "Order #{$locked->order_number}"
                    );
                }
            }

            $locked->update(['stock_decremented_at' => now()]);
            $order->setAttribute('stock_decremented_at', $locked->stock_decremented_at);
        });
    }

    public function restockForOrder(Order $order, string $reason = ''): void
    {
        DB::transaction(function () use ($order, $reason) {
            $locked = Order::lockForUpdate()->find($order->id);
            if (!$locked || !$locked->stock_decremented_at) {
                return;
            }

            $locked->loadMissing(['items.variant', 'items.size']);
            $note = $reason !== '' ? $reason : "Restock order #{$locked->order_number}";

            foreach ($locked->items as $item) {
                try {
                    if ($item->size_id) {
                        $this->adjustSizeStock($item->size_id, $item->quantity, StockMovementType::Return, $note);
                    } elseif ($item->variant_id) {
                        $this->adjustVariantStock($item->variant_id, $item->quantity, StockMovementType::Return, $note);
                    } else {
                        $this->adjustStock($item->product_id, $item->quantity, StockMovementType::Return, $note);
                    }
                } catch (\Throwable $e) {
                    Log::warning("restockForOrder skip item #{$item->id}: " . $e->getMessage());
                }
            }

            $locked->update(['stock_decremented_at' => null]);
            $order->setAttribute('stock_decremented_at', null);
        });
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
