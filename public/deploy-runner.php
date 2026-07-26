<?php
/**
 * Deploy runner — GitHub Actions:
 *   curl -X POST -H "X-Deploy-Token: ..." -F "deploy_zip=@deploy.zip" https://aliesmo.id/deploy-runner.php
 * Or (legacy): put deploy.zip via FTP then POST without file.
 *
 * Token: DEPLOY_TOKEN di .env server (= GitHub secret)
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
@ini_set('memory_limit', '256M');

$root = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$results = [];

// Accept multipart upload (preferred — no FTP)
$zipPath = $root . '/deploy.zip';
if (!empty($_FILES['deploy_zip']['tmp_name']) && is_uploaded_file($_FILES['deploy_zip']['tmp_name'])) {
    if (!move_uploaded_file($_FILES['deploy_zip']['tmp_name'], $zipPath)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to store uploaded zip']);
        exit;
    }
    $results['upload'] = 'ok (multipart)';
}

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

    $count = $zip->numFiles;
    $ok = $zip->extractTo($root);
    $zip->close();
    @unlink($zipPath);

    if (!$ok) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Extract failed']);
        exit;
    }
    $results['extract'] = "ok ({$count} entries)";
} else {
    $results['extract'] = 'skipped (no deploy.zip)';
}

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
