<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])
            ->sum('total');

        $totalOrders = Order::count();

        $avgOrderValue = $totalOrders > 0
            ? $totalRevenue / $totalOrders
            : 0;

        $lowStockCount = Product::where('stock', '<=', 5)->where('is_active', true)->count();

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('All time')
                ->descriptionIcon('heroicon-m-banknotes'),
            Stat::make('Total Orders', $totalOrders)
                ->descriptionIcon('heroicon-m-shopping-cart'),
            Stat::make('Avg Order Value', 'Rp ' . number_format($avgOrderValue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Low Stock Products', $lowStockCount)
                ->description('Stock ≤ 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle'),
        ];
    }
}
