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
file_put_contents('/tmp/save_invoice_debug.log', date('Y-m-d H:i:s') . " - Raw input: " . $rawInput . "\n", FILE_APPEND);
$data = json_decode($rawInput, true);
file_put_contents('/tmp/save_invoice_debug.log', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);
file_put_contents('/tmp/save_invoice_debug.log', "JSON error: " . json_last_error_msg() . "\n\n", FILE_APPEND);

if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('/tmp/save_invoice_debug.log', "ERROR: Invalid JSON\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

file_put_contents('/tmp/save_invoice_debug.log', "JSON validation passed\n", FILE_APPEND);

if (!isset($data['client_id']) || !isset($data['invoice_number'])) {
    file_put_contents('/tmp/save_invoice_debug.log', "ERROR: Missing fields\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

file_put_contents('/tmp/save_invoice_debug.log', "Required fields present\n", FILE_APPEND);

$clientId = intval($data['client_id']);
$invoiceNumber = trim($data['invoice_number']);
$invoiceDate = isset($data['invoice_date']) ? $data['invoice_date'] : date('Y-m-d');
$totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
$totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;
$totalRemaining = $totalCost - $totalPaid;

file_put_contents('/tmp/save_invoice_debug.log', "Variables extracted: clientId=$clientId, invoiceNumber=$invoiceNumber\n", FILE_APPEND);

// Check if invoice number already exists
$checkStmt = $conn->prepare("SELECT id FROM invoices WHERE invoice_number = ?");
file_put_contents('/tmp/save_invoice_debug.log', "Prepare statement created\n", FILE_APPEND);
$checkStmt->bind_param("s", $invoiceNumber);
file_put_contents('/tmp/save_invoice_debug.log', "Parameters bound\n", FILE_APPEND);
$checkStmt->execute();
file_put_contents('/tmp/save_invoice_debug.log', "Query executed\n", FILE_APPEND);
$result = $checkStmt->get_result();
file_put_contents('/tmp/save_invoice_debug.log', "Result retrieved, num_rows: " . $result->num_rows . "\n", FILE_APPEND);

if ($result->num_rows > 0) {
    // Invoice already exists, just return success
    file_put_contents('/tmp/save_invoice_debug.log', "Invoice exists, returning success\n", FILE_APPEND);
    echo json_encode(['success' => true, 'message' => 'Invoice already exists', 'exists' => true]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

file_put_contents('/tmp/save_invoice_debug.log', "Invoice doesn't exist, inserting new one\n", FILE_APPEND);

// Calculate due date (30 days from invoice date)
$dueDate = date('Y-m-d', strtotime($invoiceDate . ' + 30 days'));

// Insert new invoice
$stmt = $conn->prepare("INSERT INTO invoices (invoice_number, client_id, invoice_date, issue_date, due_date, amount, total_cost, total_paid, total_remaining, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent')");
file_put_contents('/tmp/save_invoice_debug.log', "Insert statement prepared\n", FILE_APPEND);
$stmt->bind_param("sisssdddd", $invoiceNumber, $clientId, $invoiceDate, $invoiceDate, $dueDate, $totalCost, $totalCost, $totalPaid, $totalRemaining);
file_put_contents('/tmp/save_invoice_debug.log', "Insert parameters bound\n", FILE_APPEND);

if ($stmt->execute()) {
    file_put_contents('/tmp/save_invoice_debug.log', "Insert successful, insert_id: " . $conn->insert_id . "\n", FILE_APPEND);
    echo json_encode([
        'success' => true, 
        'message' => 'Invoice saved successfully',
        'invoice_id' => $conn->insert_id
    ]);
    file_put_contents('/tmp/save_invoice_debug.log', "JSON response echoed\n", FILE_APPEND);
} else {
    file_put_contents('/tmp/save_invoice_debug.log', "Insert failed: " . $stmt->error . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
