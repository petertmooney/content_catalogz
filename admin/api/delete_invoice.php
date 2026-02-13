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

if (!isset($_POST['invoice_id']) || empty($_POST['invoice_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invoice ID is required']);
    exit;
}

$invoiceId = (int)$_POST['invoice_id'];

try {
    // First delete invoice services
    $deleteServicesSql = "DELETE FROM invoice_services WHERE invoice_id = ?";
    $servicesStmt = $conn->prepare($deleteServicesSql);
    $servicesStmt->bind_param('i', $invoiceId);
    $servicesStmt->execute();

    // Then delete the invoice
    $deleteInvoiceSql = "DELETE FROM invoices WHERE id = ?";
    $invoiceStmt = $conn->prepare($deleteInvoiceSql);
    $invoiceStmt->bind_param('i', $invoiceId);

    if ($invoiceStmt->execute()) {
        if ($invoiceStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        }
    } else {
        throw new Exception('Failed to delete invoice');
    }

} catch (Exception $e) {
    error_log('Error deleting invoice: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete invoice']);
}
?>