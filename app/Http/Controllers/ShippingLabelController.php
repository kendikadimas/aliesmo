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

        $courierType = $order->courier ?: '-';
        $serviceType = $order->courier_service
            ?: match (strtolower((string) $order->courier)) {
                'jne' => 'Reguler',
                'jnt', 'j&t express', 'jnt express' => 'EZ',
                'pos', 'pos indonesia' => 'Reguler',
                default => 'Reguler',
            };

        $postalCode = '';
        if ($order->shipping_area_id) {
            // area_id Biteship berakhir ...IDZ{kodepos}
            if (preg_match('/IDZ(\d+)/', $order->shipping_area_id, $m)) {
                $postalCode = $m[1];
            } else {
                $parts = explode('IDZ', $order->shipping_area_id);
                $postalCode = end($parts) ?: '';
            }
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
            : 'Harap cek isi paket sebelum diterima.';

        $reference = $order->order_number
            ?: ($order->biteship_order_id ?: '-');

        $originName = config('app.name', 'Aliesmo');
        $originPhone = config('services.biteship.origin_phone', '08138883345');
        $originAddress = config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah');

        return view('shipping-label', compact(
            'order',
            'waybillId',
            'courierType',
            'serviceType',
            'postalCode',
            'totalWeightKg',
            'totalQty',
            'itemsLines',
            'note',
            'reference',
            'originName',
            'originPhone',
            'originAddress',
        ));
    }
}
