<?php
// Cron script to refresh cached dashboard API responses.
// Run from CLI or schedule via cron: php admin/scripts/refresh_dashboard_cache.php

chdir(__DIR__ . '/../../');
require_once 'admin/config/db.php';

$endpoints = [
    'http://127.0.0.1:8081/admin/api/crm_dashboard.php',
    'http://127.0.0.1:8081/admin/api/invoice_trends.php?metric=collected&range=monthly',
    'http://127.0.0.1:8081/admin/api/invoice_trends.php?metric=invoiced&range=monthly',
    'http://127.0.0.1:8081/admin/api/invoice_trends.php?metric=collected&range=yearly',
    'http://127.0.0.1:8081/admin/api/invoice_trends.php?metric=invoiced&range=yearly',
];

foreach ($endpoints as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($res === false || $code !== 200) {
        echo "Failed to refresh: $url (code $code)\n";
    } else {
        echo "Refreshed: $url\n";
    }
    curl_close($ch);
}

echo "Done.\n";
