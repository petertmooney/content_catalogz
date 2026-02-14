<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header first
header('Content-Type: application/json');

// Database configuration
define('DB_HOST', 'db');
define('DB_PORT', 3306);
define('DB_USER', 'petertmooney');
define('DB_PASS', '68086500aA!');
define('DB_NAME', 'Content_Catalogz');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

if (!isset($data['id']) || !isset($data['client_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$invoiceId = intval($data['id']);
$clientId = intval($data['client_id']);
$invoiceNumber = isset($data['invoice_number']) ? trim($data['invoice_number']) : '';
$invoiceDate = isset($data['invoice_date']) ? $data['invoice_date'] : date('Y-m-d');
$totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
$totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;
$totalRemaining = isset($data['total_remaining']) ? floatval($data['total_remaining']) : 0.00;
$services = isset($data['services']) ? $data['services'] : [];

// Calculate due date (30 days from invoice date)
$dueDate = date('Y-m-d', strtotime($invoiceDate . ' + 30 days'));

// Update invoice
$stmt = $conn->prepare("UPDATE invoices SET invoice_date = ?, due_date = ?, amount = ?, total_cost = ?, total_paid = ?, total_remaining = ?, status = 'sent' WHERE id = ? AND client_id = ?");
$stmt->bind_param("sddddiii", $invoiceDate, $dueDate, $totalCost, $totalCost, $totalPaid, $totalRemaining, $invoiceId, $clientId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Delete existing services
        $deleteStmt = $conn->prepare("DELETE FROM invoice_services WHERE invoice_id = ?");
        $deleteStmt->bind_param("i", $invoiceId);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Save new services if provided
        if (!empty($services) && is_array($services)) {
            $serviceStmt = $conn->prepare("INSERT INTO invoice_services (invoice_id, description, quantity, unit_price, total_cost) VALUES (?, ?, 1, ?, ?)");
            foreach ($services as $service) {
                if (isset($service['name']) && isset($service['cost'])) {
                    $serviceName = trim($service['name']);
                    $serviceCost = floatval($service['cost']);
                    $serviceStmt->bind_param("isdd", $invoiceId, $serviceName, $serviceCost, $serviceCost);
                    $serviceStmt->execute();
                }
            }
            $serviceStmt->close();
        }
        
        // Log activity for invoice update
        $activityStmt = $conn->prepare("INSERT INTO activities (client_id, type, subject, description, activity_date, created_by) VALUES (?, 'invoice_updated', ?, ?, NOW(), ?)");
        $activitySubject = "Invoice Updated: " . $invoiceNumber;
        $activityDescription = "Invoice updated with total £" . number_format($totalCost, 2);
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $activityStmt->bind_param("issi", $clientId, $activitySubject, $activityDescription, $userId);
        $activityStmt->execute();
        $activityStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Invoice updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Invoice not found or no changes made']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>