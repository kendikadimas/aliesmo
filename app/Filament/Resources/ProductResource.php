<?php
namespace App\Filament\Resources;

use App\Enums\StockMovementType;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
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
                         Select::make('thumbnail_ratio')
                            ->options([
                                '1:1' => '1:1 (Persegi)',
                                '3:4' => '3:4 (Portrait)',
                            ])
                            ->default('1:1')
                            ->live()
                            ->dehydrated(false)
                            ->label('Rasio Thumbnail'),
                        FileUpload::make('thumbnail')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->imageCropAspectRatio(fn ($state, $get) => $get('thumbnail_ratio') ?? '1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
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
                        Textarea::make('description')
                            ->rows(6)
                            ->autosize()
                            ->columnSpanFull(),
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
                                Select::make('image_ratio')
                                    ->options([
                                        '1:1' => '1:1 (Persegi)',
                                        '3:4' => '3:4 (Portrait)',
                                    ])
                                    ->default('1:1')
                                    ->live()
                                    ->dehydrated(false)
                                    ->label('Rasio'),
                                FileUpload::make('path')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(8192) // 8MB
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->imageCropAspectRatio(fn ($state, $get) => $get('image_ratio') ?? '1:1')
                                    ->imageResizeTargetWidth('800')
                                    ->imageResizeTargetHeight('800')
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
                Section::make('Varian (Warna) & Ukuran')
                    ->description('Tambahkan varian warna produk. Setiap warna bisa punya ukuran (S, M, L, XL) dengan stok masing-masing. Jika produk tidak punya varian, kosongkan bagian ini.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->placeholder('Contoh: Merah, Biru, Navy...')
                                    ->label('Nama Warna'),
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
                                Select::make('variant_image_ratio')
                                    ->options([
                                        '1:1' => '1:1 (Persegi)',
                                        '3:4' => '3:4 (Portrait)',
                                    ])
                                    ->default('1:1')
                                    ->live()
                                    ->dehydrated(false)
                                    ->label('Rasio Foto'),
                                FileUpload::make('image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('variant-images')
                                    ->imageCropAspectRatio(fn ($state, $get) => $get('variant_image_ratio') ?? '1:1')
                                    ->imageResizeTargetWidth('600')
                                    ->imageResizeTargetHeight('600')
                                    ->nullable()
                                    ->columnSpanFull()
                                    ->label('Foto Warna (opsional)'),

                                // Nested sizes repeater
                                Repeater::make('sizes')
                                    ->relationship('sizes')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->placeholder('S, M, L, XL, XXL...')
                                            ->label('Ukuran'),
                                        TextInput::make('stock')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->label('Stok'),
                                        TextInput::make('sku')
                                            ->nullable()
                                            ->unique('product_variant_sizes', 'sku', ignoreRecord: true)
                                            ->label('SKU'),
                                        TextInput::make('weight')
                                            ->numeric()
                                            ->suffix('gram')
                                            ->placeholder('Override berat')
                                            ->label('Berat (opsional)'),
                                        Toggle::make('is_active')
                                            ->default(true)
                                            ->label('Aktif'),
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->label('Urutan'),
                                    ])
                                    ->columns(3)
                                    ->addActionLabel('+ Tambah Ukuran')
                                    ->reorderable('sort_order')
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->itemLabel(fn (array $state): string => $state['name'] ?? 'Ukuran Baru')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Warna')
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string => $state['name'] ?? 'Warna Baru'),
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
                EditAction::make(),
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
                // SoftDeletes: hapus = soft delete, data order history tetap aman
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Produk')
                    ->modalDescription('Produk akan diarsipkan (soft delete) dan hilang dari katalog. Riwayat order tetap utuh.'),
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
