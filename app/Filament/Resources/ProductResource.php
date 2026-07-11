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

    public static function getNavigationLabel(): string
    {
        return 'Produk';
    }

    public static function getModelLabel(): string
    {
        return 'Produk';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Produk';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Umum')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug())),
                        TextInput::make('slug')->required()->unique(Product::class, ignoreRecord: true),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->default([])
                            ->label('Kategori'),
                        Toggle::make('is_active'),
                         FileUpload::make('thumbnail')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->label('Thumbnail'),
                    ]),
                Section::make('Harga & Stok')
                    ->schema([
                        TextInput::make('sku')
                            ->required()
                            ->unique(Product::class, ignoreRecord: true)
                            ->label('Kode Produk')
                            ->helperText('Kode unik untuk identifikasi produk ini.'),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('Rp ')
                            ->label('Harga Jual')
                            ->helperText('Harga yang dibayar pelanggan.'),
                        TextInput::make('original_price')
                            ->numeric()
                            ->prefix('Rp ')
                            ->label('Harga Normal (Opsional)')
                            ->helperText('Isi jika produk sedang diskon. Harga ini akan dicoret di halaman produk.'),
                        TextInput::make('weight')
                            ->numeric()
                            ->default(300)
                            ->suffix('gram')
                            ->helperText('Berat produk dalam gram. Digunakan untuk menghitung ongkir.')
                            ->label('Berat'),
                        TextInput::make('stock')
                            ->numeric()
                            ->readOnly(fn ($livewire) => $livewire instanceof Pages\EditProduct),
                    ]),
                Section::make('Deskripsi')
                    ->schema([
                        RichEditor::make('description'),
                    ]),
                Section::make('Video Produk')
                    ->description('Tambahkan video YouTube untuk produk ini. Video akan ditampilkan di halaman detail produk.')
                    ->schema([
                        Repeater::make('videos')
                            ->relationship('videos')
                            ->schema([
                                TextInput::make('youtube_url')
                                    ->required()
                                    ->url()
                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                    ->label('URL YouTube'),
                                TextInput::make('title')
                                    ->placeholder('Judul video (opsional)')
                                    ->label('Judul'),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Urutan'),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Tambah Video')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string => $state['title'] ?: ($state['youtube_url'] ?: 'Video baru')),
                    ])
                    ->collapsible(),
                Section::make('Foto Produk')
                    ->description('Upload beberapa foto produk (sudut berbeda, varian). Foto pertama akan digunakan sebagai gambar utama.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('path')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048) // 2MB
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required()
                                    ->label('Foto'),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Urutan'),
                            ])
                            ->orderColumn('sort_order')
                            ->addActionLabel('+ Add Image')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string => 'Image #' . (($state['sort_order'] ?? 0) + 1)),
                    ]),
                Section::make('Varian')
                    ->description('Tambahkan varian produk seperti ukuran (S, M, L, XL) atau warna. Setiap varian bisa punya harga dan stok berbeda. Jika produk tidak punya varian, kosongkan bagian ini.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->placeholder('Contoh: S, M, L, XL, Merah, Biru...')
                                    ->label('Nama Varian'),
                                TextInput::make('sku')
                                    ->nullable()
                                    ->unique('product_variants', 'sku', ignoreRecord: true)
                                    ->placeholder('Kosongkan untuk auto-generate')
                                    ->label('SKU Varian'),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->label('Harga'),
                                TextInput::make('stock')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->label('Stok'),
                                TextInput::make('weight')
                                    ->numeric()
                                    ->suffix('gram')
                                    ->placeholder('Kosongkan untuk pakai berat produk')
                                    ->label('Berat (opsional)'),
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->label('Aktif'),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Urutan'),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Varian')
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string => $state['name'] ?? 'Varian Baru'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')->circular()->disk('public'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('categories.name')->badge()->separator(', '),
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
                    ->label('Sesuaikan Stok')
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
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Kategori')
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['values'] ?? null, fn ($q, $ids) =>
                            $q->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids))
                        )
                    ),
                SelectFilter::make('is_active')
                    ->label('Status Aktif')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
                Filter::make('low_stock')
                    ->label('Stok Menipis (≤ 5)')
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
