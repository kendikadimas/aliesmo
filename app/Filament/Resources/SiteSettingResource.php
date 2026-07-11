<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Situs';
    }

    public static function getModelLabel(): string
    {
        return 'Pengaturan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengaturan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->helperText('Contoh: announcement_1, stat_kemeja_terjual'),

                        TextInput::make('value')
                            ->label('Nilai')
                            ->maxLength(500),

                        TextInput::make('group')
                            ->label('Grup')
                            ->default('general')
                            ->helperText('Contoh: announcement, payment, general'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'announcement' => 'warning',
                        'stats'        => 'success',
                        default        => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('group')
            ->modifyQueryUsing(fn ($query) => $query->where('group', '!=', 'stats'))
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'announcement' => 'Announcement',
                        'payment'      => 'Pembayaran',
                        'video'        => 'Video',
                        'general'      => 'General',
                    ]),
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
            'index'  => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit'   => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
