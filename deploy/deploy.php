<?php
/**
 * Deploy unzipper script — diupload sementara ke public_html/ saat deploy.
 * Script ini akan dihapus otomatis setelah proses selesai (action=cleanup).
 *
 * PENTING: Jangan tinggalkan file ini di server setelah deploy selesai.
 */

// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ─── Deteksi home directory secara otomatis ──────────────────────────────────
$publicHtmlDir = __DIR__; // /home/username/public_html
$homeDir       = dirname($publicHtmlDir); // /home/username
$appDir        = $homeDir . '/aliesmo';
$deployDir     = $homeDir . '/aliesmo_deploy';

// ─── Autentikasi ────────────────────────────────────────────────────────────
$secret = $_POST['secret'] ?? '';

// Baca dari file .deploy_secret di home directory
$secretFile    = $homeDir . '/.deploy_secret';
$expectedSecret = '';

if (file_exists($secretFile)) {
    $expectedSecret = trim(file_get_contents($secretFile));
} else {
    http_response_code(500);
    die(json_encode([
        'error'       => '.deploy_secret file not found',
        'looked_in'   => $secretFile,
        'home_dir'    => $homeDir,
        'public_html' => $publicHtmlDir,
    ]));
}

if (empty($expectedSecret) || !hash_equals($expectedSecret, $secret)) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden — secret mismatch']));
}

$action = $_POST['action'] ?? '';

// ─── Action: unzip ──────────────────────────────────────────────────────────
if ($action === 'unzip') {
    $results = [];

    // Cek ZipArchive extension tersedia
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die(json_encode(['error' => 'ZipArchive extension not available on this server']));
    }

    // 1. Unzip app ke ~/aliesmo/
    $appZip = $deployDir . '/deploy_app.zip';
    if (file_exists($appZip)) {
        if (!is_dir($appDir)) {
            mkdir($appDir, 0755, true);
        }
        $zip = new ZipArchive();
        $opened = $zip->open($appZip);
        if ($opened === true) {
            $zip->extractTo($appDir);
            $zip->close();
            $results['app'] = 'extracted to ' . $appDir;
        } else {
            http_response_code(500);
            die(json_encode(['error' => 'Failed to open deploy_app.zip', 'code' => $opened]));
        }
    } else {
        $results['app'] = 'deploy_app.zip not found at ' . $appZip;
    }

    // 2. Unzip public ke ~/public_html/
    $publicZip = $deployDir . '/deploy_public.zip';
    if (file_exists($publicZip)) {
        $zip = new ZipArchive();
        $opened = $zip->open($publicZip);
        if ($opened === true) {
            $zip->extractTo($publicHtmlDir);
            $zip->close();
            $results['public'] = 'extracted to ' . $publicHtmlDir;
        } else {
            http_response_code(500);
            die(json_encode(['error' => 'Failed to open deploy_public.zip', 'code' => $opened]));
        }
    } else {
        $results['public'] = 'deploy_public.zip not found at ' . $publicZip;
    }

    // 3. Set permission storage & bootstrap/cache
    $storagePath = $appDir . '/storage';
    $cachePath   = $appDir . '/bootstrap/cache';
    if (is_dir($storagePath)) {
        chmod_recursive($storagePath, 0775);
        $results['storage_chmod'] = 'done';
    }
    if (is_dir($cachePath)) {
        chmod_recursive($cachePath, 0775);
        $results['cache_chmod'] = 'done';
    }

    echo json_encode(['status' => 'ok', 'results' => $results]);
    exit;
}

// ─── Action: cleanup ────────────────────────────────────────────────────────
if ($action === 'cleanup') {
    $cleaned = [];

    $appZip    = $deployDir . '/deploy_app.zip';
    $publicZip = $deployDir . '/deploy_public.zip';

    if (file_exists($appZip)) {
        unlink($appZip);
        $cleaned[] = 'deploy_app.zip';
    }
    if (file_exists($publicZip)) {
        unlink($publicZip);
        $cleaned[] = 'deploy_public.zip';
    }

    if (is_dir($deployDir) && count(scandir($deployDir)) === 2) {
        rmdir($deployDir);
        $cleaned[] = 'aliesmo_deploy/';
    }

    // Self-destruct setelah response
    $selfDestruct = $publicHtmlDir . '/deploy.php';
    if (file_exists($selfDestruct)) {
        $cleaned[] = 'deploy.php';
        register_shutdown_function(function () use ($selfDestruct) {
            @unlink($selfDestruct);
        });
    }

    echo json_encode(['status' => 'ok', 'cleaned' => $cleaned]);
    exit;
}

// ─── Debug: info path (hanya jika action=info + secret valid) ───────────────
if ($action === 'info') {
    echo json_encode([
        'home_dir'    => $homeDir,
        'public_html' => $publicHtmlDir,
        'app_dir'     => $appDir,
        'deploy_dir'  => $deployDir,
        'app_zip'     => file_exists($deployDir . '/deploy_app.zip') ? 'EXISTS' : 'NOT FOUND',
        'public_zip'  => file_exists($deployDir . '/deploy_public.zip') ? 'EXISTS' : 'NOT FOUND',
        'secret_file' => file_exists($homeDir . '/.deploy_secret') ? 'EXISTS' : 'NOT FOUND',
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action: ' . $action]);

// ─── Helper: chmod recursive ────────────────────────────────────────────────
function chmod_recursive(string $path, int $mode): void
{
    chmod($path, $mode);
    if (is_dir($path)) {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            @chmod($item->getPathname(), $mode);
        }
    }
}
