<?php
// Simple debug endpoint to inspect current user returned by getCurrentUser()
// Access restricted to logged-in users.

include __DIR__ . '/config/auth.php';
requireLogin();

header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Hide sensitive fields just in case
$public = $user;
unset($public['password']);

echo json_encode(['success' => true, 'user' => $public]);
