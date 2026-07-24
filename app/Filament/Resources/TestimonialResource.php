<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Testimoni';
    }

    public static function getModelLabel(): string
    {
        return 'Testimoni';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Testimoni';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Testimoni')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('role')
                            ->label('Jabatan / Kota (opsional)')
                            ->maxLength(100)
                            ->placeholder('Contoh: Pelanggan, Jakarta'),

                        Textarea::make('content')
                            ->label('Isi Testimoni')
                            ->required()
                            ->rows(4)
                            ->maxLength(1000),

                        FileUpload::make('avatar_url')
                            ->label('Foto (opsional)')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('testimonials')
                            ->visibility('public')
                            ->helperText('JPG/PNG/WebP, maks 2MB.'),

                        TextInput::make('rating')
                            ->label('Rating (1–5)')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),

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

                ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->height(40),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Jabatan')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('content')
                    ->label('Testimoni')
                    ->limit(50),

                TextColumn::make('rating')
                    ->label('★')
                    ->sortable()
                    ->width(50),

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
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
