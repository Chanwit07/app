<?php
/**
 * Save Supply Request - Process Form B (New) & C (Edit)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkAuth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$requestType = $_POST['request_type'] ?? '';
if (!in_array($requestType, ['new_code', 'edit_detail'])) {
    jsonResponse(false, 'ประเภทคำขอไม่ถูกต้อง');
}

$userId = (int) $_SESSION['user_id'];

// Validate based on type
if ($requestType === 'new_code') {
    if (empty(trim($_POST['item_name'] ?? '')) || empty(trim($_POST['unit'] ?? ''))) {
        jsonResponse(false, 'กรุณากรอกชื่อรายการและหน่วยนับ');
    }
    $itemNumber = null;
    $itemName = sanitize($_POST['item_name']);
    $newItemName = null;
    $unit = sanitize($_POST['unit']);
    $annualUsage = !empty($_POST['annual_usage']) ? (int) $_POST['annual_usage'] : null;
    $maxMin = !empty($_POST['max_min']) ? sanitize($_POST['max_min']) : null;
} else {
    // edit_detail
    $required = ['item_number', 'item_name', 'new_item_name', 'unit'];
    foreach ($required as $f) {
        if (empty(trim($_POST[$f] ?? ''))) {
            jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
        }
    }
    $itemNumber = sanitize($_POST['item_number']);
    $itemName = sanitize($_POST['item_name']);
    $newItemName = sanitize($_POST['new_item_name']);
    $unit = sanitize($_POST['unit']);
    $annualUsage = null;
    $maxMin = null;
}

// Handle image upload
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imagePath = handleUpload($_FILES['image'], 'supplies');
    if ($imagePath === false) {
        jsonResponse(false, 'ไม่สามารถอัปโหลดรูปภาพได้');
    }
}

// Insert
$stmt = $conn->prepare("INSERT INTO supply_requests (user_id, request_type, item_number, item_name, new_item_name, unit, annual_usage, max_min, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("isssssiss", $userId, $requestType, $itemNumber, $itemName, $newItemName, $unit, $annualUsage, $maxMin, $imagePath);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();

    // Audit trail
    logAudit($conn, 'ส่งคำขอพัสดุ (' . requestTypeLabel($requestType) . ')', 'supply_requests', $newId, json_encode([
        'request_type' => $requestType,
        'item_name' => $itemName
    ]));

    // Telegram
    $typeLabel = requestTypeLabel($requestType);
    $teleMsg = "📦 <b>คำขอพัสดุใหม่</b> ({$typeLabel})\n";
    $teleMsg .= "━━━━━━━━━━━━━\n";
    if ($itemNumber)
        $teleMsg .= "🔢 เลขที่สิ่งของ: {$itemNumber}\n";
    $teleMsg .= "📝 รายการ: {$itemName}\n";
    if ($newItemName)
        $teleMsg .= "✏️ ชื่อใหม่: {$newItemName}\n";
    $teleMsg .= "📏 หน่วยนับ: {$unit}\n";
    $teleMsg .= "👤 ผู้ขอ: " . sanitize($_SESSION['fullname']) . "\n";
    $teleMsg .= "📅 วันที่: " . thaiDate(null, 'full');
    sendTelegram($teleMsg);

    $msg = $requestType === 'new_code' ? 'ส่งคำขอรหัสพัสดุใหม่เรียบร้อย' : 'ส่งคำขอแก้ไขรายละเอียดเรียบร้อย';
    jsonResponse(true, $msg . ' (เลขที่: #' . $newId . ')');
} else {
    $stmt->close();
    jsonResponse(false, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
}
