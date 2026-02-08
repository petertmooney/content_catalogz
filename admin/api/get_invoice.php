<?php
// admin/api/get_invoice.php
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid invoice ID']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$invoiceId = intval($_GET['id']);

$stmt = $conn->prepare('SELECT * FROM invoices WHERE id = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'invoice' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
}
$stmt->close();
$conn->close();
