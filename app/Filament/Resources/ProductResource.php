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
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
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
                // ponytail: tabs biar varian (182 SKU) cuma dirender kalau tab-nya dibuka
                Tabs::make('Produk')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Umum')->icon('heroicon-o-pencil-square')->schema([
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
                        Toggle::make('is_active')->label('Aktif'),
                        FileUpload::make('thumbnail')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->imageCropAspectRatio('1:1')
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
                        ]),
                        Tabs\Tab::make('Media')->icon('heroicon-o-photo')->schema([
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
                                FileUpload::make('path')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(8192)
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->imageCropAspectRatio('1:1')
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
                        ]),
                        Tabs\Tab::make('Varian')->icon('heroicon-o-squares-2x2')->schema([
                Section::make('Varian (Warna) & Ukuran')
                    ->description('Tambahkan varian warna produk. Setiap warna bisa punya ukuran (S, M, L, XL) dengan stok masing-masing. Jika produk tidak punya varian, kosongkan bagian ini.')
                    ->headerActions([
                        \Filament\Actions\Action::make('import_tsv')
                            ->label('Import dari CSV')
                            ->icon('heroicon-o-table-cells')
                            ->color('gray')
                            ->form([
                                \Filament\Forms\Components\Placeholder::make('file_picker_html')
                                    ->label('File CSV')
                                    ->content(new \Illuminate\Support\HtmlString('
                                        <div x-data="{
                                            loading: false,
                                            done: false,
                                            filename: \'\',
                                            rowcount: 0,
                                            progress: 0,
                                            pickFile() {
                                                this.$refs.input.click();
                                            },
                                            readFile(e) {
                                                const f = e.target.files[0]; if (!f) return;
                                                this.loading = true; this.done = false; this.progress = 10;
                                                const r = new FileReader();
                                                r.onprogress = ev => { if(ev.lengthComputable) this.progress = Math.round(ev.loaded/ev.total*80)+10; };
                                                r.onload = ev => {
                                                    this.progress = 100;
                                                    const text = ev.target.result;
                                                    this.rowcount = text.split(/\r?\n/).filter(l=>l.trim()).length - 1;
                                                    this.filename = f.name;
                                                    $wire.set(\'mountedActions.0.data.csv_data\', text);
                                                    this.loading = false; this.done = true;
                                                };
                                                r.readAsText(f);
                                            }
                                        }">
                                            <input type="file" x-ref="input" accept=".csv,.txt" style="display:none" @change="readFile($event)" />
                                            <button type="button" @click="pickFile()"
                                                :style="done ? \'background:#16a34a\' : \'background:#6b7280\'"
                                                style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;padding:6px 14px;color:#fff;border:none;border-radius:6px;font-size:14px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                <span x-text="loading ? \'Membaca...\' : (done ? \'Ganti File\' : \'Pilih File CSV\')"></span>
                                            </button>
                                            <div x-show="loading || done" style="margin-top:8px;">
                                                <div x-show="loading" style="height:6px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                                                    <div :style="\'width:\'+progress+\'%\'" style="height:100%;background:#16a34a;border-radius:4px;transition:width 0.2s ease;"></div>
                                                </div>
                                                <div x-show="done" style="font-size:13px;color:#16a34a;">
                                                    <strong x-text="filename"></strong> &mdash; <span x-text="rowcount"></span> baris data siap diimport
                                                </div>
                                            </div>
                                        </div>
                                    ')),
                                \Filament\Forms\Components\Hidden::make('csv_data')
                                    ->required(),
                                \Filament\Schemas\Components\Section::make('Mapping Kolom')
                                    ->description('Nomor kolom di CSV (dimulai dari 1). Set 0 jika kolom tidak ada.')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('col_sku')->label('Kolom SKU')->numeric()->default(1)->minValue(1)->required(),
                                        \Filament\Forms\Components\TextInput::make('col_warna')->label('Kolom Warna')->numeric()->default(2)->minValue(1)->required(),
                                        \Filament\Forms\Components\TextInput::make('col_lengan')->label('Kolom Lengan')->numeric()->default(3)->minValue(0)->helperText('0 = tidak ada'),
                                        \Filament\Forms\Components\TextInput::make('col_ukuran')->label('Kolom Ukuran')->numeric()->default(4)->minValue(1)->required(),
                                        \Filament\Forms\Components\TextInput::make('col_stok')->label('Kolom Stok')->numeric()->default(5)->minValue(1)->required(),
                                    ])
                                    ->columns(5)
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->action(function (array $data, $livewire) {
                                $product = $livewire->getRecord();
                                if (!$product) {
                                    $livewire->create();
                                    $product = $livewire->getRecord();
                                }
                                if (!$product) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Produk gagal disimpan.')
                                        ->body('Pastikan field wajib (Nama, Slug, SKU) sudah diisi sebelum import.')
                                        ->warning()->persistent()->send();
                                    return;
                                }

                                $colMap = [
                                    'sku'    => (int)($data['col_sku']    ?? 1) - 1,
                                    'warna'  => (int)($data['col_warna']  ?? 2) - 1,
                                    'lengan' => (int)($data['col_lengan'] ?? 3) - 1,
                                    'ukuran' => (int)($data['col_ukuran'] ?? 4) - 1,
                                    'stok'   => (int)($data['col_stok']   ?? 5) - 1,
                                ];

                                // baca CSV dari textarea (FileReader JS sudah isi kontennya)
                                $lines = preg_split('/\r?\n/', $data['csv_data'] ?? '');

                                $variantMap = [];
                                $firstRow = true;
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if ($line === '') continue;
                                    $cols = str_getcsv($line);
                                    // auto-skip kolom NO di awal
                                    if (count($cols) >= 5 && is_numeric($cols[0]) && !is_numeric($cols[1])) {
                                        array_shift($cols);
                                    }
                                    // skip baris header
                                    if ($firstRow) {
                                        $firstRow = false;
                                        if (!is_numeric($cols[$colMap['stok']] ?? '')) continue;
                                    }
                                    $stok = $cols[$colMap['stok']] ?? '';
                                    if (!is_numeric($stok)) continue;
                                    $sku    = trim($cols[$colMap['sku']]    ?? '');
                                    $warna  = trim($cols[$colMap['warna']]  ?? '');
                                    $lengan = $colMap['lengan'] >= 0 ? trim($cols[$colMap['lengan']] ?? '') : '';
                                    $ukuran = trim($cols[$colMap['ukuran']] ?? '');
                                    $variantKey = $lengan !== '' ? "$warna - $lengan" : $warna;
                                    $variantMap[$variantKey][] = compact('sku', 'ukuran', 'stok');
                                }

                                if (empty($variantMap)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Tidak ada data valid yang bisa diparse.')
                                        ->warning()->send();
                                    return;
                                }

                                // ponytail: insert langsung ke DB, bypass Livewire state = tidak ada OOM
                                \Illuminate\Support\Facades\DB::transaction(function () use ($product, $variantMap): void {
                                    foreach ($variantMap as $variantName => $sizes) {
                                        [$warna, $lengan] = explode(' - ', $variantName, 2) + ['', ''];
                                        $variant = $product->variants()->firstOrCreate(
                                            ['name' => $variantName],
                                            ['warna' => $warna, 'lengan' => $lengan, 'price' => $product->price, 'stock' => 0, 'is_active' => true, 'sort_order' => 0]
                                        );
                                        foreach ($sizes as $s) {
                                            $variant->sizes()->updateOrCreate(
                                                ['sku' => $s['sku']],
                                                ['name' => $s['ukuran'], 'stock' => (int) $s['stok'], 'is_active' => true, 'sort_order' => 0]
                                            );
                                        }
                                    }
                                });

                                $skuCount = array_sum(array_map('count', $variantMap));
                                \Filament\Notifications\Notification::make()
                                    ->title(count($variantMap) . ' varian, ' . $skuCount . ' SKU berhasil diimport.')
                                    ->success()->send();

                                // refresh halaman supaya repeater menampilkan data baru
                                redirect()->to(request()->header('Referer') ?? url()->current());
                            }),
                    ])
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('variant-images')
                                    ->imageResizeTargetWidth('600')
                                    ->imageResizeTargetHeight('600')
                                    ->nullable()
                                    ->label('Foto'),
                                TextInput::make('warna')
                                    ->nullable()
                                    ->placeholder('Army Green, Hitam...')
                                    ->label('Warna'),
                                TextInput::make('lengan')
                                    ->nullable()
                                    ->placeholder('Panjang, Pendek...')
                                    ->label('Lengan'),
                                // ponytail: name di-derive dari warna+lengan saat save supaya backward compat
                                TextInput::make('name')
                                    ->nullable()
                                    ->placeholder('Army Green - Panjang')
                                    ->label('Label Varian')
                                    ->helperText('Isi bebas atau biarkan sesuai Warna - Lengan'),
                                // Ukuran sebagai tabel — native Repeater::table(), tidak bisa nested di table mode
                                Repeater::make('sizes')
                                    ->relationship('sizes')
                                    ->table([
                                        TableColumn::make('Ukuran'),
                                        TableColumn::make('Stok'),
                                        TableColumn::make('Harga'),
                                        TableColumn::make('SKU'),
                                    ])
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->placeholder('S, M, L, XL...')
                                            ->hiddenLabel(),
                                        TextInput::make('stock')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->rules([
                                                // ponytail: validasi stock size <= stock produk utama, cukup warning di form
                                                fn () => function (string $attribute, $value, \Closure $fail) {
                                                    $productId = request()->route('record');
                                                    if (!$productId) return;
                                                    $product = \App\Models\Product::find($productId);
                                                    if ($product && (int) $value > $product->stock) {
                                                        $fail("Stok ukuran ({$value}) melebihi stok produk utama ({$product->stock}).");
                                                    }
                                                },
                                            ])
                                            ->hiddenLabel(),
                                        TextInput::make('price')
                                            ->numeric()
                                            ->prefix('Rp ')
                                            ->placeholder('Kosong = ikut harga varian')
                                            ->nullable()
                                            ->hiddenLabel(),
                                        TextInput::make('sku')
                                            ->nullable()
                                            ->unique('product_variant_sizes', 'sku', ignoreRecord: true)
                                            ->hiddenLabel(),
                                    ])
                                    ->addActionLabel('+ Ukuran')
                                    ->reorderable('sort_order')
                                    ->defaultItems(0)
                                    ->label('Ukuran & Stok')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Varian')
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->collapsed()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): string =>
                                trim(($state['warna'] ?? '') . ' ' . ($state['lengan'] ?? ''))
                                ?: ($state['name'] ?? 'Varian Baru')
                            ),
                    ]),
                        ]),
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
