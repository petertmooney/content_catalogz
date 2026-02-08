<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database configuration
$host = 'localhost';
$dbname = 'Content_Catalogz';
$username = 'petertmooney';
$password = '68086500aA!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle GET request for tables list
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'tables') {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'tables' => $tables]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle POST request for SQL queries
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['query'])) {
        echo json_encode(['success' => false, 'error' => 'No query provided']);
        exit;
    }
    
    $query = trim($input['query']);
    
    // Security: Prevent dangerous operations
    $dangerousKeywords = ['DROP', 'TRUNCATE', 'DELETE FROM', 'ALTER TABLE', 'CREATE DATABASE', 'DROP DATABASE'];
    $upperQuery = strtoupper($query);
    
    foreach ($dangerousKeywords as $keyword) {
        if (strpos($upperQuery, $keyword) !== false) {
            echo json_encode([
                'success' => false, 
                'error' => "Dangerous operation detected: $keyword is not allowed for safety reasons. This is a read-only database manager."
            ]);
            exit;
        }
    }
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        // Check if it's a SELECT query
        if (stripos($query, 'SELECT') === 0 || stripos($query, 'SHOW') === 0 || stripos($query, 'DESCRIBE') === 0 || stripos($query, 'DESC') === 0) {
            $results = $stmt->fetchAll();
            echo json_encode([
                'success' => true,
                'results' => $results,
                'row_count' => count($results)
            ]);
        } else {
            // For UPDATE, INSERT, etc.
            $affectedRows = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'affected_rows' => $affectedRows,
                'message' => "Query executed successfully. $affectedRows row(s) affected."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
?>
