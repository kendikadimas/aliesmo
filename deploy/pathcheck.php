<?php
// Upload ke public_html/pathcheck.php, akses sekali, lalu hapus
echo '<pre>';
echo "public_html  : " . __DIR__ . "\n";
echo "home dir     : " . dirname(__DIR__) . "\n";
echo "parent dir   : " . dirname(dirname(__DIR__)) . "\n\n";

// Cek folder yang ada di home
$home = dirname(__DIR__);
echo "=== Folders in home ($home) ===\n";
if (is_dir($home)) {
    foreach (scandir($home) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $home . '/' . $item;
        echo (is_dir($path) ? '[DIR] ' : '[FILE]') . " $item\n";
    }
}

// Cek folder aliesmo_deploy
echo "\n=== Check aliesmo_deploy ===\n";
$deployDir = $home . '/aliesmo_deploy';
if (is_dir($deployDir)) {
    echo "aliesmo_deploy EXISTS\n";
    foreach (scandir($deployDir) as $item) {
        if ($item === '.' || $item === '..') continue;
        echo "  - $item (" . filesize($deployDir.'/'.$item) . " bytes)\n";
    }
} else {
    echo "aliesmo_deploy NOT FOUND\n";
}
echo '</pre>';
