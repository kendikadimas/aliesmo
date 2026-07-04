<?php
// One-time migration runner — DELETE THIS FILE after use!
// Access via: https://aliesmo.id/migrate.php

define('LARAVEL_START', microtime(true));

$isProduction = is_dir(__DIR__ . '/../aliesmo/vendor');
$basePath = $isProduction ? __DIR__ . '/../aliesmo' : __DIR__ . '/..';

require $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo '<pre>';

echo "=== Running migrations ===\n\n";
$status = $kernel->call('migrate', ['--force' => true]);
echo $kernel->output();
echo "\nExit code: $status\n";

echo "\n=== Clearing caches ===\n\n";
$kernel->call('config:clear');
echo $kernel->output();

$kernel->call('cache:clear');
echo $kernel->output();

$kernel->call('view:clear');
echo $kernel->output();

echo "\n=== Done! DELETE THIS FILE NOW ===\n";
echo '</pre>';
