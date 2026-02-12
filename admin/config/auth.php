<?php
// Disable displaying PHP errors in the browser (production-safe)
ini_set('display_errors', 0);
// Log errors to file and keep reporting enabled (don't show notices/warnings to users)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require login for admin pages
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /admin/login.php");
        exit();
    }
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;

    // Prefer fetching full user record from DB so templates can rely on fields like role/first_name
    try {
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $stmt = $GLOBALS['conn']->prepare("SELECT id, username, email, role, full_name, first_name, last_name FROM users WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                // ensure at least username/id are present
                if ($row) {
                    return [
                        'id' => $row['id'] ?? $_SESSION['user_id'],
                        'username' => $row['username'] ?? $_SESSION['username'],
                        'email' => $row['email'] ?? '',
                        'role' => $row['role'] ?? 'admin',
                        'full_name' => $row['full_name'] ?? '',
                        'first_name' => $row['first_name'] ?? '',
                        'last_name' => $row['last_name'] ?? ''
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // fall back to session values
    }

    // Fallback when DB not available or query failed
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => '',
        'role' => 'admin',
        'full_name' => '',
        'first_name' => '',
        'last_name' => ''
    ];
}

// Logout user
function logout() {
    session_destroy();
    header("Location: /admin/login.php");
    exit();
}

// Prevent XSS attacks
function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
