<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$clientId = intval($data['id']);
$addressStreet = isset($data['address_street']) ? trim($data['address_street']) : null;
$addressLine2 = isset($data['address_line2']) ? trim($data['address_line2']) : null;
$addressCity = isset($data['address_city']) ? trim($data['address_city']) : null;
$addressCounty = isset($data['address_county']) ? trim($data['address_county']) : null;
$addressPostcode = isset($data['address_postcode']) ? trim($data['address_postcode']) : null;
$addressCountry = isset($data['address_country']) ? trim($data['address_country']) : 'United Kingdom';
$services = isset($data['services']) ? $data['services'] : [];
$totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
$totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;

// Fetch old services to compare for activity logging
$oldServices = [];
$fetchStmt = $conn->prepare("SELECT services FROM quotes WHERE id = ?");
$fetchStmt->bind_param("i", $clientId);
$fetchStmt->execute();
$result = $fetchStmt->get_result();
if ($row = $result->fetch_assoc()) {
    $oldServicesJson = $row['services'];
    $oldServices = $oldServicesJson ? json_decode($oldServicesJson, true) : [];
}
$fetchStmt->close();

// Calculate total remaining
$totalRemaining = $totalCost - $totalPaid;

// Convert services array to JSON
$servicesJson = json_encode($services);

// Update the client information
$stmt = $conn->prepare("UPDATE quotes SET address_street = ?, address_line2 = ?, address_city = ?, address_county = ?, address_postcode = ?, address_country = ?, services = ?, total_cost = ?, total_paid = ?, total_remaining = ? WHERE id = ?");
$stmt->bind_param("sssssssdddi", $addressStreet, $addressLine2, $addressCity, $addressCounty, $addressPostcode, $addressCountry, $servicesJson, $totalCost, $totalPaid, $totalRemaining, $clientId);

if ($stmt->execute()) {
    // Log activity if services changed
    $servicesChanged = false;
    $activityDescription = '';
    
    // Check if services were added or modified
    $newServiceNames = array_column($services, 'name');
    $oldServiceNames = array_column($oldServices, 'name');
    
    // Find added services
    $addedServices = array_diff($newServiceNames, $oldServiceNames);
    
    if (!empty($addedServices)) {
        $servicesChanged = true;
        $servicesList = implode(', ', $addedServices);
        $activityDescription = "New service(s) added: " . $servicesList;
        
        // Log the activity
        $userId = $_SESSION['user_id'] ?? 1;
        $activityStmt = $conn->prepare("INSERT INTO activities (client_id, activity_type, subject, description, activity_date, created_by) VALUES (?, 'note', ?, ?, NOW(), ?)");
        $activitySubject = "Services Updated";
        $activityStmt->bind_param("issi", $clientId, $activitySubject, $activityDescription, $userId);
        $activityStmt->execute();
        $activityStmt->close();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Client information updated successfully',
        'total_remaining' => number_format($totalRemaining, 2)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
