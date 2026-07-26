<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label - {{ $order->order_number }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #d4d4d4;
            padding: 20px;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-controls { text-align: center; margin-bottom: 16px; }
        .print-controls button {
            padding: 10px 22px; border: none; border-radius: 6px;
            font-size: 13px; font-weight: 600; cursor: pointer; margin: 0 5px;
            background: #111; color: #fff;
        }
        .print-controls .btn-secondary { background: #6b7280; }

        /* ukuran mendekati label thermal / sample Biteship */
        .sheet {
            width: 105mm;
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
            padding: 2.8mm 2.5mm;
            vertical-align: top;
            font-size: 8.5pt;
            line-height: 1.35;
        }

        /* ===== Header: kurir kiri + brand tengah ===== */
        .cell-header {
            padding: 5mm 3mm !important;
            text-align: center;
            vertical-align: middle !important;
        }
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4mm;
            min-height: 18mm;
        }
        .courier-box {
            flex: 0 0 30%;
            text-align: left;
        }
        .courier-logo-img {
            display: block;
            max-height: 14mm;
            max-width: 28mm;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .courier-logo-fallback {
            display: inline-block;
            font-size: 16pt;
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1;
            border: 2px solid #000;
            padding: 2mm 2.5mm;
            text-transform: uppercase;
        }
        .courier-logo-fallback.jne { color: #003399; border-color: #c8102e; }
        .courier-logo-fallback.jnt { color: #e31837; border-color: #e31837; }
        .courier-logo-fallback.pos { color: #ff6600; border-color: #003399; }
        .courier-sub {
            font-size: 6.5pt;
            margin-top: 1.2mm;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .brand-box {
            flex: 1;
            text-align: center;
        }
        .brand-box img {
            height: 32px;
            width: auto;
            max-width: 100%;
            display: inline-block;
            object-fit: contain;
        }
        .brand-domain {
            font-size: 8pt;
            font-weight: 600;
            margin-top: 1.5mm;
            letter-spacing: 0.3px;
        }

        /* ===== Barcode resi ===== */
        .cell-waybill {
            text-align: center;
            padding: 3.5mm 3mm 2.5mm !important;
        }
        .cell-waybill svg { max-width: 92%; height: auto; }
        .waybill-caption {
            font-size: 9pt;
            font-weight: 500;
            margin-top: 2mm;
        }

        /* ===== Ongkir / layanan ===== */
        .cell-meta {
            text-align: center;
            padding: 2.8mm 2.5mm !important;
            font-size: 9pt;
        }
        .cell-meta .line2 { margin-top: 1.2mm; }

        /* ===== Reference | Qty ===== */
        .cell-ref { width: 58%; }
        .cell-qty { width: 42%; vertical-align: middle !important; }
        .field-label {
            font-size: 8.5pt;
            font-weight: 600;
            margin-bottom: 1.5mm;
        }
        .cell-ref svg { max-width: 100%; height: auto; display: block; margin: 0 auto; }
        .ref-text {
            font-size: 8pt;
            margin-top: 1.5mm;
            word-break: break-all;
        }
        .qty-line {
            font-size: 9pt;
            margin-bottom: 2.5mm;
        }
        .qty-line:last-child { margin-bottom: 0; }

        /* ===== Alamat ===== */
        .cell-addr { width: 50%; }
        .addr-title {
            font-size: 8.5pt;
            font-weight: 700;
            margin-bottom: 1.8mm;
        }
        .addr-body {
            font-size: 8.5pt;
            line-height: 1.4;
            white-space: pre-line;
        }

        /* ===== Barang / catatan ===== */
        .cell-block { font-size: 8.5pt; }
        .cell-block strong { font-weight: 700; }
        .item-lines { display: inline; }
        .item-lines .item { display: block; margin-top: 0.8mm; margin-left: 0; }
        .item-lines .item:first-child { display: inline; margin-top: 0; }

        /* ===== Footer ===== */
        .cell-footer {
            text-align: center;
            padding: 2.5mm !important;
            font-size: 8pt;
            line-height: 1.45;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none !important; }
            .sheet {
                width: 105mm;
                border: 2px solid #000;
                margin: 0;
                box-shadow: none;
            }
            @page { size: 105mm 148mm; margin: 0; }
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
            {{-- 1. Header --}}
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
                            <img src="{{ asset('aliesmo-horizontal.png') }}" alt="Aliesmo">
                            <div class="brand-domain">aliesmo.id</div>
                        </div>
                        <div style="flex: 0 0 12%;"></div>
                    </div>
                </td>
            </tr>

            {{-- 2. Barcode resi --}}
            <tr>
                <td colspan="2" class="cell-waybill">
                    @if($waybillId && $waybillId !== '-')
                        <svg id="barcode-waybill"></svg>
                        <div class="waybill-caption">Nomor Resi - {{ $waybillId }}</div>
                    @else
                        <div class="waybill-caption">Nomor Resi - Belum tersedia</div>
                    @endif
                </td>
            </tr>

            {{-- 3. Ongkir + layanan --}}
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

            {{-- 4. Reference | Quantity / Weight --}}
            <tr>
                <td class="cell-ref">
                    <div class="field-label">Reference Number</div>
                    @if($reference && $reference !== '-')
                        <svg id="barcode-ref"></svg>
                        <div class="ref-text">{{ $reference }}</div>
                    @else
                        <div class="ref-text">-</div>
                    @endif
                </td>
                <td class="cell-qty">
                    <div class="qty-line">Quantity: {{ $totalQty }} Pcs</div>
                    <div class="qty-line">Weight: &nbsp; {{ number_format($totalWeightKg, 1, '.', '') }} Kg</div>
                </td>
            </tr>

            {{-- 5. Alamat --}}
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

            {{-- 6. Jenis barang --}}
            <tr>
                <td colspan="2" class="cell-block">
                    <strong>Jenis Barang :</strong>
                    <span class="item-lines">
                        @foreach($itemsLines as $i => $line)
                            <span class="item">{{ $line }}</span>
                        @endforeach
                    </span>
                </td>
            </tr>

            {{-- 7. Catatan --}}
            <tr>
                <td colspan="2" class="cell-block">
                    <strong>Catatan :</strong> &nbsp; {{ $note }}
                </td>
            </tr>

            {{-- 8. Footer --}}
            <tr>
                <td colspan="2" class="cell-footer">
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
            width: 1.8,
            height: 52,
            displayValue: false,
            margin: 2,
            background: "#ffffff"
        });
        @endif
        @if($reference && $reference !== '-')
        JsBarcode("#barcode-ref", @json($reference), {
            format: "CODE128",
            width: 1.3,
            height: 36,
            displayValue: false,
            margin: 2,
            background: "#ffffff"
        });
        @endif
    </script>
</body>
</html>
