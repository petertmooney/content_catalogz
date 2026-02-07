<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once '../config/auth.php';
require_once '../config/db.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchDate = isset($_GET['date']) ? trim($_GET['date']) : '';

// Build the SQL query
$sql = "SELECT i.*, q.name, q.company, q.email, q.phone 
        FROM invoices i 
        LEFT JOIN quotes q ON i.client_id = q.id 
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($searchQuery)) {
    $sql .= " AND (i.invoice_number LIKE ? OR q.name LIKE ? OR q.company LIKE ? OR q.email LIKE ?)";
    $searchParam = '%' . $searchQuery . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ssss';
}

if (!empty($searchDate)) {
    $sql .= " AND i.invoice_date = ?";
    $params[] = $searchDate;
    $types .= 's';
}

$sql .= " ORDER BY i.invoice_date DESC, i.created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$invoices = [];
while ($row = $result->fetch_assoc()) {
    $invoices[] = $row;
}

echo json_encode([
    'success' => true,
    'invoices' => $invoices,
    'count' => count($invoices)
]);

$stmt->close();
$conn->close();
