<?php
/**
 * Deploy runner — extract zip. Tidak load Laravel (hindari timeout).
 * curl -X POST -H "X-Deploy-Token: ..." -F "deploy_zip=@files.zip" https://aliesmo.id/deploy-runner.php
 */

$token = '';
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (file($envPath) as $line) {
        if (str_starts_with(trim($line), 'DEPLOY_TOKEN=')) {
            $token = trim(explode('=', $line, 2)[1], " \t\"'");
            break;
        }
    }
}

$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? ($_GET['token'] ?? '');
if ($token === '' || $provided === '' || !hash_equals($token, $provided)) {
    http_response_code(403);
    die('403 Forbidden');
}

@set_time_limit(180);
$root    = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$results = [];

// Extract zip kalau ada
if (!empty($_FILES['deploy_zip']['tmp_name']) && is_uploaded_file($_FILES['deploy_zip']['tmp_name'])) {
    $zipPath = $root . '/deploy.zip';
    move_uploaded_file($_FILES['deploy_zip']['tmp_name'], $zipPath);
    $zip   = new ZipArchive();
    $ok    = $zip->open($zipPath) === true;
    $count = $ok ? $zip->numFiles : 0;
    if ($ok) {
        $zip->extractTo($root);
        $zip->close();
    }
    @unlink($zipPath);
    $results['extract'] = $ok ? "ok ({$count} files)" : 'failed';
} else {
    $results['extract'] = 'no zip';
}

// Hanya buang route/config cache — JANGAN hapus packages.php / services.php
$cacheDir = $root . '/bootstrap/cache';
$cleared  = [];
foreach (['routes-v7.php', 'config.php', 'events.php', 'routes.php'] as $f) {
    $p = $cacheDir . '/' . $f;
    if (is_file($p)) { @unlink($p); $cleared[] = $f; }
}
$results['cache_cleared'] = $cleared ?: 'nothing to clear';

// Buang compiled blade views supaya template baru langsung aktif
$viewsDir = $root . '/storage/framework/views';
$viewsCleared = 0;
if (is_dir($viewsDir)) {
    foreach (glob($viewsDir . '/*.php') ?: [] as $f) {
        @unlink($f);
        $viewsCleared++;
    }
}
$results['views_cleared'] = $viewsCleared;

header('Content-Type: application/json');
echo json_encode(['success' => true, 'ts' => date('Y-m-d H:i:s'), 'results' => $results]);
