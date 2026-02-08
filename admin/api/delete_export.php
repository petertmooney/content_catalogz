<?php
session_start();
require_once '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$filename = $data['filename'] ?? '';

if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename required']);
    exit;
}

// Sanitize filename
$filename = basename($filename);
$exportDir = dirname(dirname(__DIR__)) . '/exports';
$filePath = $exportDir . '/' . $filename;

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'Export file not found']);
    exit;
}

if (unlink($filePath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete export']);
}
