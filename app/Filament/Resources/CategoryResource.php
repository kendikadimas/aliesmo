<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Katalog';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kategori';
    }

    public static function getModelLabel(): string
    {
        return 'Kategori';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kategori';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('slug')->required()->unique(Category::class, ignorable: fn ($record) => $record),
                FileUpload::make('image_url')
                    ->label('Foto Kategori')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('21:9')
                    ->directory('categories')
                    ->disk('public')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Foto')->disk('public')->square(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('products_count')->label('Produk'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['products as products_count' => function ($query) {
            $query->where('is_active', true);
        }]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
