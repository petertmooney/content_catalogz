<?php
// Public endpoint - no authentication required
header('Content-Type: application/json');

// Include database only (no auth needed for quote submission)
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
if (empty($name) || empty($email) || empty($message) || empty($service)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields (name, email, service, and message).'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

// Insert quote into database
$sql = "INSERT INTO quotes (name, email, company, phone, service, message, status) VALUES (?, ?, ?, ?, ?, ?, 'new')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please try again later.'
    ]);
    exit;
}

$stmt->bind_param("ssssss", $name, $email, $company, $phone, $service, $message);

if ($stmt->execute()) {
    $quote_id = $stmt->insert_id;
    
    // You could send an email notification here
    // mail('admin@contentcatalogz.com', 'New Quote Request', ...);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your quote request! We will contact you within 24 hours.',
        'quote_id' => $quote_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit quote. Please try again later.'
    ]);
}

$stmt->close();
$conn->close();
?>
