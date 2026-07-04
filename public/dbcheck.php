<?php
// DB status checker — DELETE THIS FILE after use!
define('LARAVEL_START', microtime(true));

$isProduction = is_dir(__DIR__ . '/../aliesmo/vendor');
$basePath = $isProduction ? __DIR__ . '/../aliesmo' : __DIR__ . '/..';

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo '<pre>';

// Cek semua tabel yang ada
try {
    $tables = DB::select('SHOW TABLES');
    echo "=== Tables in database ===\n";
    foreach ($tables as $table) {
        $name = array_values((array)$table)[0];
        echo "  - $name\n";
    }
    echo "\nTotal: " . count($tables) . " tables\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

echo "\n=== Migration status ===\n";
$kernel->call('migrate:status');
echo $kernel->output();

echo '</pre>';
