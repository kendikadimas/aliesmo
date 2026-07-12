<?php
/**
 * Deploy runner — dipanggil oleh GitHub Actions setelah FTP deploy.
 * Jalankan: migrate --force dan optimize:clear
 *
 * JANGAN hapus file ini — dibutuhkan setiap deploy.
 * Token diset via GitHub Secret: DEPLOY_TOKEN
 */

// Baca token dari file .env
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

// Terima token hanya dari header — jangan dari query string (akan masuk server log)
$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';

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

$kernel->call('storage:link');
$results['storage:link'] = trim($kernel->output());

$kernel->call('optimize:clear');
$results['optimize:clear'] = trim($kernel->output());

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
]);
