<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/auth.php';

requireLogin();

$metric = $_GET['metric'] ?? 'collected'; // 'collected' => total_paid, 'invoiced' => total_cost
$range = $_GET['range'] ?? 'monthly'; // 'monthly' or 'yearly'

// normalize
$metric_col = ($metric === 'invoiced') ? 'total_cost' : 'total_paid';

if ($range === 'yearly') {
    // Return last 5 years totals
    $years = [];
    for ($y = date('Y') - 4; $y <= date('Y'); $y++) {
        $years[(string)$y] = 0.0;
    }

    $sql = "SELECT DATE_FORMAT(invoice_date, '%Y') as yy, COALESCE(SUM($metric_col), 0) as total
            FROM invoices
            WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
            GROUP BY yy
            ORDER BY yy ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $yy = $row['yy'];
            if (isset($years[$yy])) {
                $years[$yy] = floatval($row['total']);
            }
        }
    }

    echo json_encode(['success' => true, 'range' => 'yearly', 'metric' => $metric, 'years' => $years]);
    $conn->close();
    exit;
}

// Default: monthly (last 12 months)
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[$m] = 0.0;
}

$sql = "SELECT DATE_FORMAT(invoice_date, '%Y-%m') as ym, COALESCE(SUM($metric_col), 0) as total
        FROM invoices
        WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
        GROUP BY ym
        ORDER BY ym ASC";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ym = $row['ym'];
        if (isset($months[$ym])) {
            $months[$ym] = floatval($row['total']);
        }
    }
}

echo json_encode(['success' => true, 'range' => 'monthly', 'metric' => $metric, 'months' => $months]);

$conn->close();
