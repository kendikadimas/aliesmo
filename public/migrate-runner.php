<?php
/**
 * Manual migrate runner — untuk dijalankan manual via browser.
 * Akses: https://aliesmo.id/migrate-runner.php?token=YOUR_DEPLOY_TOKEN
 *
 * HAPUS file ini setelah selesai digunakan.
 */

$token = '';
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        if (str_starts_with(trim($line), 'DEPLOY_TOKEN=')) {
            $token = trim(explode('=', $line, 2)[1]);
            break;
        }
    }
}

$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? $_GET['token'] ?? '';

if (empty($token) || empty($provided) || !hash_equals($token, $provided)) {
    http_response_code(403);
    die('403 Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$results = [];

$kernel->call('migrate', ['--force' => true]);
$results['migrate'] = trim($kernel->output());

$kernel->call('optimize:clear');
$results['optimize:clear'] = trim($kernel->output());

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
], JSON_PRETTY_PRINT);
