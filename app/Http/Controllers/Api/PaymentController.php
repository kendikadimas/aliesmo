<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function callback(Request $request)
    {
        Log::channel('payment')->info('Payment callback received', $request->all());

        try {
            $payment = $this->orderService->handlePaymentCallback($request->all());

            return response()->json([
                'status' => 'ok',
                'payment_status' => $payment->status->value,
            ]);
        } catch (\RuntimeException $e) {
            Log::channel('payment')->warning('Payment callback rejected', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
