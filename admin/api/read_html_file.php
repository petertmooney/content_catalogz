<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';
include '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$filename = $_GET['filename'] ?? '';
if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename required']);
    exit;
}

// Security: only allow reading .html files in root directory
if (!preg_match('/^[a-z0-9_-]+\.html$/i', $filename)) {
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit;
}

$rootPath = dirname(dirname(__DIR__));
$filePath = $rootPath . '/' . $filename;

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

$content = file_get_contents($filePath);

echo json_encode([
    'success' => true,
    'filename' => $filename,
    'content' => $content
]);
?>
