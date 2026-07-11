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

    $announcements = [
        'announcement_1' => $settings['announcement_1'] ?? null,
        'announcement_1_link' => $settings['announcement_1_link'] ?? null,
        'announcement_2' => $settings['announcement_2'] ?? null,
        'announcement_2_link' => $settings['announcement_2_link'] ?? null,
        'announcement_3' => $settings['announcement_3'] ?? null,
        'announcement_3_link' => $settings['announcement_3_link'] ?? null,
    ];

    echo json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'announcements' => $announcements,
        'all_settings' => $settings,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT);
}
