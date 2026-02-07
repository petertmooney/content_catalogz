<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';
include '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$quote_id = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($quote_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quote ID']);
    exit;
}

$valid_statuses = ['new', 'contacted', 'in_progress', 'completed', 'declined'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
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
