<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalRevenue = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])->sum('total');
        $totalOrders = Order::count();
        $pendingCount = Order::where('status', OrderStatus::Pending)->count();
        $processingCount = Order::where('status', OrderStatus::Processing)->count();
        $completedCount = Order::where('status', OrderStatus::Completed)->count();
        $cancelledCount = Order::where('status', OrderStatus::Cancelled)->count();

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Dari order paid & completed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->extraAttributes(['class' => 'stat-widget-revenue']),
            Stat::make('Total Pesanan', $totalOrders)
                ->description('Semua status')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-orders']),
            Stat::make('Pending', $pendingCount)
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->extraAttributes(['class' => 'stat-widget-orders']),
            Stat::make('Diproses', $processingCount)
                ->description('Sedang dikirim')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-processing']),
            Stat::make('Selesai', $completedCount)
                ->description('Order completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->extraAttributes(['class' => 'stat-widget-revenue']),
            Stat::make('Dibatalkan', $cancelledCount)
                ->description('Order cancelled')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($cancelledCount > 0 ? 'danger' : 'gray')
                ->extraAttributes(['class' => 'stat-widget-cancelled']),
        ];
    }
}
