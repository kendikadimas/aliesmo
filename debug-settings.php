<?php
/**
 * Debug script: dump all site_settings rows
 * Usage: php debug-settings.php
 */
require 'D:/laragon/www/aliesmo/vendor/autoload.php';
$app = require_once 'D:/laragon/www/aliesmo/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALL SITE SETTINGS ===\n\n";

$settings = DB::table('site_settings')->orderBy('group')->orderBy('key')->get();

$currentGroup = null;
foreach ($settings as $s) {
    if ($s->group !== $currentGroup) {
        $currentGroup = $s->group;
        echo "\n--- " . strtoupper($currentGroup ?? 'UNGROUPED') . " ---\n";
    }
    $val = $s->value;
    if (strlen($val) > 80) {
        $val = substr($val, 0, 77) . '...';
    }
    echo sprintf("  ID:%-3d %-35s %-8s %s\n", $s->id, $s->key, $s->type, $val);
}

echo "\n=== TOTAL: " . $settings->count() . " rows ===\n";

// Check which keys the save() expects but don't exist
$expectedKeys = [
    'announcement_1', 'announcement_1_link',
    'announcement_2', 'announcement_2_link',
    'announcement_3', 'announcement_3_link',
    'stat_kemeja_terjual', 'stat_kota', 'stat_kualitas', 'stat_garansi',
    'contact_email', 'contact_phone', 'contact_address', 'contact_whatsapp',
    'social_instagram', 'social_facebook', 'social_tiktok', 'social_youtube',
    'payment_qris_name', 'payment_banks', 'payment_qris_image', 'payment_cod_enabled',
];

$existingKeys = $settings->pluck('key')->toArray();
$missing = array_diff($expectedKeys, $existingKeys);

if (!empty($missing)) {
    echo "\n!!! MISSING KEYS (save() expects these but they don't exist in DB):\n";
    foreach ($missing as $k) {
        echo "  - $k\n";
    }
    echo "\nThese keys will silently fail on update()!\n";
} else {
    echo "\nAll expected keys exist in DB.\n";
}
