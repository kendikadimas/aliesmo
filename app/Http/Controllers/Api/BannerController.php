<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::active()
            ->get(['id', 'title', 'subtitle', 'badge_text', 'image_url', 'youtube_url', 'button_text', 'button_link', 'order'])
            ->map(function (Banner $banner) {
                if ($banner->image_url && ! str_starts_with($banner->image_url, 'http')) {
                    $banner->image_url = asset('storage/' . $banner->image_url);
                }

                return $banner;
            });

        return response()->json(['data' => $banners]);
    }
}
