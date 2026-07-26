<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label - {{ $order->order_number }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #e5e5e5; padding: 16px; color: #000; }
        .print-controls { text-align: center; margin-bottom: 16px; }
        .print-controls button {
            padding: 10px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; margin: 0 6px; background: #18181b; color: #fff;
        }
        .print-controls .btn-secondary { background: #6b7280; }
        .sheet {
            width: 100mm; margin: 0 auto; background: #fff; border: 1.5px solid #000;
            font-size: 8.5pt; line-height: 1.35;
        }
        table.lbl { width: 100%; border-collapse: collapse; }
        table.lbl td, table.lbl th { border: 1px solid #000; padding: 3mm 2.5mm; vertical-align: top; }
        .no-border td { border: none !important; }
        .header-row td { border-bottom: 1.5px solid #000; padding: 4mm 3mm; vertical-align: middle; }
        .courier-name {
            font-size: 14pt; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .brand-wrap { text-align: center; }
        .brand-wrap img { height: 28px; width: auto; display: inline-block; }
        .brand-sub { font-size: 7pt; color: #444; margin-top: 1mm; }
        .center { text-align: center; }
        .barcode-main { padding: 3mm 2mm 2mm !important; }
        .barcode-main svg { max-width: 100%; height: auto; }
        .waybill-text { font-size: 9pt; font-weight: 600; margin-top: 1.5mm; }
        .meta-line { font-size: 8.5pt; }
        .label-soft { font-size: 7.5pt; color: #222; margin-bottom: 1mm; font-weight: 600; }
        .ref-code { font-size: 7.5pt; margin-top: 1mm; word-break: break-all; }
        .addr-title { font-size: 8pt; font-weight: 700; margin-bottom: 1.5mm; }
        .addr-body { font-size: 8pt; white-space: pre-line; }
        .items-line { font-size: 8pt; }
        .footer-note { text-align: center; font-size: 7.5pt; padding: 2.5mm !important; }
        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none !important; }
            .sheet { width: 100mm; border: 1.5px solid #000; margin: 0; }
            @page { size: 100mm 150mm; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button type="button" onclick="window.print()">Cetak Label</button>
        <button type="button" class="btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="sheet">
        <table class="lbl">
            {{-- Header: kurir + logo Aliesmo --}}
            <tr class="header-row">
                <td style="width: 32%; border-right: none;">
                    <div class="courier-name">{{ $courierType }}</div>
                    @if($serviceType)
                        <div style="font-size: 7.5pt; margin-top: 1mm;">{{ $serviceType }}</div>
                    @endif
                </td>
                <td style="width: 68%; border-left: none;">
                    <div class="brand-wrap">
                        <img src="{{ asset('aliesmo-horizontal.png') }}" alt="Aliesmo">
                        <div class="brand-sub">aliesmo.id</div>
                    </div>
                </td>
            </tr>

            {{-- Barcode resi --}}
            <tr>
                <td colspan="2" class="center barcode-main">
                    @if($waybillId && $waybillId !== '-')
                        <svg id="barcode-waybill"></svg>
                        <div class="waybill-text">Nomor Resi - {{ $waybillId }}</div>
                    @else
                        <div class="waybill-text">Nomor Resi - Belum tersedia</div>
                    @endif
                </td>
            </tr>

            {{-- Ongkir + layanan --}}
            <tr>
                <td colspan="2" class="center">
                    <div class="meta-line">Ongkos Kirim: Rp. {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</div>
                    <div class="meta-line" style="margin-top: 1mm;">
                        Jenis Layanan - {{ $serviceType ?: '-' }}
                        @if($postalCode)
                            . Kode Rute - {{ $postalCode }}
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Reference + qty/weight --}}
            <tr>
                <td style="width: 55%;">
                    <div class="label-soft">Reference Number</div>
                    @if($reference)
                        <svg id="barcode-ref"></svg>
                        <div class="ref-code">{{ $reference }}</div>
                    @else
                        <div class="ref-code">-</div>
                    @endif
                </td>
                <td style="width: 45%;">
                    <div style="margin-bottom: 2mm;"><strong>Quantity:</strong> {{ $totalQty }} Pcs</div>
                    <div><strong>Weight:</strong> {{ number_format($totalWeightKg, 1, '.', '') }} Kg</div>
                </td>
            </tr>

            {{-- Alamat --}}
            <tr>
                <td>
                    <div class="addr-title">Alamat Penerima:</div>
                    <div class="addr-body">{{ $order->customer_name }}
{{ $order->customer_phone }}
{{ $order->shipping_address }}</div>
                </td>
                <td>
                    <div class="addr-title">Alamat Pengirim:</div>
                    <div class="addr-body">{{ $originName }}
{{ $originPhone }}
{{ $originAddress }}</div>
                </td>
            </tr>

            {{-- Items --}}
            <tr>
                <td colspan="2">
                    <div class="items-line"><strong>Jenis Barang :</strong>
                        @foreach($itemsLines as $i => $line)
                            @if($i > 0)<br>@endif
                            {{ $line }}
                        @endforeach
                    </div>
                </td>
            </tr>

            {{-- Catatan --}}
            <tr>
                <td colspan="2">
                    <div class="items-line"><strong>Catatan :</strong> {{ $note }}</div>
                </td>
            </tr>

            {{-- Footer --}}
            <tr>
                <td colspan="2" class="footer-note">
                    Pengiriman melalui Aliesmo<br>
                    aliesmo.id
                </td>
            </tr>
        </table>
    </div>

    <script>
        @if($waybillId && $waybillId !== '-')
        JsBarcode("#barcode-waybill", @json($waybillId), {
            format: "CODE128",
            width: 1.6,
            height: 48,
            displayValue: false,
            margin: 0
        });
        @endif
        @if($reference)
        JsBarcode("#barcode-ref", @json($reference), {
            format: "CODE128",
            width: 1.2,
            height: 32,
            displayValue: false,
            margin: 0
        });
        @endif
    </script>
</body>
</html>
