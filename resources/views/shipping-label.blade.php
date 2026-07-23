<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman - {{ $order->order_number }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .print-controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-controls button {
            padding: 10px 24px;
            background: #7c3aed;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 8px;
        }

        .print-controls button:hover {
            background: #6d28d9;
        }

        .print-controls .btn-secondary {
            background: #6b7280;
        }

        .print-controls .btn-secondary:hover {
            background: #4b5563;
        }

        .label-container {
            width: 100mm;
            height: 150mm;
            margin: 0 auto;
            background: white;
            border: 2px solid #000;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            font-size: 8pt;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }

        .label-header .brand {
            font-size: 12pt;
            font-weight: 700;
            letter-spacing: 0.5mm;
        }

        .label-header .service-type {
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            background: #000;
            color: #fff;
            padding: 1mm 3mm;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            border-bottom: 1px solid #000;
            padding-bottom: 1.5mm;
            margin-bottom: 2mm;
            color: #333;
        }

        .section-label {
            font-size: 6.5pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3mm;
            margin-bottom: 0.5mm;
        }

        .recipient-section {
            border-bottom: 1.5px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            flex: 0 0 auto;
        }

        .recipient-name {
            font-size: 14pt;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1mm;
        }

        .recipient-address {
            font-size: 8pt;
            line-height: 1.4;
            margin-bottom: 1mm;
        }

        .recipient-phone {
            font-size: 8pt;
            font-weight: 600;
        }

        .postal-code {
            font-size: 16pt;
            font-weight: 700;
            text-align: right;
            margin-top: 1mm;
        }

        .sender-section {
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            font-size: 7pt;
            color: #333;
        }

        .sender-section .sender-name {
            font-weight: 600;
            font-size: 8pt;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }

        .details-row .detail-item {
            text-align: center;
        }

        .details-row .detail-label {
            font-size: 6pt;
            color: #666;
        }

        .details-row .detail-value {
            font-weight: 700;
            font-size: 8pt;
        }

        .barcode-section {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .barcode-section svg {
            width: 80mm;
            max-height: 20mm;
        }

        .barcode-section .waybill-text {
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 1mm;
            margin-top: 1mm;
        }

        .label-footer {
            text-align: center;
            font-size: 6pt;
            color: #666;
            border-top: 1px solid #000;
            padding-top: 1.5mm;
            margin-top: auto;
        }

        .label-footer .tracking-url {
            font-weight: 600;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .print-controls {
                display: none !important;
            }

            .label-container {
                border: 2px solid #000;
                page-break-inside: avoid;
                margin: 0;
            }

            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print()">🖨️ Cetak Label</button>
        <button class="btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="label-container">
        <!-- Header -->
        <div class="label-header">
            <span class="brand">ALIESMO</span>
            <span class="service-type">{{ $courierType }}</span>
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <span>Order: {{ $order->order_number }}</span>
            <span>{{ $order->created_at->format('d M Y') }}</span>
        </div>

        <!-- Recipient -->
        <div class="recipient-section">
            <div class="section-label">Kepada:</div>
            <div class="recipient-name">{{ $order->customer_name }}</div>
            <div class="recipient-address">{{ $order->shipping_address }}</div>
            <div class="recipient-phone">{{ $order->customer_phone }}</div>
            @if($order->shipping_area_id)
                <div class="postal-code">{{ $postalCode }}</div>
            @endif
        </div>

        <!-- Sender -->
        <div class="sender-section">
            <div class="section-label">Dari:</div>
            <div class="sender-name">Aliesmo</div>
            <div>{{ config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah') }}</div>
            <div>{{ config('services.biteship.origin_phone', '0813-888-3345') }}</div>
        </div>

        <!-- Shipment Details -->
        <div class="details-row">
            <div class="detail-item">
                <div class="detail-label">Berat</div>
                <div class="detail-value">{{ number_format($totalWeight / 1000, 1) }} kg</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Layanan</div>
                <div class="detail-value">{{ $serviceType }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Koli</div>
                <div class="detail-value">1</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Item</div>
                <div class="detail-value">{{ $order->items->count() }}</div>
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode-section">
            <svg id="barcode"></svg>
            <div class="waybill-text">{{ $waybillId }}</div>
        </div>

        <!-- Footer -->
        <div class="label-footer">
            Cek resi di <span class="tracking-url">bitsp.co</span> atau <span class="tracking-url">aliesmo.id/track</span>
        </div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $waybillId }}", {
            format: "CODE128",
            width: 2,
            height: 40,
            displayValue: false,
            margin: 0
        });
    </script>
</body>
</html>
