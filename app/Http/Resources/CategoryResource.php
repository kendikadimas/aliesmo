<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'image_url'      => $this->image_url
                                    ? (str_starts_with($this->image_url, 'http')
                                        ? $this->image_url
                                        : asset('storage/' . $this->image_url))
                                    : null,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
