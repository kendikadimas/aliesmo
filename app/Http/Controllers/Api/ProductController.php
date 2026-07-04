<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->when(request('category'), fn($q, $slug) => $q->whereHas('category', fn($q) => $q->where('slug', $slug)))
            ->when(request('search'), fn($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->when(request('min_price'), fn($q, $price) => $q->where('price', '>=', $price))
            ->when(request('max_price'), fn($q, $price) => $q->where('price', '<=', $price))
            ->when(request('sort'), function ($q, $sort) {
                return match ($sort) {
                    'price_asc'  => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'newest'     => $q->orderByDesc('created_at'),
                    default      => $q->orderByDesc('created_at'),
                };
            }, fn($q) => $q->orderByDesc('created_at'))
            ->paginate(min((int) request('per_page', 12), 100)); // Max 100 items per page (prevent DoS)

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Product::with(['category', 'images'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        return new ProductResource($product);
    }
}
