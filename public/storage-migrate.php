<?php
/**
 * Migrate files dari storage/app/private ke storage/app/public
 * Jalankan sekali setelah deploy: https://aliesmo.id/storage-migrate.php?token=YOUR_TOKEN
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

header('Content-Type: text/plain; charset=utf-8');

echo "=== Storage Migration ===\n\n";

$privateBase = realpath(__DIR__ . '/../storage/app/private');
$publicBase = realpath(__DIR__ . '/../storage/app/public');

echo "Private base: $privateBase\n";
echo "Public base: $publicBase\n\n";

if (!$privateBase || !$publicBase) {
    die("ERROR: One or both paths do not exist!\n");
}

$dirs = ['banners', 'products', 'payment', 'site-settings'];
$migrated = 0;
$skipped = 0;

foreach ($dirs as $dir) {
    $privateDir = $privateBase . '/' . $dir;
    $publicDir = $publicBase . '/' . $dir;
    
    echo "--- Directory: $dir ---\n";
    
    if (!is_dir($privateDir)) {
        echo "  Private dir not exists, skipping.\n\n";
        continue;
    }
    
    if (!is_dir($publicDir)) {
        mkdir($publicDir, 0755, true);
        echo "  Created public dir: $publicDir\n";
    }
    
    $files = glob($privateDir . '/*');
    echo "  Found " . count($files) . " files in private\n";
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            $dest = $publicDir . '/' . $filename;
            
            if (file_exists($dest)) {
                echo "  SKIP: $filename (already exists in public)\n";
                $skipped++;
            } else {
                if (copy($file, $dest)) {
                    echo "  MOVED: $filename\n";
                    $migrated++;
                } else {
                    echo "  ERROR: Failed to copy $filename\n";
                }
            }
        }
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "Migrated: $migrated files\n";
echo "Skipped: $skipped files\n";
echo "\nDone.\n";
