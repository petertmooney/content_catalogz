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
    // Single-row table to store CRM settings as JSON
    $create_table_sql = "CREATE TABLE IF NOT EXISTS crm_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_source_colors TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $conn->query($create_table_sql);

    $sql = "SELECT * FROM crm_settings LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $colors = [];
        if (!empty($row['lead_source_colors'])) {
            $decoded = json_decode($row['lead_source_colors'], true);
            if (is_array($decoded)) $colors = $decoded;
        }

        echo json_encode(['success' => true, 'settings' => ['lead_source_colors' => $colors]]);
    } else {
        echo json_encode(['success' => true, 'settings' => ['lead_source_colors' => (object)[]]]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
