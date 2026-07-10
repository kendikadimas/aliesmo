<?php
/**
 * Run missing site settings migration on server
 * 
 * Usage: php run-migration.php
 * 
 * This script will:
 * 1. Bootstrap Laravel
 * 2. Run the migration to add missing site_settings keys
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Running Missing Site Settings Migration ===\n\n";

use Illuminate\Support\Facades\DB;

$missingKeys = [
    [
        'key'   => 'contact_whatsapp',
        'label' => 'Nomor WhatsApp',
        'value' => '6285196811722',
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

$inserted = 0;
$skipped = 0;

foreach ($missingKeys as $setting) {
    $exists = DB::table('site_settings')->where('key', $setting['key'])->exists();
    if (!$exists) {
        DB::table('site_settings')->insert($setting);
        echo "[INSERTED] {$setting['key']}\n";
        $inserted++;
    } else {
        echo "[SKIPPED]  {$setting['key']} (already exists)\n";
        $skipped++;
    }
}

echo "\n=== Migration Complete ===\n";
echo "Inserted: {$inserted}\n";
echo "Skipped:  {$skipped}\n";

// Show all payment-related settings
echo "\n=== Current Payment Settings ===\n";
$paymentSettings = DB::table('site_settings')
    ->where('group', 'payment')
    ->orWhere('key', 'like', 'payment_%')
    ->get(['id', 'key', 'value', 'type']);

foreach ($paymentSettings as $s) {
    $val = strlen($s->value) > 80 ? substr($s->value, 0, 77) . '...' : $s->value;
    echo "ID:{$s->id} | {$s->key} ({$s->type}) => {$val}\n";
}
