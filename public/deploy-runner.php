<?php

// Security: hapus file ini setelah dijalankan
$token = $_GET['token'] ?? '';
$expectedToken = getenv('DEPLOY_TOKEN') ?: 'JDZvSs0bLfMK5ikRg2pzWGhn14jEdoFa';

if ($token !== $expectedToken) {
    http_response_code(403);
    die('Forbidden');
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>\n";

// Run migrate
echo "=== artisan migrate --force ===\n";
$exitCode = $kernel->call('migrate', ['--force' => true]);
echo $kernel->output();
echo "Exit code: $exitCode\n\n";

// Run optimize:clear
echo "=== artisan optimize:clear ===\n";
$exitCode = $kernel->call('optimize:clear');
echo $kernel->output();
echo "Exit code: $exitCode\n\n";

echo "=== Done. Delete this file immediately! ===\n";
echo "</pre>\n";

// Auto-delete setelah dijalankan
unlink(__FILE__);
echo "<p>File deleted.</p>\n";
