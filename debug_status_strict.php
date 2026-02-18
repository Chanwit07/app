<?php
// Debug script to check for hidden characters in status
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maintenance_platform');

$conn = mysqli_init();
@$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

echo "--- STATUS DUMP (with brackets) ---\n";
$tables = ['asset_requests', 'supply_requests'];
foreach ($tables as $tbl) {
    echo "Table: $tbl\n";
    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM `$tbl` GROUP BY status");
    while ($row = $res->fetch_assoc()) {
        echo "  ['{$row['status']}'] (Length: " . strlen($row['status']) . ") Count: {$row['cnt']}\n";
    }
}
