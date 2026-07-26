<?php
/**
 * Deploy runner — dipanggil oleh GitHub Actions setelah upload deploy.zip.
 * 1) Extract deploy.zip (jika ada) ke root Laravel
 * 2) migrate --force, storage:link, optimize:clear
 *
 * JANGAN hapus file ini — dibutuhkan setiap deploy.
 * Token: GitHub Secret DEPLOY_TOKEN (sama di .env server)
 */

$token = '';
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        if (str_starts_with(trim($line), 'DEPLOY_TOKEN=')) {
            $token = trim(explode('=', $line, 2)[1], " \t\"'");
            break;
        }
    }
}

$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';

if ($token === '' || $provided === '' || !hash_equals($token, $provided)) {
    http_response_code(403);
    die('403 Forbidden');
}

@set_time_limit(300);

$root = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$results = [];

// ── extract deploy.zip (1 file FTP, hindari scan penuh) ──
$zipPath = $root . '/deploy.zip';
if (is_file($zipPath)) {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ZipArchive not available']);
        exit;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Cannot open deploy.zip']);
        exit;
    }

    $extracted = $zip->extractTo($root);
    $count = $zip->numFiles;
    $zip->close();

    if (!$extracted) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Extract failed']);
        exit;
    }

    @unlink($zipPath);
    $results['extract'] = "ok ({$count} entries)";
} else {
    $results['extract'] = 'skipped (no deploy.zip)';
}

// ── artisan ──
define('LARAVEL_START', microtime(true));
require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

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
