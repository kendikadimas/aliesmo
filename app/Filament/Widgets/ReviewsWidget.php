<?php
namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 2;

    protected function getStats(): array
    {
        $pendingReviews = Review::where('is_approved', false)->count();
        $totalReviews = Review::count();
        $avgRating = Review::where('is_approved', true)->avg('rating');

        return [
            Stat::make('Review Pending', $pendingReviews)
                ->description($totalReviews . ' total review')
                ->descriptionIcon($pendingReviews > 0 ? 'heroicon-m-star' : 'heroicon-m-check-circle')
                ->color($pendingReviews > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.reviews.index'))
                ->extraAttributes(['class' => 'stat-widget-reviews']),
            Stat::make('Rating Rata-rata', $avgRating ? number_format($avgRating, 1) . ' / 5' : 'N/A')
                ->description('Dari review approved')
                ->descriptionIcon('heroicon-m-star')
                ->color('info')
                ->extraAttributes(['class' => 'stat-widget-rating']),
        ];
    }
}
