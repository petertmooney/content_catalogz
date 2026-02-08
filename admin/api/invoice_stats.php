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

// Get invoice statistics from quotes table (client billing)
$stats = [];

// Outstanding invoices (total_remaining > 0)
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM quotes WHERE total_remaining > 0");
$row = $result->fetch_assoc();
$stats['outstanding_count'] = intval($row['count']);
$stats['outstanding_amount'] = floatval($row['amount']);

// Overdue invoices - using updated_at + 30 days as overdue threshold
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM quotes WHERE total_remaining > 0 AND DATE_ADD(updated_at, INTERVAL 30 DAY) < CURDATE()");
$row = $result->fetch_assoc();
$stats['overdue_count'] = intval($row['count']);
$stats['overdue_amount'] = floatval($row['amount']);

// Total collected (sum of total_paid)
$result = $conn->query("SELECT COALESCE(SUM(total_paid), 0) as total FROM quotes");
$stats['total_collected'] = floatval($result->fetch_assoc()['total']);

// Total invoiced (sum of total_paid + positive balances only)
// This represents the total value of services provided to clients
$result = $conn->query("SELECT COALESCE(SUM(total_paid), 0) + COALESCE(SUM(CASE WHEN total_remaining > 0 THEN total_remaining ELSE 0 END), 0) as total FROM quotes");
$stats['total_invoiced'] = floatval($result->fetch_assoc()['total']);

echo json_encode(['success' => true] + $stats);

$conn->close();
