<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\BiteshipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestBiteshipOrder extends Command
{
    protected $signature = 'biteship:test 
        {--order= : Order number to test with} 
        {--webhook : Simulate webhook callback}
        {--create : Create a Biteship order for the given order}';

    protected $description = 'Test Biteship integration locally';

    public function handle(): int
    {
        $orderNumber = $this->option('order');

        if ($this->option('webhook')) {
            return $this->simulateWebhook($orderNumber);
        }

        if ($this->option('create')) {
            return $this->createBiteshipOrder($orderNumber);
        }

        $this->info('=== Biteship Integration Test ===');
        $this->newLine();

        // 1. Check API key
        $apiKey = config('services.biteship.api_key');
        if (empty($apiKey)) {
            $this->error('BITESHIP_API_KEY not set in .env');
            return self::FAILURE;
        }
        $this->info('✓ API Key: ' . substr($apiKey, 0, 8) . '...');

        // 2. Check config
        $this->info('✓ Origin Postal: ' . config('services.biteship.origin_postal'));
        $this->info('✓ Origin Area ID: ' . config('services.biteship.origin_area_id'));
        $this->info('✓ Origin Phone: ' . config('services.biteship.origin_phone'));
        $this->newLine();

        // 3. Test rates API (quick check)
        $this->info('Testing Rates API...');
        try {
            $biteship = app(BiteshipService::class);
            $costs = $biteship->getAllShippingCosts('10110', 500);
            $this->info('✓ Rates API: ' . count($costs) . ' options returned');
            foreach (array_slice($costs, 0, 3) as $c) {
                $this->line("  - {$c['courier']} {$c['service']}: Rp" . number_format($c['cost']));
            }
        } catch (\Exception $e) {
            $this->error('✗ Rates API failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 4. List recent orders with Biteship data
        $this->info('Recent orders with Biteship data:');
        $orders = Order::whereNotNull('biteship_order_id')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['order_number', 'biteship_order_id', 'biteship_waybill_id', 'biteship_status', 'courier']);

        if ($orders->isEmpty()) {
            $this->line('  (none yet)');
        } else {
            foreach ($orders as $o) {
                $this->line("  #{$o->order_number} | Biteship: {$o->biteship_order_id} | Resi: {$o->biteship_waybill_id} | Status: {$o->biteship_status} | Kurir: {$o->courier}");
            }
        }

        return self::SUCCESS;
    }

    private function createBiteshipOrder(?string $orderNumber): int
    {
        if (!$orderNumber) {
            $this->error('Specify --order=ORDER_NUMBER');
            return self::FAILURE;
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            $this->error("Order {$orderNumber} not found");
            return self::FAILURE;
        }

        if ($order->biteship_order_id) {
            $this->warn("Order already has Biteship ID: {$order->biteship_order_id}");
            $this->line('Use --webhook to simulate status update instead');
            return self::SUCCESS;
        }

        $this->info("Creating Biteship order for #{$orderNumber}...");

        try {
            $biteship = app(BiteshipService::class);

            $order->loadMissing(['items.product', 'items.variant', 'items.size']);

            $itemValue = 0;
            $items = $order->items->map(function ($item) use (&$itemValue) {
                $name = $item->product_name;
                if ($item->variant_name) $name .= " ({$item->variant_name})";
                if ($item->size_name)    $name .= " - {$item->size_name}";

                // Prioritas weight: size → variant → product → default 300g
                $weight = $item->size?->weight
                    ?? $item->variant?->weight
                    ?? $item->product?->weight
                    ?? 300;

                $itemValue += (int) $item->price;

                return [
                    'name'     => $name,
                    'value'    => (int) $item->price,
                    'quantity' => $item->quantity,
                    'weight'   => max(1, (int) $weight),
                ];
            })->toArray();

            // Map courier canonical name → Biteship code + type
            // POS Indonesia harus type 'sps', bukan 'reg'
            $courierNameMap = [
                'JNE'           => ['company' => 'jne', 'type' => BiteshipService::COURIER_SERVICE_MAP['jne']],
                'JNT Express'   => ['company' => 'jnt', 'type' => BiteshipService::COURIER_SERVICE_MAP['jnt']],
                'J&T Express'   => ['company' => 'jnt', 'type' => BiteshipService::COURIER_SERVICE_MAP['jnt']],
                'POS Indonesia' => ['company' => 'pos', 'type' => BiteshipService::COURIER_SERVICE_MAP['pos']],
                'Pos Indonesia' => ['company' => 'pos', 'type' => BiteshipService::COURIER_SERVICE_MAP['pos']],
            ];
            $courierEntry = $courierNameMap[$order->courier]
                ?? ['company' => strtolower(explode(' ', $order->courier)[0]), 'type' => $order->courier_service ?? 'reg'];

            $isCod     = ($order->payment_method === 'cod');
            $codAmount = $isCod ? (int) $order->total : null;

            $result = $biteship->createOrder([
                'order_number'     => $order->order_number,
                'customer_name'    => $order->customer_name,
                'customer_phone'   => $order->customer_phone,
                'customer_email'   => $order->customer_email,
                'shipping_address' => $order->shipping_address,
                'shipping_area_id' => $order->shipping_area_id,
                'destination_lat'  => $order->destination_lat,
                'destination_lng'  => $order->destination_lng,
                'courier_company'  => $courierEntry['company'],
                'courier_type'     => $courierEntry['type'],
                'item_value'       => $itemValue,
                'items'            => $items,
                'is_cod'           => $isCod,
                'cod_amount'       => $codAmount,
            ]);

            $order->update([
                'biteship_order_id'    => $result['biteship_order_id'],
                'biteship_tracking_id' => $result['biteship_tracking_id'],
                'biteship_waybill_id'  => $result['biteship_waybill_id'],
                'biteship_status'      => $result['biteship_status'],
                'tracking_number'      => $result['biteship_waybill_id'] ?? $order->tracking_number,
                'tracking_url'         => $result['tracking_url'] ?? $order->tracking_url,
            ]);

            $this->info('✓ Biteship order created!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Biteship ID', $result['biteship_order_id']],
                    ['Tracking ID', $result['biteship_tracking_id'] ?? '-'],
                    ['Waybill ID', $result['biteship_waybill_id'] ?? '-'],
                    ['Status', $result['biteship_status']],
                    ['Price', 'Rp' . number_format($result['price'])],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function simulateWebhook(?string $orderNumber): int
    {
        if (!$orderNumber) {
            $this->error('Specify --order=ORDER_NUMBER');
            return self::FAILURE;
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            $this->error("Order {$orderNumber} not found");
            return self::FAILURE;
        }

        if (!$order->biteship_order_id) {
            $this->error("Order {$orderNumber} has no Biteship order ID. Run with --create first.");
            return self::FAILURE;
        }

        $this->info("Simulating webhook for #{$orderNumber} (Biteship: {$order->biteship_order_id})");
        $this->newLine();

        // Simulate order.status webhook
        // Status resmi Biteship (sesuai docs): confirmed, scheduled, allocated, picking_up,
        // picked, cancelled, on_hold, in_transit, dropping_off, return_in_transit,
        // returned, rejected, disposed, courier_not_found, delivered
        $status = $this->choice('Select status to simulate', [
            'confirmed'         => 'Confirmed (order accepted by Biteship)',
            'allocated'         => 'Allocated (courier assigned)',
            'picking_up'        => 'Picking Up (courier on the way)',
            'picked'            => 'Picked (courier has the package)',
            'in_transit'        => 'In Transit (package in delivery)',
            'dropping_off'      => 'Dropping Off (courier at destination)',
            'delivered'         => 'Delivered → syncs Order to Completed',
            'return_in_transit' => 'Return In Transit → syncs Order to Cancelled',
            'returned'          => 'Returned → syncs Order to Cancelled',
            'cancelled'         => 'Cancelled → syncs Order to Cancelled',
            'rejected'          => 'Rejected → syncs Order to Cancelled',
            'disposed'          => 'Disposed → syncs Order to Cancelled',
            'courier_not_found' => 'Courier Not Found',
            'on_hold'           => 'On Hold',
        ], 'in_transit');

        $payload = [
            'event'                => 'order.status',
            'order_id'             => $order->biteship_order_id,
            'courier_tracking_id'  => $order->biteship_tracking_id ?? 'TEST-TRACKING-123',
            'courier_waybill_id'   => $order->biteship_waybill_id ?? 'TEST-WAYBILL-456',
            'courier_company'      => strtolower(explode(' ', $order->courier)[0] ?? 'jne'),
            'courier_type'         => 'reg',
            'courier_driver_name'  => 'Test Driver',
            'courier_driver_phone' => '08123456789',
            'status'               => $status,
        ];

        $this->info('Sending webhook payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        $this->newLine();

        // Call local webhook endpoint
        $response = Http::timeout(10)
            ->post('http://localhost:8000/api/v1/webhooks/biteship', $payload);

        if ($response->successful()) {
            $this->info('✓ Webhook processed successfully');
            $this->line('Response: ' . $response->body());

            // Refresh order to see updated status
            $order->refresh();
            $this->newLine();
            $this->info('Updated order status:');
            $this->line("  Biteship Status: {$order->biteship_status}");
            $this->line("  Waybill ID: {$order->biteship_waybill_id}");
            $this->line("  Tracking Number: {$order->tracking_number}");
        } else {
            $this->error('✗ Webhook failed: HTTP ' . $response->status());
            $this->line($response->body());
        }

        return self::SUCCESS;
    }
}
