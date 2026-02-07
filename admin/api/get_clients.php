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
    // Get all clients from quotes table
    $sql = "SELECT id, name, company, email, phone, status FROM quotes ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    
    $clients = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
