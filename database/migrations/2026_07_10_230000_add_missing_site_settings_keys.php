<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $missingKeys = [
            [
                'key'   => 'contact_whatsapp',
                'label' => 'Nomor WhatsApp',
                'value' => '628138883345',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'social_instagram',
                'label' => 'Instagram',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'social_facebook',
                'label' => 'Facebook',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'social_tiktok',
                'label' => 'TikTok',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'social_youtube',
                'label' => 'YouTube',
                'value' => '',
                'type'  => 'text',
                'group' => 'general',
            ],
        ];

        foreach ($missingKeys as $setting) {
            $exists = DB::table('site_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('site_settings')->insert($setting);
                echo "Inserted: {$setting['key']}\n";
            } else {
                echo "Skipped (already exists): {$setting['key']}\n";
            }
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'contact_whatsapp',
            'social_instagram',
            'social_facebook',
            'social_tiktok',
            'social_youtube',
        ])->delete();
    }
};
