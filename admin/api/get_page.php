<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';
include '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Page ID is required']);
    exit();
}

$sql = "SELECT * FROM pages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $page = $result->fetch_assoc();
    echo json_encode(['success' => true, 'page' => $page]);
} else {
    echo json_encode(['success' => false, 'message' => 'Page not found']);
}

$stmt->close();
$conn->close();
?>
