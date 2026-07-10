<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SiteSetting;

header('Content-Type: application/json');

$settings = SiteSetting::all(['key', 'value', 'type', 'group'])->mapWithKeys(function ($s) {
    return [$s->key => [
        'value' => $s->value,
        'type' => $s->type,
        'group' => $s->group,
        'is_empty' => empty($s->value),
    ]];
});

$paymentKeys = [
    'payment_banks',
    'payment_qris_image',
    'payment_qris_name',
    'payment_cod_enabled',
];

$paymentSettings = [];
foreach ($paymentKeys as $key) {
    $paymentSettings[$key] = $settings[$key] ?? ['value' => null, 'is_empty' => true, 'not_found' => true];
}

$qrisValue = $paymentSettings['payment_qris_image']['value'] ?? null;
$qrisFileExists = false;
$qrisFullPath = null;

if ($qrisValue) {
    $qrisFullPath = storage_path('app/public/' . $qrisValue);
    $qrisFileExists = file_exists($qrisFullPath);
}

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'payment_settings' => $paymentSettings,
    'qris_debug' => [
        'db_value' => $qrisValue,
        'full_path' => $qrisFullPath,
        'file_exists' => $qrisFileExists,
        'asset_url' => $qrisValue ? asset('storage/' . $qrisValue) : null,
    ],
    'all_settings_count' => $settings->count(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
