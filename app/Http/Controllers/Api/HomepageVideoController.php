<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomepageVideo;
use Illuminate\Http\JsonResponse;

class HomepageVideoController extends Controller
{
    public function index(): JsonResponse
    {
        $videos = HomepageVideo::active()
            ->get(['id', 'title', 'youtube_url', 'description', 'order']);

        return response()->json(['data' => $videos]);
    }
}
