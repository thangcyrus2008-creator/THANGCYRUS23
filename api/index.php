<?php

// Ensure /tmp storage directories exist on Vercel serverless environment
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    putenv('APP_PACKAGES_CACHE=/tmp/storage/bootstrap/packages.php');
    putenv('APP_SERVICES_CACHE=/tmp/storage/bootstrap/services.php');
    putenv('APP_CONFIG_CACHE=/tmp/storage/bootstrap/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/storage/bootstrap/routes.php');
    putenv('APP_EVENTS_CACHE=/tmp/storage/bootstrap/events.php');

    $_ENV['APP_PACKAGES_CACHE'] = '/tmp/storage/bootstrap/packages.php';
    $_ENV['APP_SERVICES_CACHE'] = '/tmp/storage/bootstrap/services.php';
    $_ENV['APP_CONFIG_CACHE'] = '/tmp/storage/bootstrap/config.php';
    $_ENV['APP_ROUTES_CACHE'] = '/tmp/storage/bootstrap/routes.php';
    $_ENV['APP_EVENTS_CACHE'] = '/tmp/storage/bootstrap/events.php';

    $storageDirs = [
        '/tmp/storage/bootstrap',
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/cache',
        '/tmp/storage/logs',
    ];
    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    // Prepare SQLite database in writable /tmp directory
    if (!file_exists('/tmp/database.sqlite')) {
        $sourceDb = __DIR__ . '/../database/database.sqlite';
        if (file_exists($sourceDb)) {
            @copy($sourceDb, '/tmp/database.sqlite');
        } else {
            @touch('/tmp/database.sqlite');
        }
    }
}

// Forward to Laravel entry point
require __DIR__ . '/../public/index.php';
