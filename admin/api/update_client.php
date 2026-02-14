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

if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$clientId = intval($data['id']);

// Check if this is a payment-only update (only total_paid is provided)
$isPaymentOnly = isset($data['total_paid']) && count(array_diff(array_keys($data), ['id', 'total_paid'])) === 0;

if ($isPaymentOnly) {
    // Payment-only update: only update total_paid and total_remaining
    $totalPaid = floatval($data['total_paid']);
    
    // Get current total_cost to calculate remaining
    $fetchStmt = $conn->prepare("SELECT total_cost FROM quotes WHERE id = ?");
    $fetchStmt->bind_param("i", $clientId);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();
    $totalCost = 0.00;
    if ($row = $result->fetch_assoc()) {
        $totalCost = floatval($row['total_cost']);
    }
    $fetchStmt->close();
    
    $totalRemaining = $totalCost - $totalPaid;
    
    // Update only payment fields
    $stmt = $conn->prepare("UPDATE quotes SET total_paid = ?, total_remaining = ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ddi", $totalPaid, $totalRemaining, $clientId);
} else {
    // Full update: update all fields
    $name = isset($data['name']) ? trim($data['name']) : null;
    $company = isset($data['company']) ? trim($data['company']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $phone = isset($data['phone']) ? trim($data['phone']) : null;
    
    // Validate required fields
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    $addressStreet = isset($data['address_street']) ? trim($data['address_street']) : null;
    $addressLine2 = isset($data['address_line2']) ? trim($data['address_line2']) : null;
    $addressCity = isset($data['address_city']) ? trim($data['address_city']) : null;
    $addressCounty = isset($data['address_county']) ? trim($data['address_county']) : null;
    $addressPostcode = isset($data['address_postcode']) ? trim($data['address_postcode']) : null;
    $addressCountry = isset($data['address_country']) ? trim($data['address_country']) : 'United Kingdom';
    $services = isset($data['services']) ? $data['services'] : [];
    
    // Validate services array
    if (!is_array($services)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Services must be an array']);
        exit;
    }
    
    // Clean up services array - remove invalid entries
    $validServices = [];
    foreach ($services as $service) {
        if (is_array($service) && isset($service['name']) && isset($service['cost']) && 
            !empty(trim($service['name'])) && is_numeric($service['cost'])) {
            $validServices[] = [
                'name' => trim($service['name']),
                'cost' => floatval($service['cost'])
            ];
        }
    }
    $services = $validServices;
    $totalCost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
    $totalPaid = isset($data['total_paid']) ? floatval($data['total_paid']) : 0.00;
    $leadSource = isset($data['lead_source']) ? trim($data['lead_source']) : null;

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
    if ($servicesJson === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid services data']);
        exit;
    }

    // Update the client information
    $stmt = $conn->prepare("UPDATE quotes SET name = ?, company = ?, email = ?, phone = ?, address_street = ?, address_line2 = ?, address_city = ?, address_county = ?, address_postcode = ?, address_country = ?, lead_source = ?, services = ?, total_cost = ?, total_paid = ?, total_remaining = ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssssssssdddi", $name, $company, $email, $phone, $addressStreet, $addressLine2, $addressCity, $addressCounty, $addressPostcode, $addressCountry, $leadSource, $servicesJson, $totalCost, $totalPaid, $totalRemaining, $clientId);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database execute error: ' . $stmt->error]);
        exit;
    }
    
    // Log activity if services changed (only for full updates)
    if (!$isPaymentOnly) {
        try {
            $servicesChanged = false;
            $activityDescription = '';
            
            // Check if services were added or modified
            $newServiceNames = is_array($services) ? array_column($services, 'name') : [];
            $oldServiceNames = is_array($oldServices) ? array_column($oldServices, 'name') : [];
            
            // Find added services
            $addedServices = array_diff($newServiceNames, $oldServiceNames);
            
            if (!empty($addedServices)) {
                $servicesChanged = true;
                $servicesList = implode(', ', $addedServices);
                $activityDescription = "New service(s) added: " . $servicesList;
                
                // Log the activity
                $userId = $_SESSION['user_id'] ?? 1;
                $activityStmt = $conn->prepare("INSERT INTO activities (client_id, type, subject, description, activity_date, created_by) VALUES (?, 'note', ?, ?, NOW(), ?)");
                if ($activityStmt) {
                    $activitySubject = "Services Updated";
                    $activityStmt->bind_param("issi", $clientId, $activitySubject, $activityDescription, $userId);
                    $activityStmt->execute();
                    $activityStmt->close();
                }
            }
        } catch (Exception $e) {
            // Log activity logging error but don't fail the update
            error_log("Activity logging error: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Client information updated successfully',
        'total_remaining' => number_format($totalRemaining, 2)
    ]);
}

$stmt->close();
$conn->close();
