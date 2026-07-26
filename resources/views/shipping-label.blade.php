<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label - {{ $order->order_number }}</title>
    {{-- self-host: CSP blocks cdn.jsdelivr.net --}}
    <script src="{{ asset('js/JsBarcode.all.min.js') }}"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #d4d4d4;
            padding: 16px;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-controls { text-align: center; margin-bottom: 14px; }
        .print-controls button {
            padding: 10px 22px; border: none; border-radius: 6px;
            font-size: 13px; font-weight: 600; cursor: pointer; margin: 0 5px;
            background: #111; color: #fff;
        }
        .print-controls .btn-secondary { background: #6b7280; }

        /* standar resi thermal 100×150 mm (4×6") */
        .sheet {
            width: 100mm;
            min-height: 150mm;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #000;
        }
        table.lbl {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.lbl td {
            border: 1.5px solid #000;
            padding: 2.2mm 2.5mm;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.35;
        }

        .cell-header {
            padding: 3.5mm 3mm !important;
            vertical-align: middle !important;
        }
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3mm;
            min-height: 16mm;
        }
        .courier-box { flex: 0 0 34%; text-align: left; }
        .courier-logo-img {
            display: block;
            max-height: 13mm;
            max-width: 32mm;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .courier-logo-fallback {
            display: inline-block;
            font-size: 15pt;
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1;
            border: 2px solid #000;
            padding: 1.5mm 2mm;
            text-transform: uppercase;
        }
        .courier-logo-fallback.jne { color: #003399; border-color: #c8102e; }
        .courier-logo-fallback.jnt { color: #e31837; border-color: #e31837; }
        .courier-logo-fallback.pos { color: #ff6600; border-color: #003399; }
        .courier-sub {
            font-size: 7pt;
            margin-top: 1mm;
            color: #333;
            text-transform: uppercase;
        }
        .brand-box { flex: 1; text-align: center; }
        .brand-box img {
            height: 28px;
            width: auto;
            max-width: 100%;
            display: inline-block;
            object-fit: contain;
        }
        .brand-domain {
            font-size: 8pt;
            font-weight: 700;
            margin-top: 1mm;
        }

        .cell-waybill {
            text-align: center;
            padding: 3mm 2.5mm 2mm !important;
        }
        .barcode-wrap {
            min-height: 18mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .barcode-wrap svg,
        .barcode-wrap canvas {
            max-width: 94%;
            height: auto !important;
        }
        .waybill-caption {
            font-size: 9.5pt;
            font-weight: 600;
            margin-top: 1.5mm;
            letter-spacing: 0.2px;
        }

        .cell-meta {
            text-align: center;
            padding: 2.5mm !important;
            font-size: 9pt;
        }
        .cell-meta .line2 { margin-top: 1mm; }

        .cell-ref { width: 58%; }
        .cell-qty { width: 42%; vertical-align: middle !important; }
        .field-label {
            font-size: 8.5pt;
            font-weight: 700;
            margin-bottom: 1.2mm;
        }
        .cell-ref .barcode-wrap { min-height: 12mm; }
        .ref-text {
            font-size: 8pt;
            margin-top: 1.2mm;
            word-break: break-all;
            font-weight: 600;
        }
        .qty-line {
            font-size: 9.5pt;
            margin-bottom: 2mm;
        }
        .qty-line:last-child { margin-bottom: 0; }

        .cell-addr { width: 50%; }
        .addr-title {
            font-size: 8.5pt;
            font-weight: 700;
            margin-bottom: 1.5mm;
        }
        .addr-body {
            font-size: 9pt;
            line-height: 1.4;
            white-space: pre-line;
        }

        .cell-block { font-size: 9pt; }
        .cell-block strong { font-weight: 700; }
        .item-lines .item { display: block; margin-top: 0.6mm; }
        .item-lines .item:first-child { display: inline; margin-top: 0; }

        .cell-footer {
            text-align: center;
            padding: 2mm !important;
            font-size: 8pt;
            line-height: 1.4;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none !important; }
            .sheet {
                width: 100mm;
                min-height: 150mm;
                border: 2px solid #000;
                margin: 0;
                box-shadow: none;
            }
            /* thermal 100×150; printer A4 tetap cetak label di kiri-atas */
            @page { size: 100mm 150mm; margin: 0; }
        }
        @media print and (min-width: 140mm) {
            @page { size: A4; margin: 8mm; }
            .sheet { margin: 0 auto; }
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
            <tr>
                <td colspan="2" class="cell-header">
                    <div class="header-inner">
                        <div class="courier-box">
                            @if($courierLogoUrl)
                                <img src="{{ $courierLogoUrl }}" alt="{{ $courierShort }}" class="courier-logo-img">
                            @else
                                <div class="courier-logo-fallback {{ $courierClass }}">{{ $courierShort }}</div>
                            @endif
                            @if($serviceType)
                                <div class="courier-sub">{{ $serviceType }}</div>
                            @endif
                        </div>
                        <div class="brand-box">
                            <img src="https://aliesmo.id/aliesmo-horizontal.png" alt="Aliesmo"
                                 onerror="this.style.display='none'">
                            <div class="brand-domain">aliesmo.id</div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-waybill">
                    <div class="barcode-wrap">
                        <svg id="barcode-waybill"></svg>
                    </div>
                    <div class="waybill-caption">Nomor Resi - {{ $waybillId }}</div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-meta">
                    <div>Ongkos Kirim: Rp. {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</div>
                    <div class="line2">
                        Jenis Layanan - {{ $serviceLabel }}
                        @if($postalCode)
                            . Kode Rute - {{ $postalCode }}
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <td class="cell-ref">
                    <div class="field-label">Reference Number</div>
                    <div class="barcode-wrap">
                        <svg id="barcode-ref"></svg>
                    </div>
                    <div class="ref-text">{{ $reference }}</div>
                </td>
                <td class="cell-qty">
                    <div class="qty-line">Quantity: {{ $totalQty }} Pcs</div>
                    <div class="qty-line">Weight: {{ number_format((float) $totalWeightKg, 1, '.', '') }} Kg</div>
                </td>
            </tr>

            <tr>
                <td class="cell-addr">
                    <div class="addr-title">Alamat Penerima:</div>
                    <div class="addr-body">{{ $order->customer_name }}
{{ $order->customer_phone }}
{{ $order->shipping_address }}</div>
                </td>
                <td class="cell-addr">
                    <div class="addr-title">Alamat Pengirim:</div>
                    <div class="addr-body">{{ $originName }}
{{ $originPhone }}
{{ $originAddress }}</div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-block">
                    <strong>Jenis Barang :</strong>
                    <span class="item-lines">
                        @foreach($itemsLines as $line)
                            <span class="item">{{ $line }}</span>
                        @endforeach
                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-block">
                    <strong>Catatan :</strong> {{ $note }}
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-footer">
                    Pengiriman melalui Aliesmo<br>
                    aliesmo.id
                </td>
            </tr>
        </table>
    </div>

    <script>
        (function () {
            if (typeof JsBarcode !== 'function') return;

            function draw(id, value, opts) {
                if (!value || value === '-') return;
                var el = document.getElementById(id);
                if (!el) return;
                try {
                    JsBarcode(el, String(value), Object.assign({
                        format: 'CODE128',
                        displayValue: false,
                        margin: 0,
                        background: '#ffffff',
                        lineColor: '#000000'
                    }, opts));
                } catch (e) {}
            }

            draw('barcode-waybill', @json($waybillId), { width: 2, height: 56 });
            draw('barcode-ref', @json($reference), { width: 1.4, height: 38 });
        })();
    </script>
</body>
</html>
