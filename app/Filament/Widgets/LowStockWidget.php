<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $lowStockThreshold = 5;

        $lowStockCount = Product::where('is_active', true)
            ->where('stock', '<=', $lowStockThreshold)
            ->where('stock', '>', 0)
            ->count();

        $outOfStockCount = Product::where('is_active', true)
            ->where('stock', 0)
            ->count();

        $lowStockProducts = Product::where('is_active', true)
            ->where('stock', '<=', $lowStockThreshold)
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(3)
            ->pluck('name')
            ->join(', ');

        return [
            Stat::make('Stok Hampir Habis', $lowStockCount)
                ->description($lowStockCount > 0 ? "Produk: {$lowStockProducts}" : 'Semua stok aman')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),

            Stat::make('Stok Habis', $outOfStockCount)
                ->description($outOfStockCount > 0 ? 'Perlu restock segera' : 'Tidak ada produk habis')
                ->descriptionIcon($outOfStockCount > 0 ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                ->color($outOfStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
