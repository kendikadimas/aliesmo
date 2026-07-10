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
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected string $view = 'filament.pages.sales-report';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public ?array $data = [];
    public $startDate = null;
    public $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->buildFilteredQuery())
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('customer_name'),
                TextColumn::make('total')->money('IDR'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([
                Action::make('exportXlsx')
                    ->label('Export Laporan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action('exportXlsx'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getSummaryStats(): array
    {
        $orders = $this->buildFilteredQuery()->get();

        $totalRevenue = $orders->sum(fn ($o) => (float) $o->total);
        $totalOrders = $orders->count();
        $itemsSold = $orders->sum(fn ($o) => $o->items()->count());

        return [
            'revenue' => $totalRevenue,
            'orders' => $totalOrders,
            'items_sold' => $itemsSold,
        ];
    }

    public function exportXlsx()
    {
        $orders = $this->buildFilteredQuery()->orderByDesc('created_at')->get();

        $fileName = 'laporan-penjualan-'
            . ($this->startDate ?? 'all') . '-sd-'
            . ($this->endDate ?? 'all') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'sales_');

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
            ['No. Pesanan', 'Pelanggan', 'Total (Rp)', 'Status', 'Tanggal'],
            $headerStyle
        ));

        $style = new Style();
        foreach ($orders as $order) {
            $writer->addRow(Row::fromValues([
                $order->order_number,
                $order->customer_name,
                (float) $order->total,
                $order->status->value,
                $order->created_at->translatedFormat('d F Y H:i'),
            ], $style));
        }

        $writer->close();

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildFilteredQuery()
    {
        return Order::whereIn('status', [
            OrderStatus::Paid,
            OrderStatus::Completed,
            OrderStatus::Processing,
            OrderStatus::Shipped,
        ])
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate));
    }
}
