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

// Get invoice statistics from invoices table
$stats = [];

// Outstanding invoices (total_remaining > 0)
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM invoices WHERE total_remaining > 0");
$row = $result->fetch_assoc();
$stats['outstanding_count'] = intval($row['count']);
$stats['outstanding_amount'] = floatval($row['amount']);

// Overdue invoices (due_date < today and total_remaining > 0)
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM invoices WHERE total_remaining > 0 AND due_date < CURDATE()");
$row = $result->fetch_assoc();
$stats['overdue_count'] = intval($row['count']);
$stats['overdue_amount'] = floatval($row['amount']);

// Total collected (sum of total_paid from invoices)
$result = $conn->query("SELECT COALESCE(SUM(total_paid), 0) as total FROM invoices");
$stats['total_collected'] = floatval($result->fetch_assoc()['total']);

// Total invoiced (sum of total_cost from invoices)
$result = $conn->query("SELECT COALESCE(SUM(total_cost), 0) as total FROM invoices");
$stats['total_invoiced'] = floatval($result->fetch_assoc()['total']);

echo json_encode(['success' => true] + $stats);

$conn->close();
