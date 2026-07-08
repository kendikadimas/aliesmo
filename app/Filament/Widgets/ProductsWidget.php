<?php
namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 2;

    protected function getStats(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)->where('stock', '<=', 5)->count();
        $totalCategories = Category::count();

        return [
            Stat::make('Produk Aktif', $totalProducts)
                ->description($totalCategories . ' kategori tersedia')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->url(route('filament.admin.resources.products.index'))
                ->extraAttributes(['class' => 'stat-widget-products']),
            Stat::make('Stok Rendah', $lowStockCount)
                ->description('Produk dengan stok ≤ 5')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.products.index'))
                ->extraAttributes(['class' => 'stat-widget-stock']),
        ];
    }
}
