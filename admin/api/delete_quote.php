<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['quote_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quote ID is required']);
    exit;
}

$quoteId = intval($data['quote_id']);

if ($quoteId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid quote ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Note: This deletes just the quote request, not a full client with invoices etc.
    // The foreign key constraints should handle related data if any
    
    // Delete the quote
    $stmt = $conn->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->bind_param("i", $quoteId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Quote not found');
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Quote deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting quote: ' . $e->getMessage()]);
}

$conn->close();
