<?php
namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\StockService;
use App\Enums\StockMovementType;
use Filament\Actions\Action;
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        TextEntry::make('order_number'),
                        TextEntry::make('customer_name'),
                        TextEntry::make('customer_email'),
                        TextEntry::make('customer_phone'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->dateTime(),
                    ])->columns(2),
                Section::make('Shipping Address')
                    ->schema([
                        TextEntry::make('shipping_address'),
                    ]),
                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_name'),
                                TextEntry::make('price')->money('IDR'),
                                TextEntry::make('quantity'),
                                TextEntry::make('subtotal')->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('subtotal')->money('IDR'),
                        TextEntry::make('shipping_cost')->money('IDR'),
                        TextEntry::make('total')->money('IDR'),
                        TextEntry::make('payment_method'),
                        TextEntry::make('paid_at')->dateTime(),
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
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('total')->money('IDR')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('payment_method'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')
                            ->options([
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, Order $record) {
                        $record->update(['status' => OrderStatus::from($data['status'])]);
                    })
                    ->hidden(fn (Order $record): bool => in_array($record->status, [OrderStatus::Completed, OrderStatus::Cancelled, OrderStatus::Expired])),

                Action::make('cancelOrder')
                    ->label('Cancel & Restock')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
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
                    ->label('Export CSV')
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

                        return response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['Order Number', 'Customer', 'Total', 'Status', 'Payment Method', 'Date']);

                            foreach ($records as $order) {
                                fputcsv($handle, [
                                    $order->order_number,
                                    $order->customer_name,
                                    $order->total,
                                    $order->status->value,
                                    $order->payment_method,
                                    $order->created_at->toDateTimeString(),
                                ]);
                            }

                            fclose($handle);
                        }, 'orders-export.csv');
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
