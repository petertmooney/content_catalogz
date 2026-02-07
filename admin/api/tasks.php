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

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch tasks
if ($method === 'GET') {
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    $sql = "SELECT t.*, q.name as client_name, q.company FROM tasks t LEFT JOIN quotes q ON t.client_id = q.id WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($clientId) {
        $sql .= " AND t.client_id = ?";
        $params[] = $clientId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $sql .= " ORDER BY 
        CASE t.priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
        END,
        t.due_date ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    echo json_encode(['success' => true, 'tasks' => $tasks]);
    $stmt->close();
}

// POST - Create new task
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit;
    }
    
    $clientId = isset($data['client_id']) ? intval($data['client_id']) : null;
    $title = trim($data['title']);
    $description = isset($data['description']) ? trim($data['description']) : null;
    $status = isset($data['status']) ? $data['status'] : 'pending';
    $priority = isset($data['priority']) ? $data['priority'] : 'medium';
    $dueDate = isset($data['due_date']) ? $data['due_date'] : null;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $stmt = $conn->prepare("INSERT INTO tasks (client_id, title, description, status, priority, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $clientId, $title, $description, $status, $priority, $dueDate, $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task created successfully', 'id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create task: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// PUT - Update task
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Task ID required']);
        exit;
    }
    
    $taskId = intval($data['id']);
    $updates = [];
    $params = [];
    $types = '';
    
    if (isset($data['title'])) {
        $updates[] = "title = ?";
        $params[] = trim($data['title']);
        $types .= 's';
    }
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = trim($data['description']);
        $types .= 's';
    }
    if (isset($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
        $types .= 's';
        
        if ($data['status'] === 'completed') {
            $updates[] = "completed_at = NOW()";
        }
    }
    if (isset($data['priority'])) {
        $updates[] = "priority = ?";
        $params[] = $data['priority'];
        $types .= 's';
    }
    if (isset($data['due_date'])) {
        $updates[] = "due_date = ?";
        $params[] = $data['due_date'];
        $types .= 's';
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    $params[] = $taskId;
    $types .= 'i';
    
    $sql = "UPDATE tasks SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update task']);
    }
    
    $stmt->close();
}

// DELETE - Remove task
elseif ($method === 'DELETE') {
    // Check query parameter first, then body
    $taskId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$taskId) {
        // Fallback: try JSON body
        $data = json_decode(file_get_contents("php://input"), true);
        $taskId = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$taskId) {
        // Fallback: try form-urlencoded body
        parse_str(file_get_contents("php://input"), $data);
        $taskId = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$taskId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Task ID required']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
    }
    
    $stmt->close();
}

$conn->close();
