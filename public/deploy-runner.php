<?php
/**
 * Deploy runner — dipanggil oleh GitHub Actions setelah FTP deploy.
 * Jalankan: migrate --force dan optimize:clear
 *
 * JANGAN hapus file ini — dibutuhkan setiap deploy.
 * Token diset via GitHub Secret: DEPLOY_TOKEN
 */

$token = getenv('DEPLOY_TOKEN') ?: '';

// Baca token dari file .env jika tidak ada di environment
if (empty($token)) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile) as $line) {
            if (str_starts_with(trim($line), 'DEPLOY_TOKEN=')) {
                $token = trim(explode('=', $line, 2)[1]);
                break;
            }
        }
    }
}

$provided = $_GET['token'] ?? '';

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
]);
