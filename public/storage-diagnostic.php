<?php
/**
 * Storage diagnostic — cek lokasi file upload di server
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

echo "=== Storage Diagnostic ===\n\n";

$paths = [
    'storage/app/public' => realpath(__DIR__ . '/../storage/app/public'),
    'storage/app/public/banners' => realpath(__DIR__ . '/../storage/app/public/banners'),
    'storage/app/public/products' => realpath(__DIR__ . '/../storage/app/public/products'),
    'storage/app/public/site-settings' => realpath(__DIR__ . '/../storage/app/public/site-settings'),
    'public/storage (symlink)' => is_link(__DIR__ . '/storage') ? readlink(__DIR__ . '/storage') : 'NOT A SYMLINK',
];

foreach ($paths as $label => $path) {
    echo "$label:\n";
    echo "  Path: " . ($path ?: 'NULL/NOT EXISTS') . "\n";
    if ($path && is_dir($path)) {
        echo "  Exists: YES\n";
        $files = glob($path . '/*');
        echo "  Files: " . count($files) . "\n";
        foreach (array_slice($files, 0, 5) as $file) {
            echo "    - " . basename($file) . "\n";
        }
    } else {
        echo "  Exists: NO\n";
    }
    echo "\n";
}

echo "=== Laravel Storage Config ===\n\n";
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default disk: " . config('filesystems.default') . "\n";
echo "Public disk root: " . config('filesystems.disks.public.root') . "\n";
echo "Public disk url: " . config('filesystems.disks.public.url') . "\n";
echo "Public disk visibility: " . config('filesystems.disks.public.visibility') . "\n";

echo "\n=== Symlink Test ===\n\n";
$publicStorage = __DIR__ . '/storage';
if (is_link($publicStorage)) {
    $target = readlink($publicStorage);
    echo "Symlink target: $target\n";
    echo "Target exists: " . (is_dir($target) ? 'YES' : 'NO') . "\n";
    
    if (is_dir($target)) {
        $bannersDir = $target . '/banners';
        $productsDir = $target . '/products';
        
        echo "\nBanners dir: " . (is_dir($bannersDir) ? 'EXISTS' : 'NOT EXISTS') . "\n";
        if (is_dir($bannersDir)) {
            $files = glob($bannersDir . '/*');
            echo "Files: " . count($files) . "\n";
            foreach (array_slice($files, 0, 3) as $file) {
                echo "  - " . basename($file) . "\n";
            }
        }
        
        echo "\nProducts dir: " . (is_dir($productsDir) ? 'EXISTS' : 'NOT EXISTS') . "\n";
        if (is_dir($productsDir)) {
            $files = glob($productsDir . '/*');
            echo "Files: " . count($files) . "\n";
            foreach (array_slice($files, 0, 3) as $file) {
                echo "  - " . basename($file) . "\n";
            }
        }
    }
} else {
    echo "public/storage is NOT a symlink!\n";
}

echo "\nDone.\n";
