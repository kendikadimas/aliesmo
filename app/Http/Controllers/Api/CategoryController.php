<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        Log::info('[CategoryController@index] Fetching categories', [
            'ip' => request()->ip(),
        ]);

        try {
            $categories = Category::withCount(['products as products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->having('products_count', '>', 0)
                ->get();

            Log::info('[CategoryController@index] Success', [
                'count' => $categories->count(),
            ]);

            return CategoryResource::collection($categories);
        } catch (\Throwable $e) {
            Log::error('[CategoryController@index] Error fetching categories', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
