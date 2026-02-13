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
$services = isset($_POST['services']) ? $_POST['services'] : '[]';
$total_cost = floatval($_POST['total_cost'] ?? 0);

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

// Get the current status before updating
$checkStmt = $conn->prepare("SELECT status, name FROM quotes WHERE id = ?");
if (!$checkStmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
$checkStmt->bind_param("i", $quote_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$currentData = $result->fetch_assoc();

if (!$currentData) {
    echo json_encode(['success' => false, 'message' => 'Quote not found']);
    exit;
}

$oldStatus = $currentData['status'] ?? '';
$clientName = $currentData['name'] ?? '';
$checkStmt->close();

$clientCreated = false;

// Check if status is changing to in_progress (creating a new client)
if ($status === 'in_progress' && $oldStatus !== 'in_progress' && $oldStatus !== 'completed') {
    $clientCreated = true;
}

// Check which columns exist in the quotes table
$columnsResult = $conn->query("DESCRIBE quotes");
$columns = [];
while ($col = $columnsResult->fetch_assoc()) {
    $columns[] = $col['Field'];
}

// Build update query based on available columns
$useFullUpdate = in_array('services', $columns) && in_array('total_cost', $columns);

if ($useFullUpdate) {
    // Include optional CRM fields if the columns exist
    $includeLead = in_array('lead_source', $columns);
    $includeExpected = in_array('expected_value', $columns);

    $sql = "UPDATE quotes SET status = ?, notes = ?, services = ?, total_cost = ?, updated_at = CURRENT_TIMESTAMP";
    if ($includeLead) $sql .= ", lead_source = ?";
    if ($includeExpected) $sql .= ", expected_value = ?";
    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    if ($includeLead && $includeExpected) {
        $lead = isset($_POST['lead_source']) ? trim($_POST['lead_source']) : null;
        $expected = isset($_POST['expected_value']) ? floatval($_POST['expected_value']) : 0.0;
        $stmt->bind_param("sssdsdi", $status, $notes, $services, $total_cost, $lead, $expected, $quote_id);
    } elseif ($includeLead) {
        $lead = isset($_POST['lead_source']) ? trim($_POST['lead_source']) : null;
        $stmt->bind_param("sssdsi", $status, $notes, $services, $total_cost, $lead, $quote_id);
    } elseif ($includeExpected) {
        $expected = isset($_POST['expected_value']) ? floatval($_POST['expected_value']) : 0.0;
        $stmt->bind_param("sssddi", $status, $notes, $services, $total_cost, $expected, $quote_id);
    } else {
        $stmt->bind_param("sssdi", $status, $notes, $services, $total_cost, $quote_id);
    }
} else {
    // Simple update with only status and notes
    $sql = "UPDATE quotes SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssi", $status, $notes, $quote_id);
}

if ($stmt->execute()) {
    // If a new client was created, log the activity (if activities table exists)
    if ($clientCreated) {
        // Check if activities table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'activities'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $userId = $_SESSION['user_id'] ?? null;
            $activityType = 'note';
            $activitySubject = 'Client created from quote';
            $activityDescription = "Quote converted to active client. Status changed to In Progress.";
            $activityDate = date('Y-m-d H:i:s');
            
            $actStmt = $conn->prepare("INSERT INTO activities (client_id, type, subject, description, activity_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            if ($actStmt) {
                $actStmt->bind_param("issssi", $quote_id, $activityType, $activitySubject, $activityDescription, $activityDate, $userId);
                $actStmt->execute();
                $actStmt->close();
            }
        }
    }

    // Invalidate CRM cache so dashboard reflects this update immediately
    require_once __DIR__ . '/../config/cache.php';
    invalidate_crm_cache();
    
    echo json_encode([
        'success' => true,
        'message' => $clientCreated ? 'Quote updated and client created successfully' : 'Quote updated successfully',
        'client_created' => $clientCreated,
        'client_id' => $clientCreated ? $quote_id : null,
        'client_name' => $clientCreated ? $clientName : null
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update quote: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
