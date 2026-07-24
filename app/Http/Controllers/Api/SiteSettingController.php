<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    /**
     * Daftar key yang boleh diekspos ke publik.
     * Key di luar whitelist ini tidak akan dikembalikan ke frontend.
     */
    private const PUBLIC_KEYS = [
        'store_name',
        'store_tagline',
        'store_email',
        'store_phone',
        'store_address',
        'store_logo',
        'store_favicon',
        // announcement bar (3 slot + link) — dipakai App.vue
        'announcement_1',
        'announcement_1_link',
        'announcement_2',
        'announcement_2_link',
        'announcement_3',
        'announcement_3_link',
        'announcement_text',
        'announcement_enabled',
        'announcement_color',
        'payment_methods_enabled',
        'payment_qris_enabled',
        'payment_qris_image',
        'payment_qris_name',
        'payment_banks',
        'payment_cod_enabled',
        'whatsapp_number',
        'whatsapp_enabled',
        'contact_email',
        'contact_phone',
        'contact_address',
        'contact_whatsapp',
        'social_instagram',
        'social_facebook',
        'social_tiktok',
        'social_youtube',
        'social_shopee',
        'social_tiktokshop',
        'stat_kemeja_terjual',
        'stat_kota',
        'stat_kualitas',
        'stat_garansi',
        'about_title',
        'about_desc',
        'about_description',
        'advantage_1_title',
        'advantage_1_desc',
        'advantage_2_title',
        'advantage_2_desc',
        'advantage_3_title',
        'advantage_3_desc',
        'advantage_4_title',
        'advantage_4_desc',
        'homepage_video_title',
        'homepage_video_subtitle',
        'shipping_free_threshold',
        'maintenance_mode',
        'promo_banner_enabled',
        'promo_banner_text',
        'promo_banner_color',
    ];

    public function index(): JsonResponse
    {
        $settings = SiteSetting::whereIn('key', self::PUBLIC_KEYS)
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

    public function group(string $group): JsonResponse
    {
        // Whitelist group — cegah leak key internal via ?group=anything
        $allowedGroups = ['general', 'payment', 'shipping', 'social', 'announcement', 'promo'];
        if (!in_array($group, $allowedGroups, true)) {
            return response()->json(['message' => 'Group tidak valid.'], 404);
        }

        $settings = SiteSetting::where('group', $group)
            ->whereIn('key', self::PUBLIC_KEYS)
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
