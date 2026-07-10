<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SiteSetting;

header('Content-Type: application/json');

$allSettings = SiteSetting::all(['key', 'value', 'type', 'group'])->mapWithKeys(function ($s) {
    return [$s->key => [
        'value' => $s->value,
        'type' => $s->type,
        'group' => $s->group,
        'is_empty' => empty($s->value),
    ]];
});

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'total_settings' => $allSettings->count(),
    'all_settings' => $allSettings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
