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
$services = isset($data['services']) ? $data['services'] : [];
$totalCost = floatval($data['total_cost'] ?? 0);

// Get client info from database
$stmt = $conn->prepare("SELECT name, email, company FROM quotes WHERE id = ?");
$stmt->bind_param("i", $quoteId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Quote not found']);
    exit;
}

$client = $result->fetch_assoc();
$stmt->close();

$clientName = $client['name'];
$clientEmail = $client['email'];
$clientCompany = $client['company'] ?: '';

// Build the email content
$subject = "Your Quote from Content Catalogz";

// Build services table for email
$servicesHtml = '';
$servicesText = '';
foreach ($services as $service) {
    $serviceName = htmlspecialchars($service['name']);
    $serviceCost = number_format($service['cost'], 2);
    $servicesHtml .= "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$serviceName}</td><td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>£{$serviceCost}</td></tr>";
    $servicesText .= "- {$service['name']}: £{$serviceCost}\n";
}

$totalFormatted = number_format($totalCost, 2);

// HTML Email
$htmlMessage = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .services-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .services-table th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
        .services-table th:last-child { text-align: right; }
        .total-row { font-weight: bold; font-size: 18px; }
        .total-row td { padding: 15px 10px; border-top: 2px solid #2c3e50; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0;'>Content Catalogz</h1>
            <p style='margin: 5px 0 0 0;'>Your Quote</p>
        </div>
        <div class='content'>
            <p>Dear {$clientName},</p>
            <p>Thank you for your interest in our services. Please find your quote details below:</p>
            
            <table class='services-table'>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Cost (GBP)</th>
                    </tr>
                </thead>
                <tbody>
                    {$servicesHtml}
                    <tr class='total-row'>
                        <td>Total</td>
                        <td style='text-align: right;'>£{$totalFormatted}</td>
                    </tr>
                </tbody>
            </table>
            
            <p>This quote is valid for 30 days from the date of this email.</p>
            <p>If you have any questions or would like to proceed, please don't hesitate to contact us.</p>
            <p>Best regards,<br>Content Catalogz Team</p>
        </div>
        <div class='footer'>
            <p>Content Catalogz | www.contentcatalogz.com</p>
        </div>
    </div>
</body>
</html>
";

// Plain text fallback
$textMessage = "
Dear {$clientName},

Thank you for your interest in our services. Please find your quote details below:

SERVICES:
{$servicesText}
TOTAL: £{$totalFormatted}

This quote is valid for 30 days from the date of this email.

If you have any questions or would like to proceed, please don't hesitate to contact us.

Best regards,
Content Catalogz Team
";

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Content Catalogz <noreply@contentcatalogz.com>\r\n";
$headers .= "Reply-To: info@contentcatalogz.com\r\n";

// Send email
$emailSent = mail($clientEmail, $subject, $htmlMessage, $headers);

if ($emailSent) {
    // Log the activity
    $activityType = 'email';
    $activitySubject = 'Quote emailed to client';
    $activityDescription = "Quote (£{$totalFormatted}) sent to {$clientEmail}";
    $userId = $_SESSION['user_id'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO activities (client_id, type, subject, description, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $quoteId, $activityType, $activitySubject, $activityDescription, $userId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quote emailed successfully',
        'email' => $clientEmail
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email. Please check server mail configuration.'
    ]);
}

$conn->close();
