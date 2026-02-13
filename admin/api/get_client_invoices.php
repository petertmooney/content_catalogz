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

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Client ID is required']);
    exit;
}

$clientId = (int)$_GET['client_id'];

try {
    // Get all invoices for this client
    $sql = "SELECT i.*, q.name, q.company, q.email, q.phone
            FROM invoices i
            LEFT JOIN quotes q ON i.client_id = q.id
            WHERE i.client_id = ?
            ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $clientId);
    $stmt->execute();
    $result = $stmt->get_result();

    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        // Get invoice services/items
        $servicesSql = "SELECT * FROM invoice_services WHERE invoice_id = ? ORDER BY id";
        $servicesStmt = $conn->prepare($servicesSql);
        $servicesStmt->bind_param('i', $row['id']);
        $servicesStmt->execute();
        $servicesResult = $servicesStmt->get_result();

        $services = [];
        while ($serviceRow = $servicesResult->fetch_assoc()) {
            $services[] = $serviceRow;
        }

        $row['services'] = $services;
        $invoices[] = $row;
    }

    echo json_encode([
        'success' => true,
        'invoices' => $invoices
    ]);

} catch (Exception $e) {
    error_log('Error in get_client_invoices.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>