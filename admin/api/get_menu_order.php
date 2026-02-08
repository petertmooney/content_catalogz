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
    $userId = $_SESSION['user_id'];
    $menuFile = __DIR__ . '/../config/menu_order_' . $userId . '.json';
    
    if (file_exists($menuFile)) {
        $order = json_decode(file_get_contents($menuFile), true);
        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
    } else {
        // Return empty success to use default order
        echo json_encode([
            'success' => false,
            'message' => 'No custom menu order found'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
