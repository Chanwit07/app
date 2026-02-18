<?php
/**
 * Maintenance Insight Platform - Configuration
 * MySQLi Connection, Helpers & Constants
 */

// ============================
// Timezone & Session
// ============================
date_default_timezone_set('Asia/Bangkok');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================
// Database Configuration
// ============================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maintenance_platform');

// ============================
// Base URL (For absolute paths)
// ============================
define('BASE_URL', 'http://localhost/app');

// ============================
// Telegram Configuration
// ============================
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID_HERE');

// ============================
// App Constants
// ============================
define('APP_NAME', 'Maintenance Insight Platform');
define('APP_VERSION', '1.0.0');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);

// ============================
// MySQLi Connection
// ============================
$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
@$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Check if this is an AJAX/API request
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') !== false)
    ) {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    // Show user-friendly HTML error
    die('<!DOCTYPE html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Database Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <style>*{font-family:"Prompt",sans-serif}body{min-height:100vh;background:#0f0c29;display:flex;align-items:center;justify-content:center}</style>
    </head><body><div class="text-center text-white p-4" style="max-width:500px">
    <div style="font-size:4rem">⚠️</div>
    <h3 class="mt-3">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</h3>
    <p class="text-white-50 mt-2">Error: ' . htmlspecialchars($conn->connect_error) . '</p>
    <div class="alert alert-warning text-start mt-3" style="font-size:0.85rem">
    <strong>วิธีแก้ไข:</strong><br>
    1. เปิด XAMPP → Start <b>Apache</b> และ <b>MySQL</b><br>
    2. เปิด phpMyAdmin → Import ไฟล์ <code>database.sql</code><br>
    3. ตรวจสอบค่า DB ใน <code>config.php</code>
    </div>
    <a href="" class="btn btn-outline-light mt-2">ลองอีกครั้ง</a>
    </div></body></html>');
}

$conn->set_charset('utf8mb4');

// ============================
// Helper Functions
// ============================

/**
 * Convert date to Thai Buddhist Era format (พ.ศ.)
 * @param string|null $datetime - date string or null for current time
 * @param string $format - 'short' (วว/ดด/ปปปป), 'long' (วว เดือน ปปปป), 'full' (with time)
 * @return string
 */
function thaiDate($datetime = null, $format = 'long')
{
    if ($datetime === null) {
        $timestamp = time();
    } else {
        $timestamp = strtotime($datetime);
    }

    if ($timestamp === false)
        return '-';

    $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];

    $thaiMonthsShort = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.'
    ];

    $day = (int) date('j', $timestamp);
    $month = (int) date('n', $timestamp);
    $year = (int) date('Y', $timestamp) + 543; // Convert to พ.ศ.
    $time = date('H:i', $timestamp);

    switch ($format) {
        case 'short':
            return date('d/m/', $timestamp) . $year;
        case 'long':
            return "$day {$thaiMonths[$month]} $year";
        case 'full':
            return "$day {$thaiMonths[$month]} $year เวลา $time น.";
        case 'compact':
            return "$day {$thaiMonthsShort[$month]} $year";
        case 'month':
            return "{$thaiMonthsShort[$month]} " . substr($year, 2);
        default:
            return "$day {$thaiMonths[$month]} $year";
    }
}

/**
 * Send message via Telegram Bot API
 * @param string $message - Text to send
 * @return bool
 */
function sendTelegram($message)
{
    if (TELEGRAM_BOT_TOKEN === 'YOUR_BOT_TOKEN_HERE') {
        return false; // Skip if not configured
    }

    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result !== false;
}

/**
 * Log action to audit trail
 * @param mysqli $conn
 * @param string $action - Action description
 * @param string $targetTable - Table affected
 * @param int|null $targetId - Record ID
 * @param string $details - Additional JSON details
 */
function logAudit($conn, $action, $targetTable = '', $targetId = null, $details = '')
{
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, action, target_table, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $userId, $action, $targetTable, $targetId, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Sanitize input string
 * @param string $input
 * @return string
 */
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle file upload
 * @param array $file - $_FILES element
 * @param string $subfolder - Subfolder in uploads directory
 * @return string|false - Filename on success, false on failure
 */
function handleUpload($file, $subfolder = '')
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return false;
    }

    $targetDir = UPLOAD_DIR . ($subfolder ? $subfolder . '/' : '');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = uniqid('img_') . '_' . time() . '.' . $ext;
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ($subfolder ? $subfolder . '/' : '') . $filename;
    }

    return false;
}

/**
 * JSON response helper
 * @param bool $success
 * @param string $message
 * @param array $data
 */
function jsonResponse($success, $message = '', $data = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string
 */
function statusBadge($status)
{
    $badges = [
        'Pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>รอดำเนินการ</span>',
        'Processing' => '<span class="badge bg-info text-white"><i class="fas fa-cog fa-spin me-1"></i>กำลังดำเนินการ</span>',
        'Completed' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>เสร็จสิ้น</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">ไม่ทราบ</span>';
}

/**
 * Get request type label
 * @param string $type
 * @return string
 */
function requestTypeLabel($type)
{
    $labels = [
        'new_code' => 'ขอรหัสพัสดุใหม่',
        'edit_detail' => 'แก้ไขรายละเอียดพัสดุ'
    ];
    return $labels[$type] ?? $type;
}
