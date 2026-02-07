<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';
include '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$filename = $_POST['filename'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($filename) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Filename and content required']);
    exit;
}

// Security: only allow writing .html files in root directory
if (!preg_match('/^[a-z0-9_-]+\.html$/i', $filename)) {
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit;
}

$rootPath = dirname(dirname(__DIR__));
$filePath = $rootPath . '/' . $filename;

// Create backup before saving
$backupPath = $rootPath . '/backups';
if (!is_dir($backupPath)) {
    mkdir($backupPath, 0755, true);
}

if (file_exists($filePath)) {
    $backupFile = $backupPath . '/' . $filename . '.' . date('Y-m-d_H-i-s') . '.backup';
    copy($filePath, $backupFile);
}

// Save the file
if (file_put_contents($filePath, $content) !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'File saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save file'
    ]);
}
?>
