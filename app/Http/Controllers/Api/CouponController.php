<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate([
            'code'        => ['required', 'string'],
            'order_total' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['message' => 'Kode kupon tidak valid atau sudah kadaluarsa.'], 422);
        }

        if ($request->order_total < $coupon->min_order) {
            return response()->json([
                'message' => 'Minimum order untuk kupon ini adalah Rp' . number_format($coupon->min_order, 0, ',', '.'),
            ], 422);
        }

        $discount = $coupon->calculateDiscount((float) $request->order_total);

        return response()->json([
            'coupon' => [
                'code'     => $coupon->code,
                'type'     => $coupon->type,
                'value'    => (float) $coupon->value,
                'discount' => $discount,
            ],
        ]);
    }
}
