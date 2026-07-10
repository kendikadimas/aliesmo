<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductVideoController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $videos = $product->videos()
            ->orderBy('sort_order')
            ->get(['id', 'title', 'youtube_url', 'sort_order']);

        return response()->json(['data' => $videos]);
    }
}
