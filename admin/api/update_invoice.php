<?php
// admin/api/update_invoice.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$invoiceId = isset($data['id']) ? intval($data['id']) : 0;

if ($invoiceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
    exit;
}

// Only allow updating certain fields
$fields = ['invoice_number', 'invoice_date', 'total_cost', 'total_paid', 'total_remaining', 'status', 'notes'];
$set = [];
$params = [];
$types = '';
foreach ($fields as $field) {
    if (isset($data[$field])) {
        $set[] = "$field = ?";
        $params[] = $data[$field];
        $types .= is_numeric($data[$field]) ? 'd' : 's';
    }
}
if (empty($set)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit;
}
$params[] = $invoiceId;
$types .= 'i';

$sql = 'UPDATE invoices SET ' . implode(', ', $set) . ' WHERE id = ?';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param($types, ...$params);
$success = $stmt->execute();
if ($success && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invoice not updated or no changes made']);
}
$stmt->close();
$conn->close();
