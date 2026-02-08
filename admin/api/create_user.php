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
    if (empty($data['username']) || empty($data['password'])) {
        throw new Exception('Username and password are required');
    }
    
    $username = trim($data['username']);
    $password = $data['password'];
    $email = isset($data['email']) ? trim($data['email']) : null;
    $role = isset($data['role']) ? $data['role'] : 'admin';
    
    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
        throw new Exception('Username must be at least 3 characters and contain only letters, numbers, and underscores');
    }
    
    // Validate password length
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }
    
    // Validate role
    if (!in_array($role, ['admin', 'superadmin'])) {
        $role = 'admin';
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('Username already exists');
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $passwordHash, $email, $role);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create user: ' . $stmt->error);
    }
    
    $userId = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => $userId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
