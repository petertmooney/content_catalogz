<?php
/**
 * Reset Admin Password Script
 * Use this to reset the admin password to the default: admin123
 */

require_once __DIR__ . '/../config/db.php';

echo "Resetting admin password...\n";

// New password
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "✓ Password reset successfully!\n\n";
    echo "===========================================\n";
    echo "  LOGIN CREDENTIALS\n";
    echo "===========================================\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "===========================================\n\n";
} else {
    echo "❌ Error resetting password: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>
