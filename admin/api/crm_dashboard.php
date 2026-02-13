<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/auth.php';
include __DIR__ . '/../config/db.php';

// Caching: prefer Redis if available, otherwise fall back to file cache.
// Lowered TTL for near-real-time consistency (was 300s).
$cacheTtl = 60; // seconds
$cacheKey = 'crm_dashboard_v1';
$servedFromCache = false;

try {
    if (class_exists('Redis')) {
        $r = new \Redis();
        if (@$r->connect('127.0.0.1', 6379, 1)) {
            $cached = false;
            if (is_object($r) && method_exists($r, 'get')) {
                $cached = @$r->get($cacheKey);
            }
            if ($cached) {
                echo $cached;
                $conn->close();
                exit;
            }
        }
    } else {
        $cacheFile = __DIR__ . '/../cache/crm_dashboard.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
            echo file_get_contents($cacheFile);
            $conn->close();
            exit;
        }
    }
} catch (Exception $e) {
    // ignore cache errors and continue to build fresh response
}

// Get CRM dashboard statistics
$stats = [];

// Total clients
$result = $conn->query("SELECT COUNT(*) as count FROM quotes");
$stats['total_clients'] = $result->fetch_assoc()['count'];

// Active clients (new, in_progress)
$result = $conn->query("SELECT COUNT(*) as count FROM quotes WHERE status IN ('new', 'in_progress')");
$stats['active_clients'] = $result->fetch_assoc()['count'];

// Won deals (completed)
$result = $conn->query("SELECT COUNT(*) as count FROM quotes WHERE status = 'completed'");
$stats['won_deals'] = $result->fetch_assoc()['count'];

// Total revenue (from invoices)
$result = $conn->query("SELECT COALESCE(SUM(total_cost), 0) as total FROM invoices");
$stats['total_revenue'] = floatval($result->fetch_assoc()['total']);

// Outstanding payments
$result = $conn->query("SELECT COALESCE(SUM(total_remaining), 0) as total FROM invoices WHERE total_remaining > 0");
$stats['outstanding_payments'] = floatval($result->fetch_assoc()['total']);

// Pending tasks
$result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status IN ('pending', 'in_progress')");
$stats['pending_tasks'] = $result->fetch_assoc()['count'];

// Overdue tasks
$result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status IN ('pending', 'in_progress') AND due_date < CURDATE()");
$stats['overdue_tasks'] = $result->fetch_assoc()['count'];

// Urgent tasks
$result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status IN ('pending', 'in_progress') AND priority = 'urgent'");
$stats['urgent_tasks'] = $result->fetch_assoc()['count'];

// Activities this week
$result = $conn->query("SELECT COUNT(*) as count FROM activities WHERE activity_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['activities_this_week'] = $result->fetch_assoc()['count'];

// Clients needing follow-up (only if column exists)
$stats['follow_ups_due'] = 0;
$colCheck = $conn->query("SHOW COLUMNS FROM quotes LIKE 'next_follow_up'");
if ($colCheck && $colCheck->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as count FROM quotes WHERE next_follow_up <= CURDATE() AND status IN ('new', 'contacted', 'in_progress')");
    $stats['follow_ups_due'] = intval($result->fetch_assoc()['count']);
}

// Expected value (pipeline) - prefer expected_value if available, otherwise fall back to total_cost if present
$stats['pipeline_value'] = 0.0;
$colCheck = $conn->query("SHOW COLUMNS FROM quotes LIKE 'expected_value'");
if ($colCheck && $colCheck->num_rows > 0) {
    $result = $conn->query("SELECT COALESCE(SUM(expected_value), 0) as total FROM quotes WHERE status IN ('new', 'contacted', 'in_progress')");
    $stats['pipeline_value'] = floatval($result->fetch_assoc()['total']);
} else {
    // fallback to total_cost if available
    $colCheck = $conn->query("SHOW COLUMNS FROM quotes LIKE 'total_cost'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $result = $conn->query("SELECT COALESCE(SUM(total_cost), 0) as total FROM quotes WHERE status IN ('new', 'contacted', 'in_progress')");
        $stats['pipeline_value'] = floatval($result->fetch_assoc()['total']);
    }
}

// Recent activities (last 10)
$stmt = $conn->query("SELECT a.*, q.name as client_name, q.company FROM activities a LEFT JOIN quotes q ON a.client_id = q.id ORDER BY a.activity_date DESC LIMIT 10");
$recent_activities = [];
while ($row = $stmt->fetch_assoc()) {
    $recent_activities[] = $row;
}
$stats['recent_activities'] = $recent_activities;

// Upcoming  tasks (next 7 days)
$stmt = $conn->query("SELECT t.*, q.name as client_name, q.company FROM tasks t LEFT JOIN quotes q ON t.client_id = q.id WHERE t.status IN ('pending', 'in_progress') AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY t.due_date ASC LIMIT 10");
$upcoming_tasks = [];
while ($row = $stmt->fetch_assoc()) {
    $upcoming_tasks[] = $row;
}
$stats['upcoming_tasks'] = $upcoming_tasks;

// Lead sources breakdown (only if column exists)
$stats['lead_sources'] = [];
$colCheck = $conn->query("SHOW COLUMNS FROM quotes LIKE 'lead_source'");
if ($colCheck && $colCheck->num_rows > 0) {
    $stmt = $conn->query("SELECT lead_source, COUNT(*) as count FROM quotes WHERE lead_source IS NOT NULL GROUP BY lead_source ORDER BY count DESC");
    $lead_sources = [];
    while ($row = $stmt->fetch_assoc()) {
        $lead_sources[] = $row;
    }
    $stats['lead_sources'] = $lead_sources;
}

// Status breakdown
$stmt = $conn->query("SELECT status, COUNT(*) as count FROM quotes GROUP BY status");
$status_breakdown = [];
while ($row = $stmt->fetch_assoc()) {
    $status_breakdown[$row['status']] = intval($row['count']);
}
$stats['status_breakdown'] = $status_breakdown;

$out = json_encode(['success' => true, 'stats' => $stats]);

// write cache (Redis preferred)
try {
    // avoid `instanceof \Redis` so static analyzers don't require the Redis extension/type to exist;
    // instead verify the object at runtime supports a TTL-aware write method.
    if (isset($r) && is_object($r) && method_exists($r, 'setex')) {
        // call dynamically so static analyzers won't complain about an unknown method
        $method = 'setex';
        @$r->{$method}($cacheKey, $cacheTtl, $out);
    } elseif (isset($r) && is_object($r) && method_exists($r, 'set')) {
        // some Redis clients expose `set` with a TTL parameter/options instead of `setex`
        // try the common signatures; suppress warnings on failure
        @call_user_func([$r, 'set'], $cacheKey, $out, $cacheTtl);
    } else {
        @file_put_contents(__DIR__ . '/../cache/crm_dashboard.json', $out);
    }
} catch (Exception $e) {
    // ignore cache write failures
}

echo $out;

$conn->close();
