<?php
// Debug script to check database content
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

echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n--- ASSET REQUESTS (Last 5) ---\n";
$res = $conn->query("SELECT id, status, created_at FROM asset_requests ORDER BY created_at DESC LIMIT 5");
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']} | Status: {$row['status']} | Created: {$row['created_at']}\n";
    }
} else {
    echo "No asset requests found.\n";
}

echo "\n--- SUPPLY REQUESTS (Last 5) ---\n";
$res = $conn->query("SELECT id, status, created_at FROM supply_requests ORDER BY created_at DESC LIMIT 5");
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']} | Status: {$row['status']} | Created: {$row['created_at']}\n";
    }
} else {
    echo "No supply requests found.\n";
}

echo "\n--- DISTINCT STATUSES ---\n";
$res = $conn->query("SELECT DISTINCT status FROM asset_requests UNION SELECT DISTINCT status FROM supply_requests");
while ($row = $res->fetch_assoc()) {
    echo "Status: '{$row['status']}'\n";
}
