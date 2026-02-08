<?php
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
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ];
    }
    return null;
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
