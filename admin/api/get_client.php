<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once '../config/auth.php';
require_once '../config/db.php';

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
    // Decode services JSON
    if ($row['services']) {
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
