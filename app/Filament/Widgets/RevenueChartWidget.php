<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $revenue = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
                ],
            ],
            'labels' => $revenue->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
