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
}

// Forward to Laravel entry point
require __DIR__ . '/../public/index.php';
