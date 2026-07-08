<?php
namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalActive = Product::where('is_active', true)->count();
        $totalInactive = Product::where('is_active', false)->count();
        $lowStock = Product::where('is_active', true)->where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStock = Product::where('is_active', true)->where('stock', 0)->count();
        $totalCategories = Category::count();
        $totalProducts = Product::count();

        return [
            Stat::make('Total Produk', $totalProducts)
                ->description($totalActive . ' aktif, ' . $totalInactive . ' nonaktif')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-products']),
            Stat::make('Kategori', $totalCategories)
                ->description('Kategori tersedia')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary')
                ->extraAttributes(['class' => 'stat-widget-admins']),
            Stat::make('Stok Rendah', $lowStock)
                ->description('Stok ≤ 5, perlu restock')
                ->descriptionIcon($lowStock > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStock > 0 ? 'warning' : 'success')
                ->extraAttributes(['class' => 'stat-widget-stock']),
            Stat::make('Stok Habis', $outOfStock)
                ->description('Produk tidak tersedia')
                ->descriptionIcon($outOfStock > 0 ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success')
                ->extraAttributes(['class' => 'stat-widget-cancelled']),
        ];
    }
}
