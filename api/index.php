<?php
// Set up temporary storage for Vercel Serverless environment
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
    mkdir($storagePath . '/framework/cache/data', 0777, true);
    mkdir($storagePath . '/framework/sessions', 0777, true);
    mkdir($storagePath . '/framework/views', 0777, true);
    mkdir($storagePath . '/logs', 0777, true);
}

$_ENV['APP_STORAGE'] = $storagePath;
$_SERVER['APP_STORAGE'] = $storagePath;
putenv("APP_STORAGE={$storagePath}");

$_ENV['APP_DEBUG'] = 'true';
$_SERVER['APP_DEBUG'] = 'true';
putenv("APP_DEBUG=true");

$_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");

// Forward Vercel requests to normal index.php
require __DIR__ . '/../public/index.php';
