<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Transaksi';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('note')->limit(50),
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(\App\Enums\StockMovementType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name])),
                SelectFilter::make('product')->relationship('product', 'name'),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
