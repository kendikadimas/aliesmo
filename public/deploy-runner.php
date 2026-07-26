<?php
/**
 * Deploy runner — extract zip + clear bootstrap cache.
 * Tidak load Laravel supaya tidak timeout.
 * curl -X POST -H "X-Deploy-Token: ..." -F "deploy_zip=@files.zip" https://aliesmo.id/deploy-runner.php
 */

$token = '';
foreach (file(__DIR__ . '/../.env') as $line) {
    if (str_starts_with(trim($line), 'DEPLOY_TOKEN=')) {
        $token = trim(explode('=', $line, 2)[1], " \t\"'");
        break;
    }
}

$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';
if ($token === '' || $provided === '' || !hash_equals($token, $provided)) {
    http_response_code(403);
    die('403 Forbidden');
}

@set_time_limit(120);
$root    = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$results = [];

// Extract zip kalau ada
if (!empty($_FILES['deploy_zip']['tmp_name']) && is_uploaded_file($_FILES['deploy_zip']['tmp_name'])) {
    $zipPath = $root . '/deploy.zip';
    move_uploaded_file($_FILES['deploy_zip']['tmp_name'], $zipPath);
    $zip   = new ZipArchive();
    $ok    = $zip->open($zipPath) === true;
    $count = $ok ? $zip->numFiles : 0;
    if ($ok) { $zip->extractTo($root); $zip->close(); }
    @unlink($zipPath);
    $results['extract'] = $ok ? "ok ({$count} files)" : 'failed';
}

// Hapus bootstrap cache secara langsung — tidak perlu artisan
$cacheDir = $root . '/bootstrap/cache';
$cleared  = [];
foreach (glob($cacheDir . '/*.php') ?: [] as $f) {
    $cleared[] = basename($f);
    @unlink($f);
}
$results['cache_cleared'] = $cleared ?: 'nothing to clear';

header('Content-Type: application/json');
echo json_encode(['success' => true, 'ts' => date('Y-m-d H:i:s'), 'results' => $results]);
