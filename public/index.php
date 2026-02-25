<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/**
 * 🚀 INFINITYFREE CORS & SECURITY BYPASS
 * Wajib diletakkan di paling atas sebelum bootstrap Laravel
 */

// 1. Izinkan Origin Vercel
header('Access-Control-Allow-Origin: https://mis-kampar.vercel.app');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With, X-XSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');

// 2. Handle Preflight Options Request (Penting buat 'Failed to Fetch')
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// 3. Folder Helper untuk Cookie Security
if (strpos($_SERVER['REQUEST_URI'], 'api/login') !== false && $_SERVER['REQUEST_METHOD'] == 'GET') {
    die("Browser Verified. Silakan balik ke aplikasi Vercel dan login lagi.");
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());