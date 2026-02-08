<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

requireLogin();

header('Content-Type: application/json');

try {
    // Check if requesting a specific user
    $userId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($userId) {
        // Get specific user
        $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('User not found');
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
    } else {
        // Get all users
        $sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $conn->error);
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
