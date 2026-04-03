<?php

// Router for PHP built-in server
// Serves static files directly, forwards everything else to index.php

$path = $_SERVER['REQUEST_URI'];
$path = parse_url($path, PHP_URL_PATH);
$file = __DIR__ . $path;

// If the file exists and is not a directory, serve it directly
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    // Set correct content type for common file types
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
    ];

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }

    readfile($file);
    return true;
}

// Otherwise, forward to Symfony's front controller
require __DIR__ . '/index.php';
