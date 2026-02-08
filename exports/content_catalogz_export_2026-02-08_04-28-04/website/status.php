<!DOCTYPE html>
<html>
<head>
    <title>Server Status - Content Catalogz</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .check { color: green; margin-right: 10px; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🚀 Server Status Check</h1>
    
    <div class="info">
        <h2 class="success">✓ Server is Running!</h2>
        <p>If you can see this page, the PHP development server is working correctly.</p>
    </div>

    <h2>System Information:</h2>
    <ul>
        <li><span class="check">✓</span> PHP Version: <strong><?php echo phpversion(); ?></strong></li>
        <li><span class="check">✓</span> Server Time: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></li>
        <li><span class="check">✓</span> Document Root: <strong><?php echo $_SERVER['DOCUMENT_ROOT']; ?></strong></li>
        <li><span class="check">✓</span> Server Name: <strong><?php echo $_SERVER['SERVER_NAME']; ?></strong></li>
        <li><span class="check">✓</span> Server Port: <strong><?php echo $_SERVER['SERVER_PORT']; ?></strong></li>
    </ul>

    <h2>Database Connection:</h2>
    <?php
    $db_host = '127.0.0.1';
    $db_user = 'petertmooney';
    $db_pass = '68086500aA!';
    $db_name = 'Content_Catalogz';
    
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, 3306);
    
    if ($conn->connect_error) {
        echo '<p style="color: red;">✗ Database Connection Failed: ' . htmlspecialchars($conn->connect_error) . '</p>';
    } else {
        echo '<p class="success">✓ Database Connected Successfully</p>';
        
        // Check tables
        $tables = ['users', 'pages', 'quotes'];
        echo '<ul>';
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo '<li><span class="check">✓</span> Table <strong>' . $table . '</strong>: ' . $row['count'] . ' records</li>';
            }
        }
        echo '</ul>';
        $conn->close();
    }
    ?>

    <h2>Available Pages:</h2>
    <ul>
        <li><a href="/index.html">Home Page</a></li>
        <li><a href="/about.html">About Page</a></li>
        <li><a href="/quote.html">Get Quote Page</a></li>
        <li><a href="/admin/login.php">Admin Login</a></li>
    </ul>

    <h2>How to Access from Browser:</h2>
    <div class="info">
        <ol>
            <li>In VS Code, look at the <strong>bottom panel</strong></li>
            <li>Click the <strong>"PORTS"</strong> tab (next to Terminal)</li>
            <li>Find port <strong>8083</strong></li>
            <li>Right-click → Port Visibility → <strong>Public</strong></li>
            <li>Click the <strong>🌐 globe icon</strong> to open in browser</li>
        </ol>
    </div>

    <hr>
    <p style="text-align: center; color: #666;">
        <small>Content Catalogz Development Server • <?php echo gethostname(); ?></small>
    </p>
</body>
</html>
