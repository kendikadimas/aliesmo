<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->when(request('category'), fn($q, $slug) => $q->whereHas('category', fn($q) => $q->where('slug', $slug)))
            ->when(request('search'), fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(request('sort'), function ($q, $sort) {
                match ($sort) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'newest' => $q->orderByDesc('created_at'),
                    default => $q->orderByDesc('created_at'),
                };
            }, fn($q) => $q->orderByDesc('created_at'))
            ->paginate(12);

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Product::with(['category', 'images'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        return new ProductResource($product);
    }
}
