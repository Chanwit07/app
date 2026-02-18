<?php
// Debug script to check dashboard logic
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maintenance_platform');

$conn = mysqli_init();
@$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Copying logic from dashboard.php

// 1. Status Stats
$stats = ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0];
$tables = ['asset_requests', 'supply_requests'];

echo "--- STATUS STATS ---\n";
foreach ($tables as $tbl) {
    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM `$tbl` GROUP BY status");
    if (!$res) {
        echo "Query failed for $tbl: " . $conn->error . "\n";
        continue;
    }
    while ($row = $res->fetch_assoc()) {
        echo "Table $tbl: Status '{$row['status']}' Count {$row['cnt']}\n";
        $key = strtolower($row['status']);
        if (isset($stats[$key])) {
            $stats[$key] += $row['cnt'];
        } else {
            echo "WARNING: Unknown status '$key'\n";
        }
        $stats['total'] += $row['cnt'];
    }
}
print_r($stats);

// 2. Monthly Data
echo "\n--- MONTHLY DATA ---\n";
function thaiDateStub($date)
{
    return $date;
} // Stub

$monthlyData = [];
// Simulate the requested loop
for ($i = 5; $i >= 0; $i--) {
    // ORIGINAL LOGIC
    $monthStart = date('Y-m-01', strtotime("-{$i} months"));
    $monthEnd = date('Y-m-t', strtotime("-{$i} months"));

    // PROPOSED FIX LOGIC
    $fixedStart = date('Y-m-01', strtotime("-$i months", strtotime(date('Y-m-01'))));
    $fixedEnd = date('Y-m-t', strtotime("-$i months", strtotime(date('Y-m-01'))));

    echo "Month -$i: Original: $monthStart - $monthEnd | Fixed: $fixedStart - $fixedEnd\n";

    // Run query with ORIGINAL to see data
    $assetCount = 0;
    $res = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests WHERE created_at BETWEEN '$monthStart' AND '$monthEnd 23:59:59'");
    if ($res)
        $assetCount = $res->fetch_assoc()['cnt'];

    echo "  -> Count: $assetCount\n";
}
