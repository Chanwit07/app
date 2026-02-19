<?php
/**
 * Notifications AJAX Endpoint
 * GET  - fetch unread notifications
 * POST - mark notification(s) as read
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkRole(['admin', 'super_admin']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch latest notifications for this admin
    $userId = (int) $_SESSION['user_id'];
    $limit = min((int) ($_GET['limit'] ?? 20), 50);

    $stmt = $conn->prepare("
        SELECT id, type, title, message, link, is_read, created_at 
        FROM notifications 
        WHERE user_id IS NULL OR user_id = ?
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $row['time_ago'] = timeAgo($row['created_at']);
        $notifications[] = $row;
    }
    $stmt->close();

    // Count unread
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) as cnt 
        FROM notifications 
        WHERE (user_id IS NULL OR user_id = ?) AND is_read = 0
    ");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $unread = $stmt2->get_result()->fetch_assoc()['cnt'];
    $stmt2->close();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => (int) $unread
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        jsonResponse(true, 'อ่านแล้ว');
    }

    if ($action === 'mark_all_read') {
        $userId = (int) $_SESSION['user_id'];
        $conn->query("UPDATE notifications SET is_read = 1 WHERE (user_id IS NULL OR user_id = $userId) AND is_read = 0");
        jsonResponse(true, 'อ่านทั้งหมดแล้ว');
    }

    jsonResponse(false, 'Invalid action');
}

/**
 * Human-readable time ago
 */
function timeAgo($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)
        return 'เมื่อสักครู่';
    if ($diff < 3600)
        return floor($diff / 60) . ' นาทีที่แล้ว';
    if ($diff < 86400)
        return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
    if ($diff < 604800)
        return floor($diff / 86400) . ' วันที่แล้ว';
    return thaiDate($datetime, 'compact');
}
