<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Kupon')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Kupon')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->alphaNum()
                            ->uppercase()
                            ->placeholder('DISKON50'),

                        Select::make('type')
                            ->label('Tipe Diskon')
                            ->required()
                            ->options([
                                'percent' => 'Persentase (%)',
                                'fixed'   => 'Fixed Amount (Rp)',
                            ])
                            ->default('percent')
                            ->live(),

                        TextInput::make('value')
                            ->label('Nilai Diskon')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Untuk persen: masukkan angka 1–100. Untuk fixed: masukkan nominal Rp.'),

                        TextInput::make('min_order')
                            ->label('Minimum Order (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('max_discount')
                            ->label('Maksimal Diskon (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Khusus kupon persen. Kosongkan jika tidak ada batas.'),
                    ]),

                Section::make('Batasan & Status')
                    ->schema([
                        TextInput::make('usage_limit')
                            ->label('Limit Penggunaan')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Kosongkan untuk unlimited.'),

                        TextInput::make('used_count')
                            ->label('Sudah Dipakai')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->helperText('Kosongkan jika tidak pernah expired.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed'   => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'percent' => 'Persentase',
                        'fixed'   => 'Fixed',
                        default   => $state,
                    }),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(fn($state, $record) => $record->type === 'percent'
                        ? $record->value . '%'
                        : 'Rp' . number_format($record->value, 0, ',', '.')),

                TextColumn::make('min_order')
                    ->label('Min Order')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('used_count')
                    ->label('Penggunaan')
                    ->formatStateUsing(fn($state, $record) => $record->usage_limit
                        ? "{$state} / {$record->usage_limit}"
                        : "{$state} / ∞")
                    ->badge()
                    ->color(fn($state, $record) => $record->usage_limit && $state >= $record->usage_limit ? 'danger' : 'gray'),

                TextColumn::make('expires_at')
                    ->label('Expired')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
