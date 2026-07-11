<?php
namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\StockService;
use App\Enums\StockMovementType;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Transaksi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pesanan';
    }

    public static function getModelLabel(): string
    {
        return 'Pesanan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pesanan';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pesanan')
                    ->schema([
                        TextEntry::make('order_number')->label('No. Pesanan'),
                        TextEntry::make('customer_name')->label('Nama Pelanggan'),
                        TextEntry::make('customer_email')->label('Email'),
                        TextEntry::make('customer_phone')->label('No. Telepon'),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('created_at')->label('Tanggal')->dateTime(),
                    ])->columns(2),
                Section::make('Alamat Pengiriman')
                    ->schema([
                        TextEntry::make('shipping_address')->label('Alamat'),
                    ]),
                Section::make('Informasi Resi')
                    ->schema([
                        TextEntry::make('courier')
                            ->label('Kurir')
                            ->placeholder('Belum diinput'),
                        TextEntry::make('tracking_number')
                            ->label('No. Resi')
                            ->placeholder('Belum diinput'),
                        TextEntry::make('tracking_url')
                            ->label('Link Tracking')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('Belum diinput'),
                    ])->columns(3),
                Section::make('Item Pesanan')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_name')->label('Produk'),
                                TextEntry::make('price')->label('Harga')->money('IDR'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Informasi Pembayaran')
                    ->schema([
                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        TextEntry::make('shipping_cost')->label('Ongkir')->money('IDR'),
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('payment_method')->label('Metode Pembayaran'),
                        TextEntry::make('paid_at')->label('Waktu Konfirmasi Pembayaran')->dateTime()->placeholder('Belum dikonfirmasi'),
                    ])->columns(2),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return static::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label('No. Pesanan')->searchable()->sortable(),
                TextColumn::make('customer_name')->label('Nama Pelanggan')->searchable(),
                TextColumn::make('total')->label('Total')->money('IDR')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('payment_method')->label('Metode Bayar'),
                TextColumn::make('tracking_number')->label('No. Resi')->searchable()->toggleable(),
                TextColumn::make('created_at')->label('Tanggal')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('updateStatus')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'completed' => 'Selesai',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, Order $record) {
                        $record->update(['status' => OrderStatus::from($data['status'])]);
                    })
                    ->hidden(fn (Order $record): bool => in_array($record->status, [OrderStatus::Completed, OrderStatus::Cancelled, OrderStatus::Expired])),

                Action::make('updateTracking')
                    ->label('Input Resi')
                    ->icon('heroicon-o-truck')
                    ->form([
                        // Jika kurir sudah ada di order, tampil read-only
                        TextInput::make('courier_display')
                            ->label('Kurir')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (array $arguments, $record) => !empty($record?->courier)),
                        // Jika kurir belum ada (order lama), admin pilih manual
                        Select::make('courier')
                            ->label('Kurir')
                            ->options([
                                'JNE'           => 'JNE',
                                'JNT Express'   => 'J&T Express',
                                'SiCepat'       => 'SiCepat',
                                'Anteraja'      => 'Anteraja',
                                'Ninja'         => 'Ninja Xpress',
                                'Pos Indonesia' => 'Pos Indonesia',
                                'Lion Parcel'   => 'Lion Parcel',
                            ])
                            ->required()
                            ->visible(fn (array $arguments, $record) => empty($record?->courier)),
                        TextInput::make('tracking_number')
                            ->label('No. Resi')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->fillForm(fn (Order $record): array => [
                        'courier_display' => $record->courier ?? '',
                        'tracking_number' => $record->tracking_number,
                    ])
                    ->action(function (array $data, Order $record) {
                        $courierUrls = [
                            'JNE'           => 'https://jne.co.id/tracking-package',
                            'JNT Express'   => 'https://jet.co.id/track',
                            'SiCepat'       => 'https://www.sicepat.com/',
                            'Anteraja'      => 'https://anteraja.id/id/tracking',
                            'Ninja'         => 'https://www.ninjaxpress.co/en-id/tracking',
                            'Pos Indonesia' => 'https://www.posindonesia.co.id/id/tracking',
                            'Lion Parcel'   => 'https://lionparcel.com/track',
                        ];
                        // Pakai kurir dari record jika ada, fallback ke pilihan admin
                        $courier = $record->courier ?: ($data['courier'] ?? null);
                        $record->update([
                            'courier'         => $courier,
                            'tracking_number' => $data['tracking_number'] ?: null,
                            'tracking_url'    => $courierUrls[$courier] ?? null,
                        ]);
                    }),

                Action::make('cancelOrder')
                    ->label('Batalkan & Restock')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pesanan')
                    ->modalDescription('Batalkan order ini dan kembalikan stok produk secara otomatis?')
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    ->action(function (Order $record) {
                        DB::transaction(function () use ($record) {
                            // Restock setiap item
                            $stockService = app(StockService::class);
                            $record->load('items');
                            foreach ($record->items as $item) {
                                try {
                                    $stockService->adjustStock(
                                        $item->product_id,
                                        $item->quantity,
                                        StockMovementType::Return,
                                        "Restock - Order #{$record->order_number} dibatalkan"
                                    );
                                } catch (\Exception $e) {
                                    // Produk mungkin sudah dihapus, skip saja
                                }
                            }
                            $record->update(['status' => OrderStatus::Cancelled]);
                        });
                    })
                    ->hidden(fn (Order $record): bool => in_array($record->status, [
                        OrderStatus::Completed,
                        OrderStatus::Cancelled,
                        OrderStatus::Expired,
                        OrderStatus::Shipped,
                    ])),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name])),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->toolbarActions([
                Action::make('exportSelected')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($livewire) {
                        $records = $livewire->getTableRecords();
                        if ($records->isEmpty()) {
                            $records = Order::query();
                            foreach ($livewire->getTableFiltersForm()->getState() as $filter => $value) {
                                // Apply filters
                            }
                            $records = $records->get();
                        }

                        $tempFile = tempnam(sys_get_temp_dir(), 'orders_');

                        $writer = new Writer();
                        $writer->openToFile($tempFile);

                        $headerStyle = (new Style())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setBackgroundColor(Color::rgb(128, 0, 0))
                            ->setBorder(new Border(
                                new BorderPart(BorderPart::BOTTOM, Color::BLACK, 1, Border::STYLE_THIN),
                            ));

                        $writer->addRow(Row::fromValues(
                            ['No. Pesanan', 'Pelanggan', 'Total', 'Status', 'Metode Pembayaran', 'Tanggal'],
                            $headerStyle
                        ));

                        $style = new Style();
                        foreach ($records as $order) {
                            $writer->addRow(Row::fromValues([
                                $order->order_number,
                                $order->customer_name,
                                (float) $order->total,
                                $order->status->value,
                                $order->payment_method,
                                $order->created_at->translatedFormat('d F Y H:i'),
                            ], $style));
                        }

                        $writer->close();

                        return response()->streamDownload(function () use ($tempFile) {
                            readfile($tempFile);
                            @unlink($tempFile);
                        }, 'pesanan-export.xlsx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
