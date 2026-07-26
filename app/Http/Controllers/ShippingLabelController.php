<?php

namespace App\Http\Controllers;

use App\Models\Order;

class ShippingLabelController extends Controller
{
    public function show(Order $order)
    {
        $order->load('items.product', 'items.variant', 'items.size');

        $waybillId = $order->biteship_waybill_id
            ?? $order->tracking_number
            ?? '-';

        $courierRaw = (string) ($order->courier ?? '');
        $courierKey = strtolower($courierRaw);

        // detect kurir → short name + logo file di /public
        [$courierShort, $courierClass, $courierLogo] = match (true) {
            str_contains($courierKey, 'j&t')
                || str_contains($courierKey, 'jnt')
                || str_contains($courierKey, 'jet') => [
                    'J&T',
                    'jnt',
                    'J&T_Express_logo.svg',
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

        $originName = 'Aliesmo';
        $originPhone = config('services.biteship.origin_phone', '08138883345');
        $originAddress = config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah');

        return view('shipping-label', [
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
            'originName' => $originName,
            'originPhone' => $originPhone,
            'originAddress' => $originAddress,
        ]);
    }
}
