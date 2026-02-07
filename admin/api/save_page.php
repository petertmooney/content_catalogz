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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$id = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$content = $_POST['content'] ?? '';
$page_type = trim($_POST['page_type'] ?? 'standard');
$status = trim($_POST['status'] ?? 'draft');

// Validation
$errors = [];

if (empty($title)) {
    $errors[] = 'Title is required';
}

if (empty($slug)) {
    $errors[] = 'Slug is required';
}

if (empty($content)) {
    $errors[] = 'Content is required';
}

// Check if slug already exists (for new pages, or if slug changed for existing)
$slug_check_sql = "SELECT id FROM pages WHERE slug = ?";
if ($id) {
    $slug_check_sql .= " AND id != ?";
}
$slug_stmt = $conn->prepare($slug_check_sql);
if ($id) {
    $slug_stmt->bind_param("si", $slug, $id);
} else {
    $slug_stmt->bind_param("s", $slug);
}
$slug_stmt->execute();
if ($slug_stmt->get_result()->num_rows > 0) {
    $errors[] = 'This slug is already in use';
}
$slug_stmt->close();

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

if ($id) {
    // Update existing page
    $sql = "UPDATE pages SET title = ?, slug = ?, content = ?, page_type = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $title, $slug, $content, $page_type, $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Page updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating page: ' . $conn->error]);
    }
    $stmt->close();
} else {
    // Create new page
    $sql = "INSERT INTO pages (title, slug, content, page_type, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $title, $slug, $content, $page_type, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Page created successfully', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating page: ' . $conn->error]);
    }
    $stmt->close();
}

$conn->close();
?>
