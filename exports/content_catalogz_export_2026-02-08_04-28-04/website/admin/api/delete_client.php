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

if (!isset($data['client_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Client ID is required']);
    exit;
}

$clientId = intval($data['client_id']);

if ($clientId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete invoices linked to the client
    $stmt = $conn->prepare("DELETE FROM invoices WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $invoicesDeleted = $stmt->affected_rows;
    $stmt->close();

    // Delete activities (should cascade, but delete explicitly for safety)
    $stmt = $conn->prepare("DELETE FROM activities WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $activitiesDeleted = $stmt->affected_rows;
    $stmt->close();

    // Delete tasks (should set null, but we'll delete tasks linked to this client)
    $stmt = $conn->prepare("DELETE FROM tasks WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $tasksDeleted = $stmt->affected_rows;
    $stmt->close();

    // Delete client notes (should cascade, but delete explicitly for safety)
    $stmt = $conn->prepare("DELETE FROM client_notes WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $notesDeleted = $stmt->affected_rows;
    $stmt->close();

    // Delete client tags (should cascade, but delete explicitly for safety)
    $stmt = $conn->prepare("DELETE FROM client_tags WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $tagsDeleted = $stmt->affected_rows;
    $stmt->close();

    // Finally, delete the client from quotes table
    $stmt = $conn->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Client not found');
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Client and all related data deleted successfully',
        'deleted' => [
            'invoices' => $invoicesDeleted,
            'activities' => $activitiesDeleted,
            'tasks' => $tasksDeleted,
            'notes' => $notesDeleted,
            'tags' => $tagsDeleted
        ]
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting client: ' . $e->getMessage()]);
}

$conn->close();
