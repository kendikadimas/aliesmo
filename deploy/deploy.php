<?php
/**
 * Deploy unzipper script — diupload sementara ke public_html/ saat deploy.
 * Script ini akan dihapus otomatis setelah proses selesai (action=cleanup).
 *
 * PENTING: Jangan tinggalkan file ini di server setelah deploy selesai.
 */

// ─── Autentikasi ────────────────────────────────────────────────────────────
$secret = $_POST['secret'] ?? '';
$expectedSecret = getenv('DEPLOY_SECRET') ?: ''; // dibaca dari env server

// Fallback: baca dari file .deploy_secret di home directory
if (empty($expectedSecret)) {
    $secretFile = dirname(__DIR__) . '/.deploy_secret';
    if (file_exists($secretFile)) {
        $expectedSecret = trim(file_get_contents($secretFile));
    }
}

if (empty($expectedSecret) || !hash_equals($expectedSecret, $secret)) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

$action = $_POST['action'] ?? '';
$homeDir  = dirname(__DIR__); // /home/username
$appDir   = $homeDir . '/aliesmo';
$deployDir = $homeDir . '/aliesmo_deploy';
$publicDir = $homeDir . '/public_html';

// ─── Action: unzip ──────────────────────────────────────────────────────────
if ($action === 'unzip') {
    $results = [];

    // 1. Unzip app ke ~/aliesmo/
    $appZip = $deployDir . '/deploy_app.zip';
    if (file_exists($appZip)) {
        $zip = new ZipArchive();
        if ($zip->open($appZip) === true) {
            // Buat folder jika belum ada
            if (!is_dir($appDir)) {
                mkdir($appDir, 0755, true);
            }
            $zip->extractTo($appDir);
            $zip->close();
            $results['app'] = 'extracted to ' . $appDir;
        } else {
            http_response_code(500);
            die(json_encode(['error' => 'Failed to open deploy_app.zip']));
        }
    } else {
        $results['app'] = 'deploy_app.zip not found, skipped';
    }

    // 2. Unzip public ke ~/public_html/
    $publicZip = $deployDir . '/deploy_public.zip';
    if (file_exists($publicZip)) {
        $zip = new ZipArchive();
        if ($zip->open($publicZip) === true) {
            $zip->extractTo($publicDir);
            $zip->close();
            $results['public'] = 'extracted to ' . $publicDir;
        } else {
            http_response_code(500);
            die(json_encode(['error' => 'Failed to open deploy_public.zip']));
        }
    } else {
        $results['public'] = 'deploy_public.zip not found, skipped';
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

    // Hapus zip files
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

    // Hapus folder aliesmo_deploy jika kosong
    if (is_dir($deployDir) && count(scandir($deployDir)) === 2) {
        rmdir($deployDir);
        $cleaned[] = 'aliesmo_deploy/';
    }

    // Hapus script ini sendiri (self-destruct)
    $selfDestruct = $publicDir . '/deploy.php';
    if (file_exists($selfDestruct)) {
        $cleaned[] = 'deploy.php';
        // Hapus setelah response dikirim
        register_shutdown_function(function () use ($selfDestruct) {
            @unlink($selfDestruct);
        });
    }

    echo json_encode(['status' => 'ok', 'cleaned' => $cleaned]);
    exit;
}

// ─── Action tidak dikenal ────────────────────────────────────────────────────
http_response_code(400);
echo json_encode(['error' => 'Unknown action']);

// ─── Helper: chmod recursive ────────────────────────────────────────────────
function chmod_recursive(string $path, int $mode): void
{
    chmod($path, $mode);
    if (is_dir($path)) {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            chmod($item->getPathname(), $mode);
        }
    }
}
