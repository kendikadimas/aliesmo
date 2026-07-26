<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Monorepo di public_html: public/index.php → parent = app root (punya vendor)
// Split layout: public_html/index.php → sibling aliesmo / aliesmo1
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    $basePath = __DIR__ . '/..';
} elseif (is_dir(__DIR__ . '/../aliesmo1/vendor')) {
    $basePath = __DIR__ . '/../aliesmo1';
} elseif (is_dir(__DIR__ . '/../aliesmo/vendor')) {
    $basePath = __DIR__ . '/../aliesmo';
} else {
    $basePath = __DIR__ . '/..';
}

// Self-heal: empty routes-v7.php = Laravel "cached" with zero routes → site-wide 404
// (deploy used to write <?php return []; — never do that again)
$routesCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (is_file($routesCache)) {
    $raw = @file_get_contents($routesCache);
    if (is_string($raw) && preg_match('/return\s*\[\s*\]\s*;/', $raw)) {
        @unlink($routesCache);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
