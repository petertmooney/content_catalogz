<?php
// Router script for PHP built-in web server
// This handles incoming requests and routes them appropriately

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Remove query string
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle root
if ($path === '/' || $path === '') {
    $path = '/index.html';
}

// Build the file path
$filePath = __DIR__ . $path;

// Security check - prevent directory traversal
$realPath = realpath($filePath);
if ($realPath === false || strpos($realPath, __DIR__) !== 0) {
    http_response_code(404);
    echo "404 Not Found";
    return false;
}

// If it's a PHP file and it exists, execute it
if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && file_exists($filePath)) {
    require $filePath;
    return true;
}

// If it's a static file and exists, let PHP serve it
if (file_exists($filePath) && is_file($filePath)) {
    return false; // Let PHP's built-in server handle it
}

// File doesn't exist
http_response_code(404);
echo "404 Not Found: " . htmlspecialchars($path);
return true;
