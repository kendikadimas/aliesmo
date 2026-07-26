<?php
/**
 * Hapus bootstrap cache files — dipanggil dari GitHub Actions setelah deploy.
 * Tidak load Laravel, langsung hapus file cache.
 * ponytail: no auth, IP-based restriction juga tidak perlu karena hanya hapus cache
 */
$root = realpath(__DIR__ . '/../bootstrap/cache');
$files = ['routes-v7.php', 'config.php', 'packages.php', 'services.php', 'events.php'];
$results = [];
foreach ($files as $f) {
    $path = $root . '/' . $f;
    if (file_exists($path)) {
        $results[$f] = unlink($path) ? 'deleted' : 'failed';
    } else {
        $results[$f] = 'not found';
    }
}
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'files' => $results]);
