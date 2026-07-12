<?php
/**
 * ONE-TIME reset tool — HAPUS file ini setelah digunakan!
 * Jalankan migrate:fresh + seed admin cs@aliesmo.id
 *
 * Akses: https://aliesmo.id/reset-db.php?token=RESET_ALIESMO_2026
 */

$VALID_TOKEN = 'RESET_ALIESMO_2026';
$provided    = $_GET['token'] ?? '';

if (!hash_equals($VALID_TOKEN, $provided)) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1>');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app    = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$results = [];

// migrate:fresh
$kernel->call('migrate:fresh', ['--force' => true]);
$results['migrate:fresh'] = trim($kernel->output());

// Seed admin
$kernel->call('db:seed', ['--class' => 'AdminSeeder', '--force' => true]);
$results['AdminSeeder'] = trim($kernel->output());

// Clear cache
$kernel->call('optimize:clear');
$results['optimize:clear'] = trim($kernel->output());

header('Content-Type: application/json');
echo json_encode([
    'success'  => true,
    'message'  => 'Database reset selesai. HAPUS FILE INI SEKARANG!',
    'admin'    => ['email' => 'cs@aliesmo.id', 'password' => 'aliesmopemalang'],
    'results'  => $results,
    'timestamp'=> date('Y-m-d H:i:s'),
], JSON_PRETTY_PRINT);
