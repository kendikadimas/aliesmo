<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detect environment — production (cPanel) vs local dev
// Production: public/ di public_html/, Laravel di ~/aliesmo/
// Local: public/ dan Laravel di folder yang sama
$isProduction = is_dir(__DIR__ . '/../aliesmo/vendor');

$basePath = $isProduction
    ? __DIR__ . '/../aliesmo'
    : __DIR__ . '/..';

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
