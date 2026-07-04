<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products as products_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->get();

        return CategoryResource::collection($categories);
    }
}
