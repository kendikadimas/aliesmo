<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key'   => 'social_shopee',
                'label' => 'Shopee',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'social_tiktokshop',
                'label' => 'TikTok Shop',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('site_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('site_settings')->insert($setting);
            }
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'social_shopee',
            'social_tiktokshop',
        ])->delete();
    }
};
