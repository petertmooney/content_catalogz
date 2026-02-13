<?php
/**
 * Run SQL migrations found in admin/migrations/*.sql
 * - Safe for local/staging use
 * - Run from CLI: php admin/setup/run_migrations.php
 * - Can also be executed via web when authenticated (requires admin login)
 */

if (php_sapi_name() !== 'cli') {
    // web request - require authentication
    session_start();
    require_once __DIR__ . '/../config/auth.php';
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo "403 - authentication required\n";
        exit;
    }
}

require_once __DIR__ . '/../config/db.php';

$migrationDir = __DIR__ . '/../migrations';
$files = glob($migrationDir . '/*.sql');
if (!$files) {
    echo "No migration files found in $migrationDir\n";
    exit;
}

foreach ($files as $file) {
    echo "Running migration: " . basename($file) . "\n";
    $sql = file_get_contents($file);
    if (!$sql) {
        echo "  -> failed to read file\n";
        continue;
    }

    // Execute as multi query to allow multiple statements
    if ($conn->multi_query($sql)) {
        do {
            if ($res = $conn->store_result()) {
                while ($row = $res->fetch_row()) {
                    echo "    ".implode(' | ', $row) . "\n";
                }
                $res->free();
            }
            if ($conn->more_results()) {
                // advance to next result
            } else break;
        } while ($conn->next_result());
        echo "  -> applied\n";
    } else {
        echo "  -> ERROR: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migrations complete.\n";
