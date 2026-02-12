<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

// Allow either an authenticated session OR a valid token for automation
$input = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
$tokenOk = false;

// token can be provided via `token` POST field or `X-Export-Token` header
$providedToken = $input['token'] ?? ($_SERVER['HTTP_X_EXPORT_TOKEN'] ?? null);
$tokenFile = __DIR__ . '/../config/scheduled_export_token.txt';
if ($providedToken && is_readable($tokenFile)) {
    $expected = trim(file_get_contents($tokenFile));
    if ($expected && hash_equals($expected, trim($providedToken))) {
        $tokenOk = true;
    }
}

// If not token-authorized, require login
if (!$tokenOk) {
    requireLogin();
}

header('Content-Type: application/json');
$type = $input['type'] ?? 'invoice_trends';
$metric = $input['metric'] ?? 'collected';
$range = $input['range'] ?? 'monthly';
$webhook = $input['webhook_url'] ?? ($input['webhook'] ?? null);
$triggeredBy = $tokenOk ? 'token' : 'session';

$timestamp = date('Ymd_His');
$filename = "export_{$type}_{$metric}_{$range}_{$timestamp}.csv";
$savePath = __DIR__ . "/../exports/" . $filename;

// ensure exports dir
if (!is_dir(__DIR__ . '/../exports')) mkdir(__DIR__ . '/../exports', 0755, true);

$csv = '';

if ($type === 'invoice_trends') {
    // call invoice_trends logic directly
    $metric_col = ($metric === 'invoiced') ? 'total_cost' : 'total_paid';
    if ($range === 'yearly') {
        $sql = "SELECT DATE_FORMAT(invoice_date, '%Y') as label, COALESCE(SUM($metric_col),0) as value FROM invoices WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR) GROUP BY label ORDER BY label ASC";
        $csv .= "year,{$metric}\n";
    } else {
        $sql = "SELECT DATE_FORMAT(invoice_date, '%Y-%m') as label, COALESCE(SUM($metric_col),0) as value FROM invoices WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY label ORDER BY label ASC";
        $csv .= "month,{$metric}\n";
    }
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $csv .= "{$row['label']}," . number_format((float)$row['value'], 2, '.', '') . "\n";
    }
} else if ($type === 'crm') {
    // small CRM summary CSV
    $stats = [];
    $result = $conn->query("SELECT COUNT(*) as count FROM quotes"); $stats['total_clients'] = $result->fetch_assoc()['count'];
    $result = $conn->query("SELECT COALESCE(SUM(total_cost),0) as total FROM invoices"); $stats['total_revenue'] = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status IN ('pending','in_progress')"); $stats['pending_tasks'] = $result->fetch_assoc()['count'];

    $csv .= "metric,value\n";
    foreach ($stats as $k => $v) $csv .= "{$k}," . (is_numeric($v) ? number_format((float)$v,2,'.','') : $v) . "\n";
} else {
    echo json_encode(['success' => false, 'error' => 'unsupported type']);
    exit;
}

// save CSV
file_put_contents($savePath, $csv);

$response = ['success' => true, 'filename' => $filename, 'path' => "exports/{$filename}", 'triggered_by' => $triggeredBy];

// optionally POST to webhook
if ($webhook) {
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/csv']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $csv);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response['webhook'] = ['status' => $code, 'response' => $res];
}

echo json_encode($response);
$conn->close();
