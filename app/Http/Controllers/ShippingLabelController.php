<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ShippingLabelController extends Controller
{
    private const BULK_MAX = 50;

    public function show(Order $order)
    {
        $order->load('items.product', 'items.variant', 'items.size');

        return view('shipping-label', [
            'labels' => [$this->buildLabelData($order)],
        ]);
    }

    public function bulk(Request $request)
    {
        $raw = (string) $request->query('ids', '');
        $ids = collect(preg_split('/[,\s]+/', $raw) ?: [])
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(self::BULK_MAX)
            ->values();

        if ($ids->isEmpty()) {
            abort(404, 'Tidak ada order dipilih.');
        }

        $orders = Order::query()
            ->with(['items.product', 'items.variant', 'items.size'])
            ->whereIn('id', $ids)
            ->where(function ($q) {
                $q->whereNotNull('biteship_waybill_id')
                    ->orWhereNotNull('tracking_number');
            })
            ->get()
            ->sortBy(fn (Order $o) => $ids->search($o->id))
            ->values();

        if ($orders->isEmpty()) {
            abort(404, 'Tidak ada order dengan resi di antara yang dipilih.');
        }

        $labels = $orders->map(fn (Order $o) => $this->buildLabelData($o))->all();

        return view('shipping-label', [
            'labels' => $labels,
        ]);
    }

    private function buildLabelData(Order $order): array
    {
        $waybillId = $order->biteship_waybill_id
            ?? $order->tracking_number
            ?? '-';

        $courierRaw = (string) ($order->courier ?? '');
        $courierKey = strtolower($courierRaw);

        [$courierShort, $courierClass, $courierLogo] = match (true) {
            str_contains($courierKey, 'j&t')
                || str_contains($courierKey, 'jnt')
                || str_contains($courierKey, 'jet') => [
                    'J&T',
                    'jnt',
                    'jnt-express-logo.svg',
                ],
            str_contains($courierKey, 'pos') => [
                'POS',
                'pos',
                'POSIND_2023.svg',
            ],
            str_contains($courierKey, 'jne') || $courierKey === 'jne' => [
                'JNE',
                'jne',
                'New_Logo_JNE.png',
            ],
            default => [strtoupper($courierRaw ?: '-'), '', null],
        };

        // prod sering missing/empty logo file → fallback teks
        $courierLogoUrl = null;
        if ($courierLogo) {
            $logoPath = public_path($courierLogo);
            if (is_file($logoPath) && filesize($logoPath) > 100) {
                $courierLogoUrl = asset($courierLogo);
            }
        }

        $serviceCode = $order->courier_service
            ?: match ($courierClass) {
                'jnt' => 'ez',
                'pos' => 'reguler',
                default => 'reg',
            };

        $serviceLabel = match (strtolower((string) $serviceCode)) {
            'reg', 'reguler', 'regular' => 'Reguler',
            'ez' => 'EZ',
            'yes' => 'YES',
            'oke' => 'OKE',
            'sps' => 'SPS',
            default => strtoupper((string) $serviceCode) ?: 'Reguler',
        };

        $postalCode = '';
        if ($order->shipping_area_id && preg_match('/IDZ(\d+)/', $order->shipping_area_id, $m)) {
            $postalCode = $m[1];
        } elseif ($order->shipping_area_id) {
            $parts = explode('IDZ', $order->shipping_area_id);
            $postalCode = (string) (end($parts) ?: '');
        }

        $totalWeightG = $order->items->sum(function ($item) {
            $w = $item->size?->weight
                ?? $item->variant?->weight
                ?? $item->product?->weight
                ?? 300;

            return max(1, (int) $w) * (int) $item->quantity;
        });
        if ($totalWeightG <= 0) {
            $totalWeightG = 300;
        }

        $totalWeightKg = $totalWeightG / 1000;
        $totalQty = max(1, (int) $order->items->sum('quantity'));

        $itemsLines = $order->items->map(function ($item) {
            $name = $item->product_name;
            if ($item->variant_name) {
                $name .= ' - '.$item->variant_name;
            }
            if ($item->size_name) {
                $name .= ' / '.$item->size_name;
            }
            $sku = $item->product?->sku ? '['.$item->product->sku.'] ' : '';

            return $sku.$item->quantity.'x '.$name;
        })->values()->all();

        if ($itemsLines === []) {
            $itemsLines = ['-'];
        }

        $note = $order->payment_method === 'cod'
            ? 'COD — tagih Rp. '.number_format((float) $order->total, 0, ',', '.')
            : 'Harap cek isi paket saat diterima.';

        $reference = $order->order_number
            ?: ($order->biteship_order_id ?: '-');

        return [
            'order' => $order,
            'waybillId' => $waybillId,
            'courierShort' => $courierShort,
            'courierClass' => $courierClass,
            'courierLogoUrl' => $courierLogoUrl,
            'serviceType' => $serviceLabel,
            'serviceLabel' => $serviceLabel,
            'postalCode' => $postalCode,
            'totalWeightKg' => $totalWeightKg,
            'totalQty' => $totalQty,
            'itemsLines' => $itemsLines,
            'note' => $note,
            'reference' => $reference,
            'originName' => 'Aliesmo',
            'originPhone' => config('services.biteship.origin_phone', '08138883345'),
            'originAddress' => config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah'),
            'recipientName' => $this->maskName((string) $order->customer_name),
            'recipientPhone' => $this->maskPhone((string) $order->customer_phone),
            'recipientAddress' => (string) ($order->shipping_address ?: '-'),
            // SVG server-side — JsBarcode.js sering 404 di prod (SPA catch-all)
            'barcodeWaybillSvg' => $this->code128Svg((string) $waybillId, 56, 1.9),
            'barcodeRefSvg' => $this->code128Svg((string) $reference, 38, 1.4),
        ];
    }

    /**
     * Minimal CODE128-B → SVG (no JS / no composer dep).
     * ponytail: enough for resi + order number; swap to picqer if need GS1.
     */
    private function code128Svg(string $text, int $height = 50, float $mw = 1.6): string
    {
        $text = trim($text);
        if ($text === '' || $text === '-') {
            return '';
        }

        // 0-105 patterns (bars/spaces widths), start B=104, stop=106
        static $pat = [
            '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
            '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
            '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
            '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
            '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
            '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
            '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
            '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
            '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
            '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
            '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
        ];

        $codes = [104]; // Start Code B
        $sum = 104;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($text[$i]);
            if ($c < 32 || $c > 126) {
                $c = 63; // '?'
            }
            $val = $c - 32;
            $codes[] = $val;
            $sum += $val * ($i + 1);
        }
        $codes[] = $sum % 103; // checksum
        $codes[] = 106; // stop

        $modules = '';
        foreach ($codes as $code) {
            $modules .= $pat[$code] ?? '212222';
        }

        $x = 0.0;
        $rects = '';
        $bar = true;
        $n = strlen($modules);
        for ($i = 0; $i < $n; $i++) {
            $w = ((int) $modules[$i]) * $mw;
            if ($bar) {
                $rects .= sprintf(
                    '<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#000"/>',
                    $x,
                    $w,
                    $height
                );
            }
            $x += $w;
            $bar = ! $bar;
        }

        $totalW = max(1, (int) ceil($x));

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" role="img" aria-label="%s">%s</svg>',
            $totalW,
            $height,
            $totalW,
            $height,
            e($text),
            $rects
        );
    }

    /** Dimas Kendika → D**** K****** */
    private function maskName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '-';
        }

        return collect(preg_split('/\s+/u', $name) ?: [])
            ->filter()
            ->map(function (string $part) {
                $len = mb_strlen($part);
                if ($len <= 1) {
                    return '*';
                }

                return mb_substr($part, 0, 1).str_repeat('*', $len - 1);
            })
            ->implode(' ');
    }

    /** 087864562253 → 08********** */
    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        $len = strlen($digits);
        if ($len === 0) {
            return '-';
        }
        if ($len <= 2) {
            return str_repeat('*', $len);
        }

        return substr($digits, 0, 2).str_repeat('*', $len - 2);
    }
}
