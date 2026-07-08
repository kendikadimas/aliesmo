<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrdersWidget;
use App\Filament\Widgets\ProductsWidget;
use App\Filament\Widgets\UsersWidget;
use App\Filament\Widgets\ReviewsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            OrdersWidget::class,
            ProductsWidget::class,
            UsersWidget::class,
            ReviewsWidget::class,
        ];
    }
}
