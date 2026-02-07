<?php
// Database configuration
define('DB_HOST', '127.0.0.1');  // Use 127.0.0.1 instead of localhost to force TCP
define('DB_PORT', 3306);
define('DB_USER', 'petertmooney');
define('DB_PASS', '68086500aA!');
define('DB_NAME', 'Content_Catalogz');

// Create connection with explicit port
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

// Check connection
if ($conn->connect_error) {
    $error_msg = "
    <h2>Database Connection Failed</h2>
    <p><strong>Error:</strong> " . $conn->connect_error . "</p>
    <hr>
    <h3>⚠️ The MySQL database service is not running!</h3>
    <p>You need to rebuild your dev container to start the database:</p>
    <ol>
        <li>Press <code>Ctrl+Shift+P</code> (or <code>Cmd+Shift+P</code> on Mac)</li>
        <li>Type and select: <strong>\"Rebuild Container\"</strong></li>
        <li>Wait 2-3 minutes for the rebuild to complete</li>
        <li>Then run: <code>php admin/setup/init_db.php</code></li>
    </ol>
    <p>For detailed instructions, see: <a href='/ADMIN_SETUP.md'>ADMIN_SETUP.md</a></p>
    ";
    die($error_msg);
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

// Create quotes table for client quote requests
$quotes_sql = "CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    phone VARCHAR(50),
    service VARCHAR(100),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'new',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($quotes_sql) !== TRUE) {
    // Non-fatal error - log it but don't die
    error_log("Error creating quotes table: " . $conn->error);
}
