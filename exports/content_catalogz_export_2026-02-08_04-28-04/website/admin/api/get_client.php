<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$clientId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM quotes WHERE id = ?");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Check if services column exists and decode JSON if present
    if (isset($row['services']) && $row['services']) {
        $row['services'] = json_decode($row['services'], true);
    } else {
        $row['services'] = [];
    }
    
    echo json_encode(['success' => true, 'client' => $row]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Client not found']);
}

$stmt->close();
$conn->close();
