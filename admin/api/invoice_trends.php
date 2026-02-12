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

// Prepare last 12 months labels (YYYY-MM)
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[$m] = 0.0;
}

// Aggregate paid totals by month (using invoice_date)
$sql = "SELECT DATE_FORMAT(invoice_date, '%Y-%m') as ym, COALESCE(SUM(total_paid), 0) as total
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

echo json_encode(['success' => true, 'months' => $months]);

$conn->close();
