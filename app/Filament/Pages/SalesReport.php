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
    public $startDate;
    public $endDate;

    public function mount(): void
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
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
                Action::make('exportXlsx')
                    ->label('Export Laporan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action('exportXlsx'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getSummaryStats(): array
    {
        $query = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Processing, OrderStatus::Shipped])
            ->when($this->startDate, fn ($q) => $q->whereDate('orders.created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('orders.created_at', '<=', $this->endDate));

        return [
            'revenue'    => (clone $query)->sum('total'),
            'orders'     => (clone $query)->count(),
            'items_sold' => (clone $query)->join('order_items', 'orders.id', '=', 'order_items.order_id')->sum('order_items.quantity'),
        ];
    }

    public function exportXlsx(): StreamedResponse
    {
        $start  = $this->startDate;
        $end    = $this->endDate;

        \Carbon\Carbon::setLocale('id');

        $orders = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Processing, OrderStatus::Shipped])
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end,   fn ($q) => $q->whereDate('created_at', '<=', $end))
            ->get();

        $fileName       = 'laporan-penjualan-' . now()->format('Ymd') . '.xlsx';
        $tempFile       = tempnam(sys_get_temp_dir(), 'xlsx_');
        $startFormatted = $start ? \Carbon\Carbon::parse($start)->translatedFormat('d F Y') : '-';
        $endFormatted   = $end   ? \Carbon\Carbon::parse($end)->translatedFormat('d F Y')   : '-';
        $totalRevenue   = $orders->sum('total');
        $totalOrders    = $orders->count();

        // ── Styles ──────────────────────────────────────────────
        $borderAll = new Border(
            new BorderPart(Border::BOTTOM, Color::rgb(180, 180, 180), BorderPart::WIDTH_THIN, BorderPart::STYLE_SOLID),
            new BorderPart(Border::TOP,    Color::rgb(180, 180, 180), BorderPart::WIDTH_THIN, BorderPart::STYLE_SOLID),
            new BorderPart(Border::LEFT,   Color::rgb(180, 180, 180), BorderPart::WIDTH_THIN, BorderPart::STYLE_SOLID),
            new BorderPart(Border::RIGHT,  Color::rgb(180, 180, 180), BorderPart::WIDTH_THIN, BorderPart::STYLE_SOLID),
        );

        // Judul utama — putih tebal di atas navy
        $styleTitle = (new Style())
            ->setFontBold()
            ->setFontSize(14)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(15, 40, 80));

        // Label info (Periode, Dicetak pada)
        $styleLabel = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::rgb(60, 60, 60))
            ->setBackgroundColor(Color::rgb(235, 240, 250));

        // Heading section (RINGKASAN, dll)
        $styleSection = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(30, 80, 150));

        // Nilai ringkasan
        $styleValue = (new Style())
            ->setFontSize(10)
            ->setFontColor(Color::rgb(20, 20, 20))
            ->setBackgroundColor(Color::rgb(245, 248, 255))
            ->setBorder($borderAll);

        // Header kolom tabel
        $styleHeader = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(20, 60, 120))
            ->setBorder($borderAll);

        // Baris data ganjil
        $styleRowOdd = (new Style())
            ->setFontSize(10)
            ->setFontColor(Color::rgb(30, 30, 30))
            ->setBackgroundColor(Color::WHITE)
            ->setBorder($borderAll);

        // Baris data genap
        $styleRowEven = (new Style())
            ->setFontSize(10)
            ->setFontColor(Color::rgb(30, 30, 30))
            ->setBackgroundColor(Color::rgb(240, 245, 255))
            ->setBorder($borderAll);

        // Baris kosong
        $styleEmpty = new Style();

        // ── Writer ──────────────────────────────────────────────
        $writer = new Writer();
        $writer->openToFile($tempFile);

        // Judul
        $writer->addRow(Row::fromValues(['LAPORAN PENJUALAN — ALIESMO', '', '', '', ''], $styleTitle));

        // Info periode
        $writer->addRow(Row::fromValues(['Periode', $startFormatted . ' s/d ' . $endFormatted, '', '', ''], $styleLabel));
        $writer->addRow(Row::fromValues(['Dicetak pada', now()->translatedFormat('d F Y, H:i'), '', '', ''], $styleLabel));
        $writer->addRow(Row::fromValues(['', '', '', '', ''], $styleEmpty));

        // Ringkasan
        $writer->addRow(Row::fromValues(['RINGKASAN', '', '', '', ''], $styleSection));
        $writer->addRow(Row::fromValues(['Total Pesanan', $totalOrders, '', '', ''], $styleValue));
        $writer->addRow(Row::fromValues(['Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'), '', '', ''], $styleValue));
        $writer->addRow(Row::fromValues(['', '', '', '', ''], $styleEmpty));

        // Header tabel
        $writer->addRow(Row::fromValues(['No. Pesanan', 'Nama Pelanggan', 'Total (Rp)', 'Status', 'Tanggal'], $styleHeader));

        // Data
        foreach ($orders as $i => $order) {
            $style = $i % 2 === 0 ? $styleRowOdd : $styleRowEven;
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

    private function getFilteredOrders()
    {
        return Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Completed])
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->get();
    }
}
