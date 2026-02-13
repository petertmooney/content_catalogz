<?php
// Simple script to run the lead_source update migration
require_once __DIR__ . '/../config/db.php';

echo "Running lead_source update migration...\n";

$sql = file_get_contents(__DIR__ . '/../migrations/2026-02-13_update_blank_lead_sources.sql');

if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            while ($row = $res->fetch_row()) {
                echo implode(' | ', $row) . "\n";
            }
            $res->free();
        }
        if ($conn->more_results()) {
            // advance to next result
        } else break;
    } while ($conn->next_result());
    echo "Migration completed successfully\n";
} else {
    echo "Error running migration: " . $conn->error . "\n";
}

$conn->close();
?>