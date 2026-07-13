<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'thumbnail'    => $this->thumbnail
                ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : asset('storage/' . $this->thumbnail))
                : null,
            'excerpt'      => $this->excerpt,
            'author'       => $this->author,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            // konten hanya diexpose di detail, bukan di listing
            'content'      => $this->when($request->routeIs('api.articles.show'), $this->content),
        ];
    }
}
