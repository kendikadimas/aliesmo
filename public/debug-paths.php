<?php
// Debug file - HAPUS SETELAH DIPAKAI
$paths = [
    'PHP version'            => phpversion(),
    '__DIR__'                => __DIR__,
    'Laravel root'           => realpath(__DIR__ . '/..'),
    'vendor exists'          => is_dir(__DIR__ . '/../vendor') ? 'YES' : 'NO',
    'bootstrap/app.php'      => file_exists(__DIR__ . '/../bootstrap/app.php') ? 'EXISTS' : 'MISSING',
    '.env exists'            => file_exists(__DIR__ . '/../.env') ? 'EXISTS' : 'MISSING',
    'public/build exists'    => is_dir(__DIR__ . '/build') ? 'YES' : 'NO',
    'manifest.json'          => file_exists(__DIR__ . '/build/manifest.json') ? 'EXISTS' : 'MISSING',
    'manifest content'       => file_exists(__DIR__ . '/build/manifest.json')
                                    ? file_get_contents(__DIR__ . '/build/manifest.json')
                                    : 'N/A',
    'storage/logs writable'  => is_writable(__DIR__ . '/../storage/logs') ? 'YES' : 'NO',
    'APP_ENV'                => getenv('APP_ENV') ?: '(not set)',
    'APP_DEBUG'              => getenv('APP_DEBUG') ?: '(not set)',
];

echo '<pre style="font-family:monospace;padding:20px">';
echo "=== SERVER DEBUG ===\n\n";
foreach ($paths as $key => $val) {
    echo str_pad($key, 30) . ': ' . $val . "\n";
}

// Try bootstrap Laravel and catch errors
echo "\n=== LARAVEL BOOTSTRAP TEST ===\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "Laravel bootstrap: OK\n";
    echo "APP_URL: " . config('app.url') . "\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "DB_HOST: " . config('database.connections.mysql.host') . "\n";

    // Test DB connection
    try {
        \DB::connection()->getPdo();
        echo "DB connection: OK\n";
    } catch (\Exception $e) {
        echo "DB connection: FAILED - " . $e->getMessage() . "\n";
    }

} catch (\Throwable $e) {
    echo "Laravel bootstrap: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo '</pre>';
