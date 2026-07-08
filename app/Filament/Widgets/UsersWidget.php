<?php
namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 2;

    protected function getStats(): array
    {
        $totalCustomers = User::where('role', UserRole::Customer)->count();
        $newThisMonth = User::where('role', UserRole::Customer)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalAdmins = User::where('role', UserRole::Admin)->count();

        return [
            Stat::make('Total Customer', $totalCustomers)
                ->description($newThisMonth . ' baru bulan ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes(['class' => 'stat-widget-users']),
            Stat::make('Total Admin', $totalAdmins)
                ->description('Akun dengan akses admin')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info')
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes(['class' => 'stat-widget-admins']),
        ];
    }
}
