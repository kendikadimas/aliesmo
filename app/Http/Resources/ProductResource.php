<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductImageResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'thumbnail' => $this->thumbnail
                ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : asset('storage/' . $this->thumbnail))
                : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at,
        ];
    }
}
