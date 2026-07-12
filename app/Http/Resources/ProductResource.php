<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductImageResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\ProductVideoResource;

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
            'price'          => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
            'stock'  => $this->stock,
            'weight' => (int) ($this->weight ?? 300), // gram, default 300g
            'is_active' => $this->is_active,
            'thumbnail' => $this->thumbnail
                ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : asset('storage/' . $this->thumbnail))
                : null,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'videos'   => ProductVideoResource::collection($this->whenLoaded('videos')),
            'avg_rating'    => $this->avg_rating ? round((float) $this->avg_rating, 1) : null,
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'created_at' => $this->created_at,
        ];
    }
}
