<?php
session_start();
header('Content-Type: application/json');

require_once '../config/auth.php';
require_once '../config/db.php';

// Get invoice statistics
$stats = [];

// Outstanding invoices (total_remaining > 0)
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM invoices WHERE total_remaining > 0");
$row = $result->fetch_assoc();
$stats['outstanding_count'] = intval($row['count']);
$stats['outstanding_amount'] = floatval($row['amount']);

// Overdue invoices (due_date < today and total_remaining > 0)
// If no due_date column, use invoice_date + 30 days as due
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_remaining), 0) as amount FROM invoices WHERE total_remaining > 0 AND DATE_ADD(invoice_date, INTERVAL 30 DAY) < CURDATE()");
$row = $result->fetch_assoc();
$stats['overdue_count'] = intval($row['count']);
$stats['overdue_amount'] = floatval($row['amount']);

// Total collected (sum of total_paid)
$result = $conn->query("SELECT COALESCE(SUM(total_paid), 0) as total FROM invoices");
$stats['total_collected'] = floatval($result->fetch_assoc()['total']);

// Total invoiced (sum of total_cost)
$result = $conn->query("SELECT COALESCE(SUM(total_cost), 0) as total FROM invoices");
$stats['total_invoiced'] = floatval($result->fetch_assoc()['total']);

echo json_encode(['success' => true] + $stats);

$conn->close();
