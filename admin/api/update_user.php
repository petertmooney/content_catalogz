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
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['id'])) {
        throw new Exception('User ID is required');
    }
    
    $userId = intval($data['id']);
    $email = isset($data['email']) ? trim($data['email']) : null;
    $fullName = isset($data['full_name']) ? trim($data['full_name']) : null;
    $role = isset($data['role']) ? $data['role'] : 'admin';
    $password = isset($data['password']) ? $data['password'] : null;
    
    // Validate role
    if (!in_array($role, ['admin', 'superadmin'])) {
        $role = 'admin';
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    // Update user
    if ($password && strlen($password) >= 8) {
        // Update with new password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $fullName, $email, $role, $passwordHash, $userId);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $email, $role, $userId);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update user: ' . $stmt->error);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
