<?php
// Debug file - HAPUS SETELAH DIPAKAI

$root = realpath(__DIR__ . '/..');

// Parse .env manually
$env = [];
$envFile = $root . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
        }
    }
}

echo '<pre style="font-family:monospace;padding:20px">';
echo "=== SERVER PATHS ===\n\n";
$checks = [
    'PHP version'              => phpversion(),
    '__DIR__ (public)'         => __DIR__,
    'Laravel root'             => $root,
    'vendor/autoload.php'      => file_exists($root . '/vendor/autoload.php') ? 'EXISTS' : 'MISSING',
    'bootstrap/app.php'        => file_exists($root . '/bootstrap/app.php') ? 'EXISTS' : 'MISSING',
    '.env'                     => file_exists($envFile) ? 'EXISTS' : 'MISSING',
    'public/build/'            => is_dir(__DIR__ . '/build') ? 'EXISTS' : 'MISSING',
    'public/build/manifest.json' => file_exists(__DIR__ . '/build/manifest.json') ? 'EXISTS' : 'MISSING',
    'storage/logs writable'    => is_writable($root . '/storage/logs') ? 'YES' : 'NO',
];
foreach ($checks as $k => $v) {
    echo str_pad($k, 30) . ': ' . $v . "\n";
}

echo "\n=== .ENV VALUES ===\n\n";
$show = ['APP_ENV','APP_DEBUG','APP_URL','DB_HOST','DB_DATABASE','DB_USERNAME'];
foreach ($show as $k) {
    $v = $env[$k] ?? '(not set)';
    // mask password-like keys
    echo str_pad($k, 20) . ': ' . $v . "\n";
}

echo "\n=== MANIFEST CONTENT ===\n";
$manifest = __DIR__ . '/build/manifest.json';
echo file_exists($manifest) ? file_get_contents($manifest) : "NOT FOUND\n";

echo "\n=== LARAVEL BOOTSTRAP TEST ===\n";
try {
    require $root . '/vendor/autoload.php';
    $app = require_once $root . '/bootstrap/app.php';
    echo "Bootstrap: OK\n";

    // Kernel boot to make DB available
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->handle($request);

    // Test DB
    try {
        \DB::connection()->getPdo();
        $version = \DB::selectOne('SELECT VERSION() as v')->v ?? 'unknown';
        echo "DB connection: OK (MySQL $version)\n";

        // Check migrations
        $tables = \DB::select("SHOW TABLES");
        $tableList = array_map(fn($t) => array_values((array)$t)[0], $tables);
        echo "Tables (" . count($tableList) . "): " . implode(', ', $tableList) . "\n";
    } catch (\Exception $e) {
        echo "DB connection: FAILED - " . $e->getMessage() . "\n";
    }
} catch (\Throwable $e) {
    echo "Bootstrap: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "At: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo '</pre>';
