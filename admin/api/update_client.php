<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once '../config/auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$clientId = intval($data['id']);
$address = isset($data['address']) ? trim($data['address']) : null;
$services = isset($data['services']) ? $data['services'] : [];
$totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
$totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;

// Calculate total remaining
$totalRemaining = $totalCost - $totalPaid;

// Convert services array to JSON
$servicesJson = json_encode($services);

// Update the client information
$stmt = $conn->prepare("UPDATE quotes SET address = ?, services = ?, total_cost = ?, total_paid = ?, total_remaining = ? WHERE id = ?");
$stmt->bind_param("ssdddi", $address, $servicesJson, $totalCost, $totalPaid, $totalRemaining, $clientId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Client information updated successfully',
        'total_remaining' => number_format($totalRemaining, 2)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
