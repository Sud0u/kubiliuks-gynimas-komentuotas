<?php
// Hostinger public_html index komentaro pradzia
// Sitas failas yra viesas Hostinger pradinis failas.
// Jis paleidzia Laravel projekta, kuris laikomas ne public_html viduje.
// Hostinger public_html index komentaro pabaiga

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__ . '/../laravel_projektas/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../laravel_projektas/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../laravel_projektas/bootstrap/app.php';

$app->handleRequest(Request::capture());