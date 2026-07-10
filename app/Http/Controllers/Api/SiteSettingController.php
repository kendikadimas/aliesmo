<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SiteSetting::all(['key', 'value', 'type', 'group'])
            ->mapWithKeys(fn($s) => [$s->key => match ($s->type) {
                'boolean' => (bool) $s->value,
                'json'    => json_decode($s->value, true),
                default   => $s->value,
            }]);

        if (!empty($settings['payment_qris_image'])) {
            $settings['payment_qris_image'] = asset('storage/' . $settings['payment_qris_image']);
        }

        if (!empty($settings['payment_banks']) && is_array($settings['payment_banks'])) {
            $settings['payment_banks'] = array_values($settings['payment_banks']);
        }

        return response()->json(['data' => $settings]);
    }

    public function group(string $group): JsonResponse
    {
        $settings = SiteSetting::where('group', $group)
            ->get(['key', 'value', 'type'])
            ->mapWithKeys(fn($s) => [$s->key => match ($s->type) {
                'boolean' => (bool) $s->value,
                'json'    => json_decode($s->value, true),
                default   => $s->value,
            }]);

        if (!empty($settings['payment_qris_image'])) {
            $settings['payment_qris_image'] = asset('storage/' . $settings['payment_qris_image']);
        }

        if (!empty($settings['payment_banks']) && is_array($settings['payment_banks'])) {
            $settings['payment_banks'] = array_values($settings['payment_banks']);
        }

        return response()->json(['data' => $settings]);
    }
}
