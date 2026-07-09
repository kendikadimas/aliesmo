<?php
// TEMPORARY - DELETE AFTER USE
$secret = $_GET['secret'] ?? '';
if ($secret !== 'aliesmo-migrate-2026') {
    die('Unauthorized');
}

chdir(dirname(__DIR__));
$output = shell_exec('/usr/local/bin/php artisan migrate --force 2>&1');
echo '<pre>' . htmlspecialchars($output) . '</pre>';
