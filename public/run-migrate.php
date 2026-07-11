<?php

/**
 * One-time migration runner. DELETE this file after use.
 * Access: https://aliesmo.id/run-migrate.php?token=YOUR_SECRET_TOKEN
 */

define('LARAVEL_START', microtime(true));

$token = $_GET['token'] ?? '';
$secret = 'aliesmo-migrate-2026';

if (!hash_equals($secret, $token)) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== Laravel Migrate ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $exitCode = $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();
    echo "\nExit code: $exitCode\n";
    echo "\nDone. DELETE this file now!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
