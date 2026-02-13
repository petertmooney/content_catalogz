<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['sections']) || !is_array($data['sections'])) {
        throw new Exception('Invalid dashboard layout data');
    }
    
    $userId = $_SESSION['user_id'];
    $layoutFile = __DIR__ . '/../config/dashboard_layout_' . $userId . '.json';
    
    // Save dashboard layout to file
    $result = file_put_contents($layoutFile, json_encode($data['sections'], JSON_PRETTY_PRINT));
    
    if ($result === false) {
        throw new Exception('Failed to save dashboard layout');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Dashboard layout saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
