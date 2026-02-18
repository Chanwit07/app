<?php
/**
 * Update Status - AJAX endpoint for Kanban
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkRole(['admin', 'super_admin']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$type = $_POST['type'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$adminNote = trim($_POST['admin_note'] ?? '');

// Validate
if (!in_array($type, ['asset', 'supply'])) {
    jsonResponse(false, 'ประเภทไม่ถูกต้อง');
}
if ($id <= 0) {
    jsonResponse(false, 'ID ไม่ถูกต้อง');
}
if (!in_array($newStatus, ['Pending', 'Processing', 'Completed'])) {
    jsonResponse(false, 'สถานะไม่ถูกต้อง');
}

$table = $type === 'asset' ? 'asset_requests' : 'supply_requests';
$adminId = (int) $_SESSION['user_id'];

// Update status
if ($newStatus === 'Completed') {
    $stmt = $conn->prepare("UPDATE `$table` SET status = ?, admin_note = ?, updated_by = ?, finished_at = NOW() WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE `$table` SET status = ?, admin_note = ?, updated_by = ? WHERE id = ?");
}
$stmt->bind_param("ssii", $newStatus, $adminNote, $adminId, $id);

if ($stmt->execute()) {
    $stmt->close();

    // Audit trail
    $statusLabels = ['Pending' => 'รอดำเนินการ', 'Processing' => 'กำลังดำเนินการ', 'Completed' => 'เสร็จสิ้น'];
    logAudit($conn, 'เปลี่ยนสถานะเป็น ' . $statusLabels[$newStatus], $table, $id, json_encode([
        'new_status' => $newStatus,
        'admin_note' => $adminNote
    ]));

    // Get request details for Telegram
    $detail = $conn->query("SELECT * FROM `$table` WHERE id = $id")->fetch_assoc();
    $typeLabel = $type === 'asset' ? 'สินทรัพย์' : 'พัสดุ';

    $teleMsg = "🔄 <b>อัปเดตสถานะ</b>\n";
    $teleMsg .= "━━━━━━━━━━━━━\n";
    $teleMsg .= "📋 ประเภท: {$typeLabel} #{$id}\n";
    $teleMsg .= "📊 สถานะ: {$statusLabels[$newStatus]}\n";
    if ($adminNote)
        $teleMsg .= "📝 หมายเหตุ: {$adminNote}\n";
    $teleMsg .= "👤 โดย: " . sanitize($_SESSION['fullname']) . "\n";
    $teleMsg .= "📅 วันที่: " . thaiDate(null, 'full');
    sendTelegram($teleMsg);

    jsonResponse(true, 'อัปเดตสถานะเรียบร้อยแล้ว');
} else {
    $stmt->close();
    jsonResponse(false, 'เกิดข้อผิดพลาดในการอัปเดต');
}
