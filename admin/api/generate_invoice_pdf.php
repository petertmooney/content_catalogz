<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$invoiceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invoice ID required']);
    exit;
}

// Fetch invoice data
$invoiceStmt = $conn->prepare("
    SELECT i.*, q.name, q.company, q.email, q.phone,
           CONCAT_WS(', ', q.address_street, q.address_line2, q.address_city, q.address_county, q.address_postcode, q.address_country) as address
    FROM invoices i
    JOIN quotes q ON i.client_id = q.id
    WHERE i.id = ?
");
$invoiceStmt->bind_param("i", $invoiceId);
$invoiceStmt->execute();
$invoiceResult = $invoiceStmt->get_result();

if ($invoiceResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    exit;
}

$invoice = $invoiceResult->fetch_assoc();
$invoiceStmt->close();

// Fetch invoice services
$servicesStmt = $conn->prepare("SELECT * FROM invoice_services WHERE invoice_id = ? ORDER BY id");
$servicesStmt->bind_param("i", $invoiceId);
$servicesStmt->execute();
$servicesResult = $servicesStmt->get_result();
$services = [];
while ($service = $servicesResult->fetch_assoc()) {
    $services[] = $service;
}
$servicesStmt->close();

// Fetch payment history (activities with payment_received type)
$paymentsStmt = $conn->prepare("
    SELECT * FROM activities
    WHERE client_id = ? AND type = 'payment_received'
    ORDER BY activity_date DESC
");
$paymentsStmt->bind_param("i", $invoice['client_id']);
$paymentsStmt->execute();
$paymentsResult = $paymentsStmt->get_result();
$payments = [];
while ($payment = $paymentsResult->fetch_assoc()) {
    $payments[] = $payment;
}
$paymentsStmt->close();

// Generate HTML invoice
$logoPath = __DIR__ . '/../../assets/images/LogoPink.png';
$logoData = '';
if (file_exists($logoPath)) {
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoData = 'data:image/png;base64,' . $logoBase64;
}

// Build services HTML
$servicesHtml = '';
$totalCost = 0;
foreach ($services as $service) {
    $servicesHtml .= '<tr>
        <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($service['description']) . '</td>
        <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">£' . number_format($service['unit_price'], 2) . '</td>
    </tr>';
    $totalCost += $service['unit_price'];
}

// Build payment history HTML
$paymentsHtml = '';
if (!empty($payments)) {
    foreach ($payments as $payment) {
        // Extract amount from description if possible
        $amount = 0;
        if (preg_match('/£?(\d+(?:\.\d{2})?)/', $payment['description'], $matches)) {
            $amount = floatval($matches[1]);
        }
        $paymentsHtml .= '<tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">' . date('d/m/Y', strtotime($payment['activity_date'])) . '</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">' . htmlspecialchars($payment['subject']) . '</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">£' . number_format($amount, 2) . '</td>
        </tr>';
    }
} else {
    $paymentsHtml = '<tr><td colspan="3" style="padding: 20px; text-align: center; color: #666;">No payment history available</td></tr>';
}

$invoiceDate = date('d/m/Y', strtotime($invoice['invoice_date']));
$dueDate = date('d/m/Y', strtotime($invoice['due_date']));

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .invoice-container { background: white; max-width: 800px; margin: 0 auto; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { border-bottom: 3px solid #DB1C56; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #DB1C56; margin: 0 0 10px 0; }
        .header .invoice-number { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #DB1C56; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .balance-row { background: #fff3cd; color: #dc3545; font-weight: bold; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
        .print-btn { background: #DB1C56; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-bottom: 20px; }
        .print-btn:hover { background: #B81748; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <button class="print-btn no-print" onclick="window.print()">🖨️ Print/Save as PDF</button>

        <div class="header">
            <table width="100%">
            <tr>
            <td>
                <h1>INVOICE</h1>
                <p class="invoice-number">' . htmlspecialchars($invoice['invoice_number']) . '</p>
            </td>
            <td style="text-align: right;">
                ' . ($logoData ? '<img src="' . $logoData . '" alt="Content Catalogz" style="height: 50px; margin-bottom: 10px;">' : '<h2 style="margin: 0; color: #333;">Content Catalogz</h2>') . '
                <p style="color: #666; margin: 5px 0;">Your Business Address</p>
                <p style="color: #666; margin: 5px 0;">contact@contentcatalogz.com</p>
            </td>
            </tr>
            </table>
        </div>

        <table style="margin-bottom: 30px;">
        <tr>
            <td valign="top" width="50%">
                <h3 style="margin-bottom: 10px; color: #333;">Bill To:</h3>
                <p style="margin: 5px 0;"><strong>' . htmlspecialchars($invoice['name']) . '</strong></p>
                ' . ($invoice['company'] ? '<p style="margin: 5px 0;">' . htmlspecialchars($invoice['company']) . '</p>' : '') . '
                ' . ($invoice['address'] ? '<p style="margin: 5px 0;">' . htmlspecialchars($invoice['address']) . '</p>' : '') . '
                <p style="margin: 5px 0;">' . htmlspecialchars($invoice['email']) . '</p>
                ' . ($invoice['phone'] ? '<p style="margin: 5px 0;">' . htmlspecialchars($invoice['phone']) . '</p>' : '') . '
            </td>
            <td style="text-align: right;" valign="top" width="50%">
                <p style="margin: 5px 0;"><strong>Invoice Date:</strong> ' . $invoiceDate . '</p>
                <p style="margin: 5px 0;"><strong>Due Date:</strong> ' . $dueDate . '</p>
            </td>
        </tr>
        </table>

        <h3 style="margin-bottom: 15px; color: #333;">Services</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . $servicesHtml . '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td style="padding: 12px;">Total</td>
                    <td style="padding: 12px; text-align: right;">£' . number_format($totalCost, 2) . '</td>
                </tr>
                <tr>
                    <td style="padding: 12px;">Paid</td>
                    <td style="padding: 12px; text-align: right; color: #28a745;">£' . number_format($invoice['total_paid'], 2) . '</td>
                </tr>
                <tr class="balance-row">
                    <td style="padding: 12px;">Balance Due</td>
                    <td style="padding: 12px; text-align: right;">£' . number_format($invoice['total_remaining'], 2) . '</td>
                </tr>
            </tfoot>
        </table>

        <h3 style="margin-bottom: 15px; color: #333;">Payment History</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . $paymentsHtml . '
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="font-size: 12px;">Payment is due within 30 days of invoice date.</p>
        </div>
    </div>
</body>
</html>';

$conn->close();

// Output the HTML
header('Content-Type: text/html; charset=UTF-8');
echo $html;
?>