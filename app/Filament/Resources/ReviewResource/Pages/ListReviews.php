<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Widgets\ReviewStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            ReviewStatsWidget::class,
        ];
    }
}
