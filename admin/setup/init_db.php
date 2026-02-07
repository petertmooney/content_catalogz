<?php
/**
 * Database Initialization Script
 * Run this once to create the database tables and initial admin user
 */

require_once __DIR__ . '/../config/db.php';

echo "Starting database setup...\n\n";

// The database is already created by db.php, now create the users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($users_table) === TRUE) {
    echo "✓ Users table created successfully\n";
} else {
    die("Error creating users table: " . $conn->error . "\n");
}

// Check if admin user exists
$check_admin = "SELECT id FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows === 0) {
    // Create default admin user
    // Default credentials: admin / admin123 (CHANGE THIS AFTER FIRST LOGIN!)
    $default_username = 'admin';
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $default_email = 'admin@contentcatalogz.com';
    
    $insert_admin = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $insert_admin->bind_param("sss", $default_username, $default_password, $default_email);
    
    if ($insert_admin->execute()) {
        echo "✓ Default admin user created successfully\n";
        echo "\n";
        echo "===========================================\n";
        echo "  DEFAULT LOGIN CREDENTIALS\n";
        echo "===========================================\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
        echo "===========================================\n";
        echo "  ⚠️  IMPORTANT: Change this password after\n";
        echo "     your first login!\n";
        echo "===========================================\n\n";
    } else {
        echo "Error creating admin user: " . $insert_admin->error . "\n";
    }
    $insert_admin->close();
} else {
    echo "✓ Admin user already exists\n\n";
}

echo "Database setup complete!\n";
echo "You can now access the admin portal at: /admin/login.php\n";

$conn->close();
?>
