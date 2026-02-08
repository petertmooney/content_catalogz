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
    
    $smtp_host = isset($data['smtp_host']) ? $data['smtp_host'] : '';
    $smtp_port = isset($data['smtp_port']) ? intval($data['smtp_port']) : 587;
    $smtp_username = isset($data['smtp_username']) ? $data['smtp_username'] : '';
    $smtp_password = isset($data['smtp_password']) ? $data['smtp_password'] : '';
    $smtp_encryption = isset($data['smtp_encryption']) ? $data['smtp_encryption'] : 'tls';
    $smtp_from_email = isset($data['smtp_from_email']) ? $data['smtp_from_email'] : '';
    
    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        throw new Exception('SMTP host, username, and password are required');
    }
    
    // Check if PHPMailer is available (it may not be installed)
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Simple test using fsockopen
        $errno = 0;
        $errstr = '';
        
        // Try to connect to SMTP server
        $connection = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
        
        if (!$connection) {
            throw new Exception("Cannot connect to SMTP server: $errstr ($errno)");
        }
        
        fclose($connection);
        
        echo json_encode([
            'success' => true,
            'message' => 'SMTP server is reachable. Connection test successful!',
            'note' => 'Full email sending requires PHPMailer library. Connection to server verified.'
        ]);
    } else {
        // Use PHPMailer if available
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->Port = $smtp_port;
        
        if ($smtp_encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($smtp_encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Recipients
        $mail->setFrom($smtp_from_email, 'Content Catalogz Test');
        $mail->addAddress($smtp_from_email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email from Content Catalogz';
        $mail->Body = 'This is a test email to verify your SMTP settings are working correctly.';
        
        $mail->send();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test email sent successfully!'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
