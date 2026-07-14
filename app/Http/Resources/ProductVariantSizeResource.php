<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantSizeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'stock'      => $this->stock,
            'sku'        => $this->sku,
            'weight'     => $this->weight,
            'is_active'  => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
