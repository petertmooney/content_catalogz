<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'petertmooney');
define('DB_PASS', '68086500aA!');
define('DB_NAME', 'Content_Catalogz');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    // Select database
    $conn->select_db(DB_NAME);
} else {
    die("Error creating database: " . $conn->error);
}

// Create pages table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    page_type VARCHAR(50),
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($table_sql) === TRUE) {
    // Table created successfully
} else {
    die("Error creating table: " . $conn->error);
}

// Create users table for admin authentication
$users_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($users_sql) === TRUE) {
    // Check if admin user exists, if not create one
    $username = 'admin';
    $password = password_hash('admin_password', PASSWORD_BCRYPT);
    $email = 'admin@contentcatalogz.com';
    
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $insert_sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $username, $password, $email);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
} else {
    die("Error creating users table: " . $conn->error);
}
?>
