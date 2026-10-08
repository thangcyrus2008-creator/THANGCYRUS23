<?php

// Ensure /tmp storage directories exist on Vercel serverless environment
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $storageDirs = [
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
