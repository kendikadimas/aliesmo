<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\SiteSetting;

header('Content-Type: application/json');

try {
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
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT);
}
