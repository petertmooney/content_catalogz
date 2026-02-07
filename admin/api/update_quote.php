<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$quote_id = intval($_POST['id'] ?? 0);
$status = strtolower(trim($_POST['status'] ?? ''));
$notes = trim($_POST['notes'] ?? '');

if ($quote_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quote ID']);
    exit;
}

// Normalize status - handle spaces and various formats
$status = str_replace(' ', '_', $status);
$status = str_replace('-', '_', $status);

$valid_statuses = ['new', 'contacted', 'in_progress', 'completed', 'declined'];
if (empty($status) || !in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status: "' . $status . '". Valid options: ' . implode(', ', $valid_statuses)]);
    exit;
}

$sql = "UPDATE quotes SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $status, $notes, $quote_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Quote updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update quote'
    ]);
}

$stmt->close();
$conn->close();
?>
