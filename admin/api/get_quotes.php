<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';
include '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM quotes WHERE 1=1";
$params = [];
$types = '';

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR company LIKE ? OR message LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$quotes = [];
while ($row = $result->fetch_assoc()) {
    $quotes[] = $row;
}

// Get stats
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
FROM quotes";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

echo json_encode([
    'success' => true,
    'quotes' => $quotes,
    'stats' => $stats
]);

$stmt->close();
$conn->close();
?>
