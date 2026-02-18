<?php
// Debug script focused on supply_requests
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maintenance_platform');

$conn = mysqli_init();
@$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

echo "--- SUPPLY REQUESTS DUMP ---\n";
$res = $conn->query("SELECT id, status, created_at FROM supply_requests");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Status: {$row['status']} | Created: {$row['created_at']}\n";
}
