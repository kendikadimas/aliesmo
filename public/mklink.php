<?php
// One-time script to create symlink: public_html/build -> aliesmo/public/build
// DELETE THIS FILE after running!

$target = __DIR__ . '/../aliesmo/public/build';
$link   = __DIR__ . '/build';

if (file_exists($link) || is_link($link)) {
    echo "Already exists: $link\n";
    echo "Is symlink: " . (is_link($link) ? 'yes' : 'no') . "\n";
    echo "Target: " . (is_link($link) ? readlink($link) : 'n/a') . "\n";
} else {
    if (symlink($target, $link)) {
        echo "Symlink created: $link -> $target\n";
    } else {
        echo "Failed to create symlink.\n";
        // Fallback: try to copy instead
        echo "Trying copy...\n";
        $result = shell_exec("cp -r " . escapeshellarg($target) . " " . escapeshellarg($link) . " 2>&1");
        echo $result ?: "Copy done.\n";
    }
}

echo "\nVerify:\n";
echo "build/ exists: " . (file_exists($link) ? 'yes' : 'no') . "\n";
echo "manifest.json: " . (file_exists($link . '/manifest.json') ? 'yes' : 'no') . "\n";
