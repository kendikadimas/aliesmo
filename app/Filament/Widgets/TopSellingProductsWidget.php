<?php
namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::select(
                        DB::raw('MIN(id) as id'),
                        'product_name',
                        DB::raw('SUM(quantity) as total_qty'),
                        DB::raw('SUM(subtotal) as total_revenue')
                    )
                    ->groupBy('product_name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('total_qty')
                    ->label('Sold'),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR'),
            ]);
    }
}
