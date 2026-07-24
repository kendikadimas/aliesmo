<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class TestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Testimonial::active()
            ->get(['id', 'name', 'role', 'content', 'avatar_url', 'rating', 'order'])
            ->map(function (Testimonial $t) {
                if ($t->avatar_url && ! str_starts_with($t->avatar_url, 'http')) {
                    $t->avatar_url = asset('storage/' . $t->avatar_url);
                }

                return $t;
            });

        return response()->json(['data' => $items]);
    }
}
