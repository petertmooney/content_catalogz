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

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (!is_array($data)) throw new Exception('Invalid JSON payload');

    $colors = isset($data['lead_source_colors']) && is_array($data['lead_source_colors']) ? $data['lead_source_colors'] : [];
    $encoded = json_encode($colors);

    // Create table if missing
    $create_table_sql = "CREATE TABLE IF NOT EXISTS crm_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_source_colors TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$conn->query($create_table_sql)) throw new Exception('Failed to create crm_settings: ' . $conn->error);

    // Upsert single row
    $check_sql = "SELECT id FROM crm_settings LIMIT 1";
    $res = $conn->query($check_sql);
    if ($res && $res->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE crm_settings SET lead_source_colors = ? WHERE id = 1");
        $stmt->bind_param('s', $encoded);
    } else {
        $stmt = $conn->prepare("INSERT INTO crm_settings (lead_source_colors) VALUES (?)");
        $stmt->bind_param('s', $encoded);
    }

    if (!$stmt->execute()) throw new Exception('Failed to save CRM settings: ' . $stmt->error);

    echo json_encode(['success' => true, 'message' => 'CRM settings saved']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
