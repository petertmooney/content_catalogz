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
    
    $to = isset($data['to']) ? trim($data['to']) : '';
    $subject = isset($data['subject']) ? trim($data['subject']) : '';
    $message = isset($data['message']) ? trim($data['message']) : '';
    $client_id = isset($data['client_id']) ? intval($data['client_id']) : null;
    
    if (empty($to) || empty($subject) || empty($message)) {
        throw new Exception('To, subject, and message are required');
    }
    
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Get email settings
    $settings_sql = "SELECT * FROM email_settings LIMIT 1";
    $settings_result = $conn->query($settings_sql);
    
    if (!$settings_result || $settings_result->num_rows === 0) {
        throw new Exception('Email settings not configured. Please configure email settings first.');
    }
    
    $settings = $settings_result->fetch_assoc();
    
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Fallback to PHP mail() function
        $headers = "From: " . $settings['smtp_from_name'] . " <" . $settings['smtp_from_email'] . ">\r\n";
        $headers .= "Reply-To: " . $settings['smtp_from_email'] . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $formatted_message = nl2br(htmlspecialchars($message));
        
        if (mail($to, $subject, $formatted_message, $headers)) {
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully using system mail'
            ]);
        } else {
            throw new Exception('Failed to send email using system mail');
        }
    } else {
        // Use PHPMailer
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->Port = $settings['smtp_port'];
        
        if ($settings['smtp_encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($settings['smtp_encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Recipients
        $mail->setFrom($settings['smtp_from_email'], $settings['smtp_from_name']);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br(htmlspecialchars($message));
        $mail->AltBody = $message;
        
        $mail->send();
        
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully'
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
