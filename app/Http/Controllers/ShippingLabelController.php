<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ShippingLabelController extends Controller
{
    public function show(Order $order)
    {
        // Eager load relasi yang dibutuhkan untuk menghitung berat
        $order->load('items.product', 'items.variant', 'items.size');
        $waybillId = $order->biteship_waybill_id ?? $order->tracking_number ?? '-';
        
        $courierMap = [
            'JNE' => 'JNE',
            'JNT Express' => 'J&T',
            'J&T Express' => 'J&T',
            'POS Indonesia' => 'POS',
        ];
        $courierType = $courierMap[$order->courier] ?? ($order->courier ?? '-');

        $serviceMap = [
            'jne' => 'Regular',
            'jnt' => 'EZ',
            'pos' => 'Regular',
        ];
        $serviceType = $serviceMap[strtolower($order->courier ?? '')] ?? 'Regular';

        $postalCode = '';
        if ($order->shipping_area_id) {
            $parts = explode('IDZ', $order->shipping_area_id);
            $postalCode = end($parts);
        }

        $totalWeight = $order->items->sum(function ($item) {
            // Prioritas berat: size > variant > product > default 300g
            if ($item->size_id && $item->size && $item->size->weight) {
                return $item->size->weight * $item->quantity;
            }
            if ($item->variant_id && $item->variant && $item->variant->weight) {
                return $item->variant->weight * $item->quantity;
            }
            if ($item->product && $item->product->weight) {
                return $item->product->weight * $item->quantity;
            }
            return 300 * $item->quantity;
        });

        if ($totalWeight === 0) {
            $totalWeight = 300;
        }

        return view('shipping-label', compact(
            'order',
            'waybillId',
            'courierType',
            'serviceType',
            'postalCode',
            'totalWeight'
        ));
    }
}
