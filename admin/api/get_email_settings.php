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
    // Create email_settings table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS email_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        smtp_host VARCHAR(255),
        smtp_port INT,
        smtp_username VARCHAR(255),
        smtp_password VARCHAR(255),
        smtp_encryption VARCHAR(10) DEFAULT 'tls',
        smtp_from_email VARCHAR(255),
        smtp_from_name VARCHAR(255),
        enable_notifications TINYINT(1) DEFAULT 0,
        enable_auto_reply TINYINT(1) DEFAULT 0,
        notification_email VARCHAR(255),
        auto_reply_template TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->query($create_table_sql);
    
    // Get email settings
    $sql = "SELECT * FROM email_settings LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $settings = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);
    } else {
        // Return default settings
        echo json_encode([
            'success' => true,
            'settings' => [
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'smtp_from_email' => '',
                'smtp_from_name' => 'Content Catalogz',
                'enable_notifications' => 0,
                'enable_auto_reply' => 0,
                'notification_email' => '',
                'auto_reply_template' => 'Thank you for your quote request. We will get back to you within 24 hours.'
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
