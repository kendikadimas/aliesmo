<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk order tracking publik (via token, tanpa autentikasi).
 * Tidak expose PII sensitif seperti email, phone, address lengkap.
 */
class PublicOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            'customer_name'    => $this->customer_name,
            // Email dimasking — hanya 2 karakter pertama + ***
            'customer_email'   => $this->maskEmail($this->customer_email),
            // Phone & address TIDAK ditampilkan untuk keamanan
            'subtotal'         => (float) $this->subtotal,
            'shipping_cost'    => (float) $this->shipping_cost,
            'coupon_code'      => $this->coupon_code,
            'coupon_discount'  => (float) ($this->coupon_discount ?? 0),
            'total'            => (float) $this->total,
            'status'           => $this->status->value,
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'       => $this->created_at,
        ];
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return $masked . '@' . $domain;
    }
}
