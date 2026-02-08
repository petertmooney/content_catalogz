<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$company = trim($data['company'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$serviceType = trim($data['service_type'] ?? '');
$status = trim($data['status'] ?? 'new');
$notes = trim($data['notes'] ?? '');

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM quotes WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A client with this email already exists']);
    exit;
}
$stmt->close();

// Insert the new client into quotes table - use correct column names
$stmt = $conn->prepare("INSERT INTO quotes (name, email, company, phone, address_street, service, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssssss", $name, $email, $company, $phone, $address, $serviceType, $status, $notes);

if ($stmt->execute()) {
    $clientId = $conn->insert_id;
    
    // Log the activity
    $userId = $_SESSION['user_id'] ?? 1;
    $activityStmt = $conn->prepare("INSERT INTO activities (client_id, activity_type, subject, description, activity_date, created_by) VALUES (?, 'note', ?, ?, NOW(), ?)");
    $activitySubject = "Client Added";
    $activityDesc = "New client " . $name . " was added manually.";
    $activityStmt->bind_param("issi", $clientId, $activitySubject, $activityDesc, $userId);
    $activityStmt->execute();
    $activityStmt->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Client added successfully',
        'client_id' => $clientId
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add client: ' . $conn->error]);
}

$stmt->close();
$conn->close();
