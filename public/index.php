<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Production: public/ di public_html/, app di ~/aliesmo1 (atau ~/aliesmo)
$basePath = __DIR__ . '/..';
foreach (['aliesmo1', 'aliesmo'] as $appDir) {
    if (is_dir(__DIR__ . '/../' . $appDir . '/vendor')) {
        $basePath = __DIR__ . '/../' . $appDir;
        break;
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
