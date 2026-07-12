<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'Toko';
    }

    public static function getNavigationLabel(): string
    {
        return 'Ulasan';
    }

    public static function getModelLabel(): string
    {
        return 'Ulasan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ulasan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Ulasan')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->disabled(fn($record) => $record !== null),

                        Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->disabled(fn($record) => $record !== null),

                        Select::make('order_id')
                            ->label('Order (opsional)')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->disabled(fn($record) => $record !== null),

                        Select::make('rating')
                            ->label('Rating')
                            ->required()
                            ->options([
                                1 => '1 - Sangat Buruk',
                                2 => '2 - Buruk',
                                3 => '3 - Cukup',
                                4 => '4 - Bagus',
                                5 => '5 - Sangat Bagus',
                            ]),

                        Textarea::make('comment')
                            ->label('Komentar')
                            ->rows(4)
                            ->maxLength(1000),

                        Toggle::make('is_approved')
                            ->label('Disetujui')
                            ->default(false)
                            ->helperText('Review tampil di halaman produk setelah disetujui.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(50)
                    ->wrap(),

                IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Status Approval')
                    ->trueLabel('Approved')
                    ->falseLabel('Pending'),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 Bintang',
                        2 => '2 Bintang',
                        3 => '3 Bintang',
                        4 => '4 Bintang',
                        5 => '5 Bintang',
                    ]),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_approved)
                    ->action(function ($record) {
                        $record->is_approved = true;
                        $record->save();
                    }),

                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn($record) => $record->is_approved)
                    ->action(function ($record) {
                        $record->is_approved = false;
                        $record->save();
                    }),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('approve_all')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn($records) => $records->each(function ($record) {
                            $record->is_approved = true;
                            $record->save();
                        })),

                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit'  => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
