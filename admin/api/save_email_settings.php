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
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
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
    
    if (!$conn->query($create_table_sql)) {
        throw new Exception('Failed to create email_settings table: ' . $conn->error);
    }
    
    // Check if settings exist
    $check_sql = "SELECT id FROM email_settings LIMIT 1";
    $result = $conn->query($check_sql);
    
    $smtp_host = isset($data['smtp_host']) ? $data['smtp_host'] : '';
    $smtp_port = isset($data['smtp_port']) ? intval($data['smtp_port']) : 587;
    $smtp_username = isset($data['smtp_username']) ? $data['smtp_username'] : '';
    $smtp_password = isset($data['smtp_password']) ? $data['smtp_password'] : '';
    $smtp_encryption = isset($data['smtp_encryption']) ? $data['smtp_encryption'] : 'tls';
    $smtp_from_email = isset($data['smtp_from_email']) ? $data['smtp_from_email'] : '';
    $smtp_from_name = isset($data['smtp_from_name']) ? $data['smtp_from_name'] : 'Content Catalogz';
    $enable_notifications = isset($data['enable_notifications']) && $data['enable_notifications'] ? 1 : 0;
    $enable_auto_reply = isset($data['enable_auto_reply']) && $data['enable_auto_reply'] ? 1 : 0;
    $notification_email = isset($data['notification_email']) ? $data['notification_email'] : '';
    $auto_reply_template = isset($data['auto_reply_template']) ? $data['auto_reply_template'] : '';
    
    if ($result && $result->num_rows > 0) {
        // Update existing settings
        $stmt = $conn->prepare("UPDATE email_settings SET 
            smtp_host = ?, 
            smtp_port = ?, 
            smtp_username = ?, 
            smtp_password = ?, 
            smtp_encryption = ?, 
            smtp_from_email = ?, 
            smtp_from_name = ?,
            enable_notifications = ?,
            enable_auto_reply = ?,
            notification_email = ?,
            auto_reply_template = ?
            WHERE id = 1");
        
        $stmt->bind_param("sisssssiiss", 
            $smtp_host, 
            $smtp_port, 
            $smtp_username, 
            $smtp_password, 
            $smtp_encryption, 
            $smtp_from_email, 
            $smtp_from_name,
            $enable_notifications,
            $enable_auto_reply,
            $notification_email,
            $auto_reply_template
        );
    } else {
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO email_settings 
            (smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, smtp_from_email, smtp_from_name, enable_notifications, enable_auto_reply, notification_email, auto_reply_template) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sisssssiiss", 
            $smtp_host, 
            $smtp_port, 
            $smtp_username, 
            $smtp_password, 
            $smtp_encryption, 
            $smtp_from_email, 
            $smtp_from_name,
            $enable_notifications,
            $enable_auto_reply,
            $notification_email,
            $auto_reply_template
        );
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save email settings: ' . $stmt->error);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Email settings saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
