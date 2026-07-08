<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\ProductStatsWidget;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        $maxProducts = 30;
        $count = Product::count();

        return [
            CreateAction::make()
                ->visible(fn () => $count < $maxProducts)
                ->label("Tambah Produk ({$count}/{$maxProducts})"),
        ];
    }

    public function getTitle(): string
    {
        $maxProducts = 30;
        $count = Product::count();
        return "Produk ({$count}/{$maxProducts})";
    }
}
