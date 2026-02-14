<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header first
header('Content-Type: application/json');

// Database configuration (don't use db.php to avoid HTML error messages)
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

if (!isset($data['client_id']) || !isset($data['invoice_number'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$clientId = intval($data['client_id']);
$invoiceNumber = trim($data['invoice_number']);
$invoiceDate = isset($data['invoice_date']) ? $data['invoice_date'] : date('Y-m-d');
$totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
$totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;
$services = isset($data['services']) ? $data['services'] : [];
$totalRemaining = $totalCost - $totalPaid;

// Calculate due date (30 days from invoice date)
$dueDate = date('Y-m-d', strtotime($invoiceDate . ' + 30 days'));

// Insert new invoice
$stmt = $conn->prepare("INSERT INTO invoices (invoice_number, client_id, invoice_date, issue_date, due_date, amount, total_cost, total_paid, total_remaining, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent')");
$stmt->bind_param("sisssdddd", $invoiceNumber, $clientId, $invoiceDate, $invoiceDate, $dueDate, $totalCost, $totalCost, $totalPaid, $totalRemaining);

if ($stmt->execute()) {
    $invoiceId = $conn->insert_id;
    
    // Save services if provided
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
    
    echo json_encode([
        'success' => true, 
        'message' => 'Invoice saved successfully',
        'invoice_id' => $invoiceId
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
