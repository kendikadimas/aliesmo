<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\PaymentResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            // lookup_token hanya expose saat order baru dibuat — cegah token bocor di list/detail endpoint
            'lookup_token'     => $this->when($this->wasRecentlyCreated, $this->lookup_token),
            'customer_name'    => $this->customer_name,
            'customer_email'   => $this->customer_email,
            'customer_phone'   => $this->customer_phone,
            'shipping_address' => $this->shipping_address,
            'subtotal'         => (float) $this->subtotal,
            'shipping_cost'    => (float) $this->shipping_cost,
            'coupon_code'      => $this->coupon_code,
            'coupon_discount'  => (float) ($this->coupon_discount ?? 0),
            'total'            => (float) $this->total,
            'status'           => $this->status->value,
            'payment_method'   => $this->payment_method,
            'tracking_number'  => $this->tracking_number,
            'tracking_url'     => $this->tracking_url,
            'courier'          => $this->courier,
            'biteship_status'  => $this->biteship_status,
            'paid_at'          => $this->paid_at,
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'payment'          => $this->whenLoaded('payment', fn() => new PaymentResource($this->payment)),
            'created_at'       => $this->created_at,
        ];
    }
}
