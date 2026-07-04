<?php
namespace App\Filament\Resources;

use App\Enums\StockMovementType;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Katalog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug())),
                        TextInput::make('slug')->required()->unique(Product::class, ignoreRecord: true),
                        Select::make('category_id')->relationship('category', 'name'),
                        Toggle::make('is_active'),
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('products')
                            ->visibility('public')
                            ->label('Thumbnail'),
                    ]),
                Section::make('Pricing & Stock')
                    ->schema([
                        TextInput::make('sku')->required()->unique(Product::class),
                        TextInput::make('price')->numeric()->prefix('Rp '),
                        TextInput::make('stock')
                            ->numeric()
                            ->readOnly(fn ($livewire) => $livewire instanceof Pages\EditProduct),
                    ]),
                Section::make('Description')
                    ->schema([
                        RichEditor::make('description'),
                    ]),
                Section::make('Product Images')
                    ->description('Upload multiple product images (different angles, variants). The first image will be used as the main image.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('path')
                                    ->image()
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required()
                                    ->label('Image'),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Sort Order'),
                            ])
                            ->orderColumn('sort_order')
                            ->addActionLabel('+ Add Image')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string => 'Image #' . (($state['sort_order'] ?? 0) + 1)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')->circular()->disk('public'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->badge()->sortable(),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn ($state): string => $state <= 5 ? 'danger' : ($state <= 20 ? 'warning' : 'success'))
                    ->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->recordActions([
                Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-cube')
                    ->size('sm')
                    ->form([
                        \Filament\Forms\Components\Select::make('type')
                            ->options(collect(StockMovementType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name]))
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('note'),
                    ])
                    ->action(function (array $data, Product $record) {
                        app(\App\Services\StockService::class)->adjustStock(
                            $record->id,
                            $data['quantity'],
                            StockMovementType::from($data['type']),
                            $data['note'] ?? null,
                            auth()->user()
                        );
                    }),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                Filter::make('low_stock')
                    ->label('Low Stock (≤ 5)')
                    ->query(fn (Builder $query) => $query->where('stock', '<=', 5)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
