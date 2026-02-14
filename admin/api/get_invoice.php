<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invoice ID is required']);
    exit;
}

$invoiceId = (int)$_GET['id'];

try {
    // First, update any overdue invoices
    $updateOverdueSql = "UPDATE invoices 
                        SET status = 'overdue' 
                        WHERE status != 'paid' 
                        AND status != 'overdue' 
                        AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $conn->query($updateOverdueSql);
    
    // Get invoice details with client information
    $sql = "SELECT i.*, q.name, q.company, q.email, q.phone,
                   q.address_street, q.address_line2, q.address_city,
                   q.address_county, q.address_postcode, q.address_country
            FROM invoices i
            LEFT JOIN quotes q ON i.client_id = q.id
            WHERE i.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit;
    }

    $invoice = $result->fetch_assoc();

    // Get invoice services/items
    $servicesSql = "SELECT * FROM invoice_services WHERE invoice_id = ? ORDER BY id";
    $servicesStmt = $conn->prepare($servicesSql);
    $servicesStmt->bind_param('i', $invoiceId);
    $servicesStmt->execute();
    $servicesResult = $servicesStmt->get_result();

    $services = [];
    while ($service = $servicesResult->fetch_assoc()) {
        $services[] = $service;
    }

    $invoice['services'] = $services;

    echo json_encode([
        'success' => true,
        'invoice' => $invoice
    ]);

} catch (Exception $e) {
    error_log('Error fetching invoice: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch invoice details']);
}
?>