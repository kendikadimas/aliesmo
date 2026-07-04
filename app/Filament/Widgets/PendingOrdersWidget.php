<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $pendingCount = Order::where('status', OrderStatus::Pending)->count();
        $processingCount = Order::where('status', OrderStatus::Processing)->count();

        $oldestPending = Order::where('status', OrderStatus::Pending)
            ->orderBy('created_at')
            ->first();

        $oldestDesc = $oldestPending
            ? 'Terlama: ' . $oldestPending->created_at->diffForHumans()
            : 'Tidak ada pesanan pending';

        return [
            Stat::make('Pesanan Pending', $pendingCount)
                ->description($oldestDesc)
                ->descriptionIcon($pendingCount > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingCount > 5 ? 'danger' : ($pendingCount > 0 ? 'warning' : 'success')),

            Stat::make('Sedang Diproses', $processingCount)
                ->description('Pesanan dalam proses pengiriman')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}
