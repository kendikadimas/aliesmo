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

        $courierLogoUrl = $courierLogo ? asset($courierLogo) : null;

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
        ];
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
