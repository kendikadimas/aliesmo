<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createFromCart(
                $request->items,
                $request->only(['customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'shipping_cost'])
            );

            $order->load('items');

            return response()->json([
                'order' => new OrderResource($order),
                'payment' => null,
                'whatsapp_number' => config('services.whatsapp.number'),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function status(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'payment'])
            ->firstOrFail();

        return response()->json([
            'data' => new OrderResource($order),
            'whatsapp_number' => config('services.whatsapp.number'),
        ]);
    }

    public function myOrders()
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return OrderResource::collection($orders);
    }
}
