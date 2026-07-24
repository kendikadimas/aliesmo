<?php
namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\StockService;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\OrderShippedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

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
                        TextEntry::make('courier_service')
                            ->label('Layanan')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-'),
                        TextEntry::make('tracking_number')
                            ->label('No. Resi')
                            ->placeholder('Belum diinput'),
                        TextEntry::make('tracking_url')
                            ->label('Link Tracking')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('Belum diinput'),
                        TextEntry::make('biteship_order_id')
                            ->label('Biteship Order ID')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('biteship_tracking_id')
                            ->label('Biteship Tracking ID')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('biteship_waybill_id')
                            ->label('Biteship Waybill')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('biteship_status')
                            ->label('Status Biteship')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'confirmed' => 'info',
                                'picking', 'picked' => 'warning',
                                'dropping', 'dropped' => 'success',
                                'delivered' => 'success',
                                'returned', 'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('-'),
                    ])
                    ->headerActions([
                        Action::make('processCodOrder')
                            ->label('Proses Order COD')
                            ->icon('heroicon-o-truck')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Proses Order COD')
                            ->modalDescription('Buat pengiriman Biteship untuk order COD ini? Paket akan siap dijemput kurir.')
                            ->modalSubmitActionLabel('Ya, Proses')
                            ->action(function (Order $record) {
                                try {
                                    $orderService = app(OrderService::class);

                                    // Untuk COD: kurangi stok + buat shipment Biteship
                                    // Tidak set paid_at karena customer bayar saat paket tiba
                                    DB::transaction(function () use ($record) {
                                        $record->update(['status' => OrderStatus::Processing]);
                                        app(StockService::class)->decrementForOrder($record);
                                    });

                                    // Buat shipment di Biteship — di luar transaction agar timeout tidak rollback DB
                                    $orderService->createBiteshipShipment($record->fresh());

                                    \Filament\Notifications\Notification::make()
                                        ->title('Order COD diproses')
                                        ->body("Shipment Biteship untuk order #{$record->order_number} sudah dibuat.")
                                        ->success()
                                        ->send();
                                } catch (\Throwable $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Gagal memproses order COD')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->visible(fn (Order $record): bool =>
                                $record->payment_method === 'cod'
                                && empty($record->biteship_order_id)
                                && in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])
                            ),
                        Action::make('printLabel')
                            ->label('Cetak Label')
                            ->icon('heroicon-o-printer')
                            ->color('primary')
                            ->url(fn (Order $record): string => route('orders.label', $record))
                            ->openUrlInNewTab()
                            ->visible(false), // TODO: label custom belum selesai — print dari dashboard Biteship
                        Action::make('biteshipDashboard')
                            ->label('Buka di Biteship')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('gray')
                            ->url(fn (Order $record): string =>
                                $record->biteship_order_id
                                    ? 'https://dashboard.biteship.com/orders/' . $record->biteship_order_id
                                    : 'https://dashboard.biteship.com/orders'
                            )
                            ->openUrlInNewTab(),
                    ])
                    ->columns(3),
                Section::make('Item Pesanan')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_name')->label('Produk'),
                                TextEntry::make('variant_name')->label('Varian')->placeholder('-'),
                                TextEntry::make('price')->label('Harga')->money('IDR'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            ])
                            ->columns(5),
                    ]),
                Section::make('Informasi Pembayaran')
                    ->schema([
                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        TextEntry::make('shipping_cost')->label('Ongkir')->money('IDR'),
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'cod'           => 'warning',
                                'qris'          => 'info',
                                'bank_transfer' => 'primary',
                                default         => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'cod'           => 'COD (Bayar di Tempat)',
                                'qris'          => 'QRIS',
                                'bank_transfer' => 'Transfer Bank',
                                default         => $state ?? '-',
                            }),
                        TextEntry::make('paid_at')->label('Waktu Konfirmasi Pembayaran')->dateTime()->placeholder('Belum dikonfirmasi'),
                    ])->columns(2),
                Section::make('Bukti Pembayaran')
                    ->schema([
                        // URL lewat admin route (disk private) — bukan path /storage
                        \Filament\Infolists\Components\ImageEntry::make('payment_proof_preview')
                            ->label('Foto Bukti Bayar')
                            ->getStateUsing(fn (Order $record): ?string =>
                                $record->payment?->proof_image
                                    ? route('admin.payment-proof', $record)
                                    : null
                            )
                            ->height(300)
                            ->width(300)
                            ->url(fn (Order $record): ?string =>
                                $record->payment?->proof_image
                                    ? route('admin.payment-proof', $record)
                                    : null
                            )
                            ->openUrlInNewTab()
                            ->visible(fn (Order $record): bool => (bool) $record->payment?->proof_image)
                            ->placeholder('Belum diunggah'),
                        TextEntry::make('payment.proof_note')
                            ->label('Catatan')
                            ->placeholder('-'),
                        TextEntry::make('payment.created_at')
                            ->label('Waktu Upload')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->headerActions([
                        Action::make('confirmPaymentInline')
                            ->label('Konfirmasi Pembayaran')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Konfirmasi Pembayaran')
                            ->modalDescription('Konfirmasi pembayaran order ini? Stok akan dikurangi dan order pengiriman Biteship akan dibuat otomatis.')
                            ->modalSubmitActionLabel('Ya, Konfirmasi')
                            ->action(function (Order $record) {
                                $orderService = app(OrderService::class);
                                $orderService->markAsPaid($record, $record->payment_method ?? 'bank_transfer');

                                \Filament\Notifications\Notification::make()
                                    ->title('Pembayaran dikonfirmasi')
                                    ->body("Order #{$record->order_number} sudah dikonfirmasi.")
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn (Order $record): bool =>
                                in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])
                                && $record->payment_method !== 'cod'
                            ),
                    ])
                    ->columns(3)
                    ->visible(fn (Order $record): bool => $record->payment && $record->payment->proof_image),
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
                TextColumn::make('biteship_status')->label('Biteship')->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'picking', 'picked' => 'warning',
                        'dropping', 'dropped' => 'success',
                        'delivered' => 'success',
                        'returned', 'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Tanggal')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('confirmPayment')
                    ->label('Konfirmasi Pembayaran')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription('Konfirmasi pembayaran order ini? Stok akan dikurangi dan order pengiriman Biteship akan dibuat otomatis.')
                    ->modalSubmitActionLabel('Ya, Konfirmasi')
                    ->action(function (Order $record) {
                        $orderService = app(OrderService::class);
                        $orderService->markAsPaid($record, $record->payment_method ?? 'bank_transfer');
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pembayaran dikonfirmasi')
                            ->body("Order #{$record->order_number} sudah dibayar. Order Biteship sedang diproses.")
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (Order $record): bool => $record->status !== OrderStatus::Pending),

                Action::make('retryBiteship')
                    ->label('Retry Biteship')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Retry Biteship Order')
                    ->modalDescription('Buat order pengiriman Biteship untuk order ini?')
                    ->modalSubmitActionLabel('Ya, Retry')
                    ->action(function (Order $record) {
                        $orderService = app(OrderService::class);
                        $orderService->createBiteshipShipment($record);
                        
                        $record->refresh();
                        
                        if ($record->biteship_order_id) {
                            \Filament\Notifications\Notification::make()
                                ->title('Biteship order berhasil dibuat')
                                ->body("Order ID: {$record->biteship_order_id}")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal membuat Biteship order')
                                ->body('Cek log untuk detail error.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Order $record): bool => 
                        in_array($record->status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped]) 
                        && empty($record->biteship_order_id)
                    ),

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
                        $newStatus    = OrderStatus::from($data['status']);
                        $stockService = app(StockService::class);

                        $paidStatuses = [
                            OrderStatus::Paid,
                            OrderStatus::Processing,
                            OrderStatus::Shipped,
                            OrderStatus::Completed,
                        ];

                        // Idempotent via stock_decremented_at
                        if (in_array($newStatus, $paidStatuses) && !$record->stock_decremented_at) {
                            $record->load('items');
                            $stockService->decrementForOrder($record);
                        }

                        $record->update(['status' => $newStatus]);

                        // Kirim notifikasi email ke customer
                        try {
                            $notification = new OrderStatusUpdatedNotification($record->fresh());
                            if ($record->user_id) {
                                $record->user->notify($notification);
                            } else {
                                Notification::route('mail', $record->customer_email)
                                    ->notify($notification);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Gagal kirim notifikasi status order: ' . $e->getMessage(), ['order' => $record->order_number]);
                        }
                    })
                    ->hidden(fn (Order $record): bool => in_array($record->status, [OrderStatus::Completed, OrderStatus::Cancelled, OrderStatus::Expired])),

                Action::make('updateTracking')
                    ->label('Input Resi')
                    ->icon('heroicon-o-truck')
                    ->form([
                        TextInput::make('courier_display')
                            ->label('Kurir')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tracking_number')
                            ->label('No. Resi')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->fillForm(fn (Order $record): array => [
                        'courier_display' => $record->courier ?? '(tidak ada data kurir)',
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
                        // Use Biteship tracking URL if available, otherwise use hardcoded courier URLs
                        $trackingUrl = $record->tracking_url ?? ($record->courier ? ($courierUrls[$record->courier] ?? null) : null);
                        $record->update([
                            'tracking_number' => $data['tracking_number'] ?: null,
                            'tracking_url'    => $trackingUrl,
                        ]);

                        // Kirim notifikasi resi ke customer
                        try {
                            $fresh = $record->fresh();
                            $notification = new OrderShippedNotification($fresh);
                            if ($record->user_id) {
                                $record->user->notify($notification);
                            } else {
                                Notification::route('mail', $record->customer_email)
                                    ->notify($notification);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Gagal kirim notifikasi resi: ' . $e->getMessage(), ['order' => $record->order_number]);
                        }
                    }),

                Action::make('cancelOrder')
                    ->label('Batalkan & Restock')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pesanan')
                    ->modalDescription(fn (Order $record) => $record->biteship_order_id
                        ? 'Batalkan di Biteship + restock stok? Order Biteship juga akan di-cancel.'
                        : 'Batalkan order ini dan kembalikan stok produk secara otomatis?')
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    ->action(function (Order $record) {
                        try {
                            app(OrderService::class)->cancel(
                                $record,
                                "Dibatalkan dari admin panel order #{$record->order_number}"
                            );
                            \Filament\Notifications\Notification::make()
                                ->title('Order dibatalkan')
                                ->body($record->biteship_order_id
                                    ? 'Stok di-restock. Shipment Biteship juga dibatalkan.'
                                    : 'Stok di-restock.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Cancel order gagal', [
                                'order' => $record->order_number,
                                'error' => $e->getMessage(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal batalkan order')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->hidden(fn (Order $record): bool => in_array($record->status, [
                        OrderStatus::Completed,
                        OrderStatus::Cancelled,
                        OrderStatus::Expired,
                        OrderStatus::Shipped,
                    ])),

                // SoftDeletes: arsip order dari list; riwayat item tetap di DB
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pesanan')
                    ->modalDescription('Pesanan diarsipkan (soft delete) dan hilang dari daftar. Data tetap di database. Batalkan dulu di Biteship jika shipment masih aktif.'),
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
                        // Gunakan query yang sudah terfilter dari tabel, bukan fallback ke semua order
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();

                        $tempFile = tempnam(sys_get_temp_dir(), 'orders_');

                        $writer = new Writer();
                        $writer->openToFile($tempFile);

                        $headerStyle = (new Style())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setBackgroundColor(Color::rgb(128, 0, 0));

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
