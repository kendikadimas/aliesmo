<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function index(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $reviews = Review::where('product_id', $product->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(10);

        $avgRating = Review::where('product_id', $product->id)
            ->where('is_approved', true)
            ->avg('rating');

        return response()->json([
            'data'       => $reviews,
            'avg_rating' => round($avgRating, 1),
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $user    = $request->user();

        $request->validate([
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment'  => ['nullable', 'string', 'max:1000'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        // Cek apakah user sudah pernah review produk ini
        $existing = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Kamu sudah pernah mereview produk ini.'], 422);
        }

        // Wajibkan order_id — pastikan order milik user ini, sudah delivered,
        // dan produk benar-benar ada di dalam order tersebut (cegah review tanpa beli)
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->where('status', \App\Enums\OrderStatus::Completed)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Kamu hanya bisa mereview produk yang sudah kamu beli dan diterima.'], 422);
        }

        $productInOrder = $order->items()->where('product_id', $product->id)->exists();
        if (!$productInOrder) {
            return response()->json(['message' => 'Produk ini tidak ada dalam pesanan tersebut.'], 422);
        }

        $review = Review::create([
            'product_id'  => $product->id,
            'user_id'     => $user->id,
            'order_id'    => $request->order_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'is_approved' => false, // Butuh approval admin
        ]);

        return response()->json([
            'message' => 'Terima kasih! Review kamu sedang menunggu persetujuan.',
            'review'  => $review,
        ], 201);
    }
}
