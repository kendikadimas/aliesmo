<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $filters = array_filter([
            'category'  => request('category'),
            'search'    => request('search'),
            'min_price' => request('min_price'),
            'max_price' => request('max_price'),
            'sort'      => request('sort'),
            'per_page'  => request('per_page'),
            'page'      => request('page'),
        ]);

        Log::info('[ProductController@index] Fetching products', [
            'ip'      => request()->ip(),
            'filters' => $filters,
        ]);

        try {
            $products = Product::with(['category', 'images', 'variants' => fn ($q) => $q->where('is_active', true)])
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

            Log::info('[ProductController@index] Success', [
                'total'        => $products->total(),
                'current_page' => $products->currentPage(),
            ]);

            return ProductResource::collection($products);
        } catch (\Throwable $e) {
            Log::error('[ProductController@index] Error fetching products', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'filters' => $filters,
            ]);
            throw $e;
        }
    }

    public function show(string $slug)
    {
        Log::info('[ProductController@show] Fetching product', [
            'ip'   => request()->ip(),
            'slug' => $slug,
        ]);

        try {
            $product = Product::with(['category', 'images', 'variants' => fn ($q) => $q->where('is_active', true), 'videos'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();

            Log::info('[ProductController@show] Success', [
                'product_id' => $product->id,
                'name'       => $product->name,
            ]);

            return new ProductResource($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('[ProductController@show] Product not found', [
                'slug' => $slug,
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[ProductController@show] Error fetching product', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'slug'    => $slug,
            ]);
            throw $e;
        }
    }
}
