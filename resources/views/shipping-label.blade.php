<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label - {{ count($labels) === 1 ? $labels[0]['order']->order_number : '('.count($labels).')' }}</title>
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
        .print-guide {
            max-width: 420px;
            margin: 0 auto 16px;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: left;
            font-size: 12px;
            line-height: 1.45;
            color: #222;
        }
        .print-guide summary {
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            list-style: none;
        }
        .print-guide summary::-webkit-details-marker { display: none; }
        .print-guide summary::before { content: '+ '; color: #666; }
        .print-guide[open] summary::before { content: '− '; }
        .print-guide ol { margin: 10px 0 0 18px; padding: 0; }
        .print-guide li { margin-bottom: 5px; }
        .print-guide .note {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e5e5e5;
            color: #555;
            font-size: 11px;
        }

        /* thermal A6 = 105×148 mm */
        .sheet {
            width: 105mm;
            min-height: 148mm;
            margin: 0 auto 16px;
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
            gap: 2mm;
            min-height: 18mm;
        }
        .courier-box { flex: 0 0 28%; text-align: left; }
        .courier-logo-img {
            display: block;
            max-height: 8mm;
            max-width: 22mm;
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
        .brand-box { flex: 1; text-align: right; }
        .brand-box img {
            height: 12mm;
            width: auto;
            max-width: 55mm;
            display: block;
            margin-left: auto;
            object-fit: contain;
            /* logo file = putih di hitam; invert biar hitam di label putih */
            filter: invert(1);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .brand-domain {
            font-size: 7.5pt;
            font-weight: 700;
            margin-top: 0.8mm;
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
            .print-controls,
            .print-guide { display: none !important; }
            .sheet {
                width: 105mm;
                min-height: 148mm;
                border: 2px solid #000;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
            }
            .sheet:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            /* thermal A6 105×148 — @page via raw echo (Blade/Livewire mangling) */
        }
        {!! '@page { size: 105mm 148mm; margin: 0; }' !!}
    </style>
</head>
<body>
    <div class="print-controls">
        <button type="button" onclick="window.print()">Cetak Label{{ count($labels) > 1 ? ' ('.count($labels).')' : '' }}</button>
        <button type="button" class="btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <details class="print-guide">
        <summary>Panduan cetak thermal A6 (Xprinter dll.)</summary>
        <ol>
            <li>Install driver resmi printer thermal (Xprinter / merek lain).</li>
            <li>Buat / pilih ukuran kertas <strong>A6 (105 × 148 mm)</strong> di driver printer.</li>
            <li>Klik <strong>Cetak Label</strong>, pilih printer thermal di dialog.</li>
            <li>Paper size: <strong>A6</strong> · Margins: <strong>None</strong> · Scale: <strong>100%</strong>.</li>
            <li>Aktifkan <strong>Background graphics</strong> agar logo &amp; barcode jelas.</li>
        </ol>
        <p class="note">Label ini fixed A6. Browser tidak bisa auto-pilih printer; set sekali, Chrome biasanya mengingat. Jangan “Fit to page”.{{ count($labels) > 1 ? ' Bulk: 1 order = 1 lembar (page break).' : '' }}</p>
    </details>

    @foreach($labels as $i => $L)
    @php
        $order = $L['order'];
        $waybillId = $L['waybillId'];
        $courierShort = $L['courierShort'];
        $courierClass = $L['courierClass'];
        $courierLogoUrl = $L['courierLogoUrl'];
        $serviceType = $L['serviceType'];
        $serviceLabel = $L['serviceLabel'];
        $postalCode = $L['postalCode'];
        $totalWeightKg = $L['totalWeightKg'];
        $totalQty = $L['totalQty'];
        $itemsLines = $L['itemsLines'];
        $note = $L['note'];
        $reference = $L['reference'];
        $originName = $L['originName'];
        $originPhone = $L['originPhone'];
        $originAddress = $L['originAddress'];
        $recipientName = $L['recipientName'];
        $recipientPhone = $L['recipientPhone'];
        $recipientAddress = $L['recipientAddress'];
        $barcodeWaybillSvg = $L['barcodeWaybillSvg'] ?? '';
        $barcodeRefSvg = $L['barcodeRefSvg'] ?? '';
    @endphp
    <div class="sheet">
        <table class="lbl">
            <tr>
                <td colspan="2" class="cell-header">
                    <div class="header-inner">
                        <div class="courier-box">
                            @if($courierLogoUrl)
                                <img src="{{ $courierLogoUrl }}" alt="{{ $courierShort }}" class="courier-logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block'">
                                <div class="courier-logo-fallback {{ $courierClass }}" style="display:none">{{ $courierShort }}</div>
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
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="cell-waybill">
                    <div class="barcode-wrap">
                        {!! $barcodeWaybillSvg !!}
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
                        {!! $barcodeRefSvg !!}
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
                    <div class="addr-body">{{ $recipientName }}
{{ $recipientPhone }}
{{ $recipientAddress }}</div>
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
    @endforeach
</body>
</html>
