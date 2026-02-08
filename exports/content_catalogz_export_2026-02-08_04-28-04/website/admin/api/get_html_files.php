<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

requireLogin();

header('Content-Type: application/json');

// Get all HTML files in the root directory
$htmlFiles = [];
$rootPath = dirname(dirname(__DIR__));

$files = glob($rootPath . '/*.html');
foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Extract title from HTML
    preg_match('/<title>(.*?)<\/title>/', $content, $titleMatch);
    $title = $titleMatch[1] ?? $filename;
    
    $htmlFiles[] = [
        'filename' => $filename,
        'title' => $title,
        'path' => $file,
        'size' => filesize($file),
        'modified' => filemtime($file)
    ];
}

echo json_encode([
    'success' => true,
    'files' => $htmlFiles
]);
?>
