<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 2;

    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $pendingCount = Order::where('status', OrderStatus::Pending)->count();
        $totalRevenue = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])->sum('total');

        return [
            Stat::make('Total Pesanan', $totalOrders)
                ->description($pendingCount . ' pesanan pending')
                ->descriptionIcon($pendingCount > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.orders.index'))
                ->extraAttributes(['class' => 'stat-widget-orders']),
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Dari order paid & completed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(route('filament.admin.resources.orders.index'))
                ->extraAttributes(['class' => 'stat-widget-revenue']),
        ];
    }
}
