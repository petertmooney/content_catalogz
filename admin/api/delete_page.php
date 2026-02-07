<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Page ID is required']);
    exit();
}

$sql = "DELETE FROM pages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Page deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Page not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting page: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
