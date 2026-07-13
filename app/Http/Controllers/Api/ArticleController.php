<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function index()
    {
        Log::info('[ArticleController@index] Fetching articles', [
            'ip' => request()->ip(),
        ]);

        try {
            $articles = Article::published()
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->paginate(min((int) request('per_page', 12), 50));

            return ArticleResource::collection($articles);
        } catch (\Throwable $e) {
            Log::error('[ArticleController@index] Error', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function show(string $slug)
    {
        Log::info('[ArticleController@show] Fetching article', [
            'ip'   => request()->ip(),
            'slug' => $slug,
        ]);

        try {
            $article = Article::published()
                ->where('slug', $slug)
                ->firstOrFail();

            return new ArticleResource($article);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('[ArticleController@show] Article not found', ['slug' => $slug]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[ArticleController@show] Error', [
                'message' => $e->getMessage(),
                'slug'    => $slug,
            ]);
            throw $e;
        }
    }
}
