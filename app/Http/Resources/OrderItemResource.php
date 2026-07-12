<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $productLoaded = $this->resource->relationLoaded('product');
        $thumbnail = $productLoaded ? $this->product?->thumbnail : null;
        return [
            'id'            => $this->id,
            'product_id'    => $this->product_id,
            'product_name'  => $this->product_name,
            'product_slug'  => $productLoaded ? $this->product?->slug : null,
            'product_image' => $thumbnail
                ? (str_starts_with($thumbnail, 'http') ? $thumbnail : asset('storage/' . $thumbnail))
                : null,
            'variant_id'    => $this->variant_id,
            'variant_name'  => $this->variant_name,
            'price'         => (float) $this->price,
            'quantity'      => $this->quantity,
            'subtotal'      => (float) $this->subtotal,
        ];
    }
}
