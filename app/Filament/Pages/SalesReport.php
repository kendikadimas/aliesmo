<?php
namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected string $view = 'filament.pages.sales-report';

    public ?array $data = [];
    public $startDate;
    public $endDate;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Processing, OrderStatus::Shipped])
                    ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
                    ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            )
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('customer_name'),
                TextColumn::make('total')->money('IDR'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->action('exportCsv'),
                Action::make('exportXlsx')
                    ->label('Export XLSX')
                    ->action('exportXlsx'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getSummaryStats(): array
    {
        $query = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate));

        return [
            'revenue' => (clone $query)->sum('total'),
            'orders' => (clone $query)->count(),
            'items_sold' => (clone $query)->join('order_items', 'orders.id', '=', 'order_items.order_id')->sum('order_items.quantity'),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $orders = $this->getFilteredOrders();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Customer', 'Total', 'Status', 'Date']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->total,
                    $order->status->value,
                    $order->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'sales-report.csv');
    }

    public function exportXlsx(): StreamedResponse
    {
        $orders = $this->getFilteredOrders();

        $fileName = 'sales-report-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($orders) {
            $writer = new Writer();
            $writer->openToBrowser('php://output');
            $writer->addRow(['Order Number', 'Customer', 'Total', 'Status', 'Date']);

            foreach ($orders as $order) {
                $writer->addRow([
                    $order->order_number,
                    $order->customer_name,
                    (float) $order->total,
                    $order->status->value,
                    $order->created_at->toDateTimeString(),
                ]);
            }

            $writer->close();
        }, $fileName);
    }

    private function getFilteredOrders()
    {
        return Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->get();
    }
}
