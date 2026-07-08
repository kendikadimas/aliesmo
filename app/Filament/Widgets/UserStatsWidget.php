<?php
namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalCustomers = User::where('role', UserRole::Customer)->count();
        $totalAdmins = User::where('role', UserRole::Admin)->count();
        $newThisMonth = User::where('role', UserRole::Customer)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $newThisWeek = User::where('role', UserRole::Customer)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            Stat::make('Total Customer', $totalCustomers)
                ->description($newThisMonth . ' baru bulan ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes(['class' => 'stat-widget-users']),
            Stat::make('Baru Minggu Ini', $newThisWeek)
                ->description('Customer baru 7 hari terakhir')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success')
                ->extraAttributes(['class' => 'stat-widget-revenue']),
            Stat::make('Total Admin', $totalAdmins)
                ->description('Akun dengan akses admin')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-admins']),
        ];
    }
}
