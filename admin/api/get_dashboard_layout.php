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
    $layoutFile = __DIR__ . '/../config/dashboard_layout_' . $userId . '.json';
    
    if (file_exists($layoutFile)) {
        $sections = json_decode(file_get_contents($layoutFile), true);
        echo json_encode([
            'success' => true,
            'sections' => $sections
        ]);
    } else {
        // Return empty success to use default layout
        echo json_encode([
            'success' => false,
            'message' => 'No custom dashboard layout found'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
