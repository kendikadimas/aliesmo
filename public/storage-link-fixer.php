<?php
/**
 * Storage link fixer — untuk cPanel shared hosting yang tidak support symlink via artisan.
 * Jalankan manual sekali: https://aliesmo.id/storage-link-fixer.php?token=YOUR_TOKEN
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

$publicStorage = __DIR__ . '/storage';
$storageAppPublic = realpath(__DIR__ . '/../storage/app/public');

echo "=== Storage Link Fixer ===\n\n";
echo "Public storage path: $publicStorage\n";
echo "Storage app/public path: $storageAppPublic\n\n";

if (!is_dir($storageAppPublic)) {
    die("ERROR: storage/app/public does not exist!\n");
}

if (is_link($publicStorage)) {
    $target = readlink($publicStorage);
    echo "Existing symlink target: $target\n";
    if (realpath($publicStorage) === $storageAppPublic) {
        echo "Symlink is correct!\n";
        exit(0);
    }
    echo "Symlink is broken or wrong. Removing...\n";
    unlink($publicStorage);
} elseif (is_dir($publicStorage)) {
    echo "public/storage is a real directory (not symlink). Removing...\n";
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($publicStorage, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    rmdir($publicStorage);
}

echo "Creating symlink...\n";
if (symlink($storageAppPublic, $publicStorage)) {
    echo "SUCCESS: Symlink created!\n";
    echo "Verify: " . (is_link($publicStorage) ? 'OK' : 'FAILED') . "\n";
} else {
    echo "FAILED: Could not create symlink.\n";
    echo "Error: " . error_get_last()['message'] . "\n";
    echo "\nTrying fallback: copying storage/app/public to public/storage...\n";

    if (!is_dir($publicStorage)) {
        mkdir($publicStorage, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storageAppPublic, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $count = 0;
    foreach ($iterator as $item) {
        $dest = $publicStorage . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
        if ($item->isDir()) {
            if (!is_dir($dest)) mkdir($dest, 0755, true);
        } else {
            copy($item->getRealPath(), $dest);
            $count++;
        }
    }
    echo "Fallback: Copied $count files.\n";
}

echo "\nDone.\n";
