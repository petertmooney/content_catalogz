<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch activities for a client
if ($method === 'GET') {
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
    
    if ($clientId) {
        $stmt = $conn->prepare("SELECT * FROM activities WHERE client_id = ? ORDER BY activity_date DESC");
        $stmt->bind_param("i", $clientId);
    } else {
        // Get all recent activities
        $stmt = $conn->prepare("SELECT a.*, q.name as client_name FROM activities a LEFT JOIN quotes q ON a.client_id = q.id ORDER BY a.activity_date DESC LIMIT 50");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode(['success' => true, 'activities' => $activities]);
    $stmt->close();
}

// POST - Create new activity
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['client_id']) || !isset($data['activity_type']) || !isset($data['subject'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $clientId = intval($data['client_id']);
    $activityType = $data['activity_type'];
    $subject = trim($data['subject']);
    $description = isset($data['description']) ? trim($data['description']) : null;
    $activityDate = isset($data['activity_date']) ? $data['activity_date'] : date('Y-m-d H:i:s');
    $duration = isset($data['duration_minutes']) ? intval($data['duration_minutes']) : 0;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $stmt = $conn->prepare("INSERT INTO activities (client_id, activity_type, subject, description, activity_date, duration_minutes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssii", $clientId, $activityType, $subject, $description, $activityDate, $duration, $userId);
    
    if ($stmt->execute()) {
        // Update last_contact_date in quotes table
        $updateStmt = $conn->prepare("UPDATE quotes SET last_contact_date = CURDATE() WHERE id = ?");
        $updateStmt->bind_param("i", $clientId);
        $updateStmt->execute();
        $updateStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Activity logged successfully', 'id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to log activity: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// DELETE - Remove activity
elseif ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $activityId = isset($data['id']) ? intval($data['id']) : null;
    
    if (!$activityId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Activity ID required']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
    $stmt->bind_param("i", $activityId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete activity']);
    }
    
    $stmt->close();
}

$conn->close();
