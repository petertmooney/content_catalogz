<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

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
$totalRemaining = $totalCost - $totalPaid;

// Check if invoice number already exists
$checkStmt = $conn->prepare("SELECT id FROM invoices WHERE invoice_number = ?");
$checkStmt->bind_param("s", $invoiceNumber);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Invoice already exists, just return success
    echo json_encode(['success' => true, 'message' => 'Invoice already exists', 'exists' => true]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insert new invoice
$stmt = $conn->prepare("INSERT INTO invoices (invoice_number, client_id, invoice_date, total_cost, total_paid, total_remaining) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisddd", $invoiceNumber, $clientId, $invoiceDate, $totalCost, $totalPaid, $totalRemaining);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Invoice saved successfully',
        'invoice_id' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
