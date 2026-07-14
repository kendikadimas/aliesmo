<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
        // Verify webhook secret if configured
        $webhookSecret = config('services.biteship.webhook_secret');
        if ($webhookSecret) {
            $providedSecret = $request->header('Authorization') ?? $request->input('secret');
            if ($providedSecret !== $webhookSecret && $providedSecret !== 'Bearer ' . $webhookSecret) {
                Log::warning('Biteship webhook: invalid secret');
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
        }

        $event = $request->input('event');
        $orderId = $request->input('order_id');

        Log::info('Biteship webhook received', [
            'event'    => $event,
            'order_id' => $orderId,
            'payload'  => $request->all(),
        ]);

        // Cari order berdasarkan biteship_order_id
        $order = Order::where('biteship_order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Biteship webhook: order not found', ['order_id' => $orderId]);
            return response()->json(['success' => true, 'message' => 'Order not found, ignored']);
        }

        switch ($event) {
            case 'order.status':
                return $this->handleStatusUpdate($order, $request);

            case 'order.waybill_id':
                return $this->handleWaybillUpdate($order, $request);

            case 'order.price':
                return $this->handlePriceUpdate($order, $request);

            default:
                Log::info('Biteship webhook: unknown event', ['event' => $event]);
                return response()->json(['success' => true]);
        }
    }

    /**
     * Handle order status update from Biteship.
     *
     * Statuses: confirmed, picking, picked, dropping, dropped, delivered, returned, cancelled
     */
    private function handleStatusUpdate(Order $order, Request $request)
    {
        $status = $request->input('status');
        $trackingId = $request->input('courier_tracking_id');
        $waybillId = $request->input('courier_waybill_id');
        $driverName = $request->input('courier_driver_name');
        $driverPhone = $request->input('courier_driver_phone');

        $order->update([
            'biteship_status'      => $status,
            'biteship_tracking_id' => $trackingId ?? $order->biteship_tracking_id,
            'biteship_waybill_id'  => $waybillId ?? $order->biteship_waybill_id,
            'tracking_number'      => $waybillId ?? $order->tracking_number,
        ]);

        Log::info('Biteship status updated', [
            'order'      => $order->order_number,
            'status'     => $status,
            'waybill_id' => $waybillId,
            'driver'     => $driverName,
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

        if ($waybillId) {
            $order->update([
                'biteship_waybill_id'  => $waybillId,
                'biteship_tracking_id' => $trackingId ?? $order->biteship_tracking_id,
                'tracking_number'      => $waybillId,
            ]);

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
     */
    private function handlePriceUpdate(Order $order, Request $request)
    {
        $price = $request->input('price');

        Log::info('Biteship price update', [
            'order'        => $order->order_number,
            'new_price'    => $price,
            'old_shipping' => $order->shipping_cost,
        ]);

        // Optional: update shipping cost if different
        // For now, just log it — admin can manually adjust if needed
        // if ($price && (int) $price !== (int) $order->shipping_cost) {
        //     $order->update(['shipping_cost' => $price]);
        // }

        return response()->json(['success' => true]);
    }
}
