<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageVideoResource\Pages;
use App\Models\HomepageVideo;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageVideoResource extends Resource
{
    protected static ?string $model = HomepageVideo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Video Homepage';
    }

    public static function getModelLabel(): string
    {
        return 'Video';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Video Homepage';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Video')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Video')
                            ->maxLength(200)
                            ->placeholder('Contoh: Koleksi Terbaru Aliesmo'),

                        TextInput::make('youtube_url')
                            ->label('URL YouTube')
                            ->required()
                            ->maxLength(300)
                            ->placeholder('https://www.youtube.com/watch?v=xxxxx atau https://youtu.be/xxxxx')
                            ->helperText('Tempel link YouTube biasa — sistem otomatis konversi ke embed.'),

                        Textarea::make('description')
                            ->label('Deskripsi (opsional)')
                            ->maxLength(500)
                            ->rows(2),

                        TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu'),

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
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('title')
                    ->label('Judul')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('youtube_url')
                    ->label('URL YouTube')
                    ->limit(50)
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
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
            'index'  => Pages\ListHomepageVideos::route('/'),
            'create' => Pages\CreateHomepageVideo::route('/create'),
            'edit'   => Pages\EditHomepageVideo::route('/{record}/edit'),
        ];
    }
}
