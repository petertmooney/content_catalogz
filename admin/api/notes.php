<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch notes for a client
if ($method === 'GET') {
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
    
    if (!$clientId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Client ID required']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT * FROM client_notes WHERE client_id = ? ORDER BY is_important DESC, created_at DESC");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
    
    echo json_encode(['success' => true, 'notes' => $notes]);
    $stmt->close();
}

// POST - Create new note
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['client_id']) || !isset($data['note'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Client ID and note are required']);
        exit;
    }
    
    $clientId = intval($data['client_id']);
    $note = trim($data['note']);
    $isImportant = isset($data['is_important']) ? (bool)$data['is_important'] : false;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $stmt = $conn->prepare("INSERT INTO client_notes (client_id, note_text, is_important, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $clientId, $note, $isImportant, $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note added successfully', 'id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add note: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// PUT - Update note
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note ID required']);
        exit;
    }
    
    $noteId = intval($data['id']);
    $note = isset($data['note']) ? trim($data['note']) : null;
    $isImportant = isset($data['is_important']) ? (bool)$data['is_important'] : null;
    
    $updates = [];
    $params = [];
    $types = '';
    
    if ($note !== null) {
        $updates[] = "note_text = ?";
        $params[] = $note;
        $types .= 's';
    }
    if ($isImportant !== null) {
        $updates[] = "is_important = ?";
        $params[] = $isImportant;
        $types .= 'i';
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    $params[] = $noteId;
    $types .= 'i';
    
    $sql = "UPDATE client_notes SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update note']);
    }
    
    $stmt->close();
}

// DELETE - Remove note
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $noteId = isset($data['id']) ? intval($data['id']) : null;
    
    if (!$noteId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note ID required']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM client_notes WHERE id = ?");
    $stmt->bind_param("i", $noteId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete note']);
    }
    
    $stmt->close();
}

$conn->close();
