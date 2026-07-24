<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookController extends Controller
{
    /**
     * Handle Biteship webhook events.
     *
     * Events:
     * - order.status: Status update for shipment
     * - order.price: Price change (when actual weight differs)
     * - order.waybill_id: Waybill ID update
     */
    public function handle(Request $request)
    {
        Log::debug('Biteship webhook: incoming request', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Fail-closed: secret wajib — webhook terbuka jika kosong
        $webhookSecret = config('services.biteship.webhook_secret');
        if (empty($webhookSecret)) {
            Log::error('Biteship webhook: BITESHIP_WEBHOOK_SECRET not configured');
            return response()->json(['success' => false, 'message' => 'Webhook not configured'], 503);
        }

        $providedSecret = $request->header('Authorization') ?? $request->input('secret');
        if ($providedSecret !== $webhookSecret && $providedSecret !== 'Bearer ' . $webhookSecret) {
            Log::warning('Biteship webhook: invalid secret', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $orderId = $request->input('order_id');

        Log::info('Biteship webhook received', [
            'event'    => $event,
            'order_id' => $orderId,
            'status'   => $request->input('status'),
            'payload'  => $request->all(),
        ]);

        // Cari order berdasarkan biteship_order_id
        $order = Order::where('biteship_order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Biteship webhook: order not found', [
                'order_id' => $orderId,
                'event' => $event,
            ]);
            return response()->json(['success' => true, 'message' => 'Order not found, ignored']);
        }

        Log::debug('Biteship webhook: order found', [
            'order_number' => $order->order_number,
            'current_status' => $order->status->value,
            'current_biteship_status' => $order->biteship_status,
        ]);

        switch ($event) {
            case 'order.status':
                return $this->handleStatusUpdate($order, $request);

            case 'order.waybill_id':
                return $this->handleWaybillUpdate($order, $request);

            case 'order.price':
                return $this->handlePriceUpdate($order, $request);

            default:
                Log::info('Biteship webhook: unknown event', [
                    'event' => $event,
                    'order' => $order->order_number,
                ]);
                return response()->json(['success' => true]);
        }
    }

    /**
     * Handle order status update from Biteship.
     *
     * Statuses resmi (docs): confirmed, scheduled, allocated, picking_up, picked,
     * cancelled, on_hold, in_transit, dropping_off, return_in_transit,
     * returned, rejected, disposed, courier_not_found, delivered
     */
    private function handleStatusUpdate(Order $order, Request $request)
    {
        $status = $request->input('status');
        $trackingId = $request->input('courier_tracking_id');
        $waybillId = $request->input('courier_waybill_id');
        $driverName = $request->input('courier_driver_name');
        $driverPhone = $request->input('courier_driver_phone');
        $trackingLink = $request->input('courier_link');

        Log::debug('Biteship handleStatusUpdate', [
            'order' => $order->order_number,
            'old_status' => $order->biteship_status,
            'new_status' => $status,
            'tracking_id' => $trackingId,
            'waybill_id' => $waybillId,
            'driver' => $driverName,
        ]);

        $updateData = [
            'biteship_status'      => $status,
            'biteship_tracking_id' => $trackingId ?? $order->biteship_tracking_id,
            'biteship_waybill_id'  => $waybillId ?? $order->biteship_waybill_id,
            'tracking_number'      => $waybillId ?? $order->tracking_number,
        ];

        if ($trackingLink) {
            $updateData['tracking_url'] = $trackingLink;
        }

        // Sync Order::status berdasarkan status Biteship
        // Form aktivasi Biteship membutuhkan ini agar order status terupdate otomatis
        // Status resmi Biteship (dari docs): confirmed, scheduled, allocated, picking_up,
        // picked, cancelled, on_hold, in_transit, dropping_off, return_in_transit,
        // returned, rejected, disposed, courier_not_found, delivered
        $orderStatusMap = [
            'delivered'         => OrderStatus::Completed,
            'returned'          => OrderStatus::Cancelled,
            'return_in_transit' => OrderStatus::Cancelled,
            'cancelled'         => OrderStatus::Cancelled,
            'rejected'          => OrderStatus::Cancelled,
            'disposed'          => OrderStatus::Cancelled,
            'courier_not_found' => OrderStatus::Cancelled,
        ];

        // Restock hanya saat final fail — BUKAN return_in_transit (masih di jalan balik)
        $restockStatuses = ['returned', 'cancelled', 'rejected', 'disposed', 'courier_not_found'];

        if (isset($orderStatusMap[$status])) {
            $updateData['status'] = $orderStatusMap[$status];

            Log::info('Biteship webhook: syncing order status', [
                'order'           => $order->order_number,
                'biteship_status' => $status,
                'order_status'    => $orderStatusMap[$status]->value,
            ]);
        }

        $order->update($updateData);

        // Restock stok saat kiriman final batal/return (idempotent via stock_decremented_at)
        if (in_array($status, $restockStatuses, true)) {
            try {
                app(StockService::class)->restockForOrder(
                    $order->fresh(),
                    "Biteship {$status} — order #{$order->order_number}"
                );
                Log::info('Biteship webhook: restock attempted', [
                    'order'  => $order->order_number,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                Log::error('Biteship webhook: restock failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Biteship status updated', [
            'order'        => $order->order_number,
            'status'       => $status,
            'waybill_id'   => $waybillId,
            'driver'       => $driverName,
            'driver_phone' => $driverPhone,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Handle waybill ID update from Biteship.
     */
    private function handleWaybillUpdate(Order $order, Request $request)
    {
        $waybillId = $request->input('courier_waybill_id');
        $trackingId = $request->input('courier_tracking_id');
        $trackingLink = $request->input('courier_link');

        if ($waybillId) {
            $updateData = [
                'biteship_waybill_id'  => $waybillId,
                'biteship_tracking_id' => $trackingId ?? $order->biteship_tracking_id,
                'tracking_number'      => $waybillId,
            ];

            if ($trackingLink) {
                $updateData['tracking_url'] = $trackingLink;
            }

            $order->update($updateData);

            Log::info('Biteship waybill updated', [
                'order'      => $order->order_number,
                'waybill_id' => $waybillId,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle price update from Biteship.
     * Price changes when actual weight differs from estimated.
     * Update shipping_cost dan recalculate total order secara otomatis.
     */
    private function handlePriceUpdate(Order $order, Request $request)
    {
        $newPrice = $request->input('price');

        Log::info('Biteship price update', [
            'order'        => $order->order_number,
            'new_price'    => $newPrice,
            'old_shipping' => $order->shipping_cost,
        ]);

        if ($newPrice && (int) $newPrice !== (int) $order->shipping_cost) {
            $oldShipping = (int) $order->shipping_cost;
            $oldTotal    = (float) $order->total;   // simpan sebelum update()
            $newShipping = (int) $newPrice;

            // Recalculate total: hapus ongkir lama, masukkan ongkir baru
            $newTotal = $oldTotal - $oldShipping + $newShipping;

            $order->update([
                'shipping_cost' => $newShipping,
                'total'         => max(0, $newTotal),
            ]);

            Log::info('Biteship price updated — shipping_cost & total adjusted', [
                'order'        => $order->order_number,
                'old_shipping' => $oldShipping,
                'new_shipping' => $newShipping,
                'old_total'    => $oldTotal,
                'new_total'    => max(0, $newTotal),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
