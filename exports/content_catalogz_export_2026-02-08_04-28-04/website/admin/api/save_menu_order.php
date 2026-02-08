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
    
    if (!isset($data['order']) || !is_array($data['order'])) {
        throw new Exception('Invalid menu order data');
    }
    
    $userId = $_SESSION['user_id'];
    $menuFile = __DIR__ . '/../config/menu_order_' . $userId . '.json';
    
    // Save menu order to file
    $result = file_put_contents($menuFile, json_encode($data['order'], JSON_PRETTY_PRINT));
    
    if ($result === false) {
        throw new Exception('Failed to save menu order');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Menu order saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
