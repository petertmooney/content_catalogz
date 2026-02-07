<?php
session_start();
header('Content-Type: application/json');

require_once '../config/auth.php';
require_once '../config/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$clientId = $data['client_id'] ?? null;
$clientName = $data['client_name'] ?? '';
$clientEmail = $data['client_email'] ?? '';
$invoiceNumber = $data['invoice_number'] ?? '';
$services = $data['services'] ?? [];
$totalCost = floatval($data['total_cost'] ?? 0);
$totalPaid = floatval($data['total_paid'] ?? 0);
$totalRemaining = floatval($data['total_remaining'] ?? 0);

if (empty($clientEmail)) {
    echo json_encode(['success' => false, 'message' => 'Client email is required']);
    exit;
}

if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Get client details from database
$company = '';
$phone = '';
$address = '';
if ($clientId) {
    $stmt = $conn->prepare("SELECT company, phone, address FROM quotes WHERE id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $company = $row['company'] ?? '';
        $phone = $row['phone'] ?? '';
        $address = $row['address'] ?? '';
    }
}

// Build services HTML
$servicesHtml = '';
foreach ($services as $service) {
    $servicesHtml .= '<tr><td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($service['name']) . '</td>';
    $servicesHtml .= '<td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">£' . number_format($service['cost'], 2) . '</td></tr>';
}

// Build invoice HTML for email
$invoiceDate = date('d/m/Y');
$dueDate = date('d/m/Y', strtotime('+30 days'));

$emailHtml = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice ' . htmlspecialchars($invoiceNumber) . '</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
    <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #667eea; padding-bottom: 20px;">
            <div>
                <h1 style="color: #667eea; margin: 0;">INVOICE</h1>
                <p style="color: #666; margin: 5px 0;">' . htmlspecialchars($invoiceNumber) . '</p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; color: #333;">Content Catalogz</h2>
                <p style="color: #666; margin: 5px 0;">Your Business Address</p>
                <p style="color: #666; margin: 5px 0;">contact@contentcatalogz.com</p>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
            <div>
                <h3 style="color: #333; margin-bottom: 10px;">Bill To:</h3>
                <p style="margin: 5px 0;"><strong>' . htmlspecialchars($clientName) . '</strong></p>
                ' . ($company ? '<p style="margin: 5px 0;">' . htmlspecialchars($company) . '</p>' : '') . '
                ' . ($address ? '<p style="margin: 5px 0;">' . htmlspecialchars($address) . '</p>' : '') . '
                <p style="margin: 5px 0;">' . htmlspecialchars($clientEmail) . '</p>
                ' . ($phone ? '<p style="margin: 5px 0;">' . htmlspecialchars($phone) . '</p>' : '') . '
            </div>
            <div style="text-align: right;">
                <p style="margin: 5px 0;"><strong>Invoice Date:</strong> ' . $invoiceDate . '</p>
                <p style="margin: 5px 0;"><strong>Due Date:</strong> ' . $dueDate . '</p>
            </div>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background: #667eea; color: white;">
                    <th style="padding: 12px; text-align: left;">Description</th>
                    <th style="padding: 12px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . $servicesHtml . '
            </tbody>
            <tfoot>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 12px; font-weight: bold;">Total</td>
                    <td style="padding: 12px; text-align: right; font-weight: bold;">£' . number_format($totalCost, 2) . '</td>
                </tr>
                <tr>
                    <td style="padding: 12px;">Paid</td>
                    <td style="padding: 12px; text-align: right; color: #28a745;">£' . number_format($totalPaid, 2) . '</td>
                </tr>
                <tr style="background: #fff3cd;">
                    <td style="padding: 12px; font-weight: bold;">Balance Due</td>
                    <td style="padding: 12px; text-align: right; font-weight: bold; color: #dc3545;">£' . number_format($totalRemaining, 2) . '</td>
                </tr>
            </tfoot>
        </table>
        
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
            <p>Thank you for your business!</p>
            <p style="font-size: 12px;">Payment is due within 30 days of invoice date.</p>
        </div>
    </div>
</body>
</html>';

// Email subject and headers
$subject = "Invoice " . $invoiceNumber . " from Content Catalogz";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Content Catalogz <noreply@contentcatalogz.com>\r\n";
$headers .= "Reply-To: contact@contentcatalogz.com\r\n";

// Send email
if (mail($clientEmail, $subject, $emailHtml, $headers)) {
    // Log the activity
    $user = getCurrentUser();
    $userId = $user['id'] ?? 1;
    
    $stmt = $conn->prepare("INSERT INTO activities (client_id, activity_type, subject, description, activity_date, created_by) VALUES (?, 'email', ?, ?, NOW(), ?)");
    $activitySubject = "Invoice Sent: " . $invoiceNumber;
    $activityDesc = "Invoice " . $invoiceNumber . " emailed to " . $clientEmail;
    $stmt->bind_param("issi", $clientId, $activitySubject, $activityDesc, $userId);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Invoice sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please check your server mail configuration.']);
}

$conn->close();
