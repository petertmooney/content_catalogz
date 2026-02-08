<?php
// admin/api/delete_invoice.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$invoiceId = isset($data['invoice_id']) ? intval($data['invoice_id']) : 0;

if ($invoiceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM invoices WHERE id = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $invoiceId);
$success = $stmt->execute();
if ($success && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invoice not found or could not be deleted']);
}
$stmt->close();
$conn->close();
