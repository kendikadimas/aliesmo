<?php
namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalReviews = Review::count();
        $pendingReviews = Review::where('is_approved', false)->count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating');

        return [
            Stat::make('Total Review', $totalReviews)
                ->description($approvedReviews . ' approved')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-admins']),
            Stat::make('Pending Approval', $pendingReviews)
                ->description('Perlu ditinjau')
                ->descriptionIcon($pendingReviews > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingReviews > 0 ? 'warning' : 'success')
                ->extraAttributes(['class' => 'stat-widget-stock']),
            Stat::make('Rating Rata-rata', $avgRating ? number_format($avgRating, 1) . ' / 5' : 'N/A')
                ->description('Dari review approved')
                ->descriptionIcon('heroicon-m-star')
                ->color('success')
                ->extraAttributes(['class' => 'stat-widget-revenue']),
        ];
    }
}
