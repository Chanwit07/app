<?php
/**
 * Save Asset Request - Process Form A
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkAuth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

// Validate required
$required = ['department', 'asset_id', 'asset_group', 'serial_number', 'account_type'];
foreach ($required as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
    }
}

$department = sanitize($_POST['department']);
$assetId = sanitize($_POST['asset_id']);
$assetGroup = sanitize($_POST['asset_group']);
$serialNumber = sanitize($_POST['serial_number']);
$accountType = sanitize($_POST['account_type']);
$userId = (int) $_SESSION['user_id'];

// Handle image upload
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imagePath = handleUpload($_FILES['image'], 'assets');
    if ($imagePath === false) {
        jsonResponse(false, 'ไม่สามารถอัปโหลดรูปภาพได้ (ตรวจสอบขนาดและประเภทไฟล์)');
    }
}

// Insert to database
$stmt = $conn->prepare("INSERT INTO asset_requests (user_id, department, asset_id, asset_group, serial_number, account_type, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("issssss", $userId, $department, $assetId, $assetGroup, $serialNumber, $accountType, $imagePath);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();

    // Audit trail
    logAudit($conn, 'ส่งคำขอรหัสสินทรัพย์', 'asset_requests', $newId, json_encode([
        'department' => $department,
        'asset_id' => $assetId,
        'asset_group' => $assetGroup
    ]));

    // Telegram notification
    $teleMsg = "📋 <b>คำขอรหัสสินทรัพย์ใหม่</b>\n";
    $teleMsg .= "━━━━━━━━━━━━━\n";
    $teleMsg .= "🏢 หน่วยงาน: {$department}\n";
    $teleMsg .= "🔢 เลขที่: {$assetId}\n";
    $teleMsg .= "📂 กลุ่ม: {$assetGroup}\n";
    $teleMsg .= "🔑 Serial: {$serialNumber}\n";
    $teleMsg .= "👤 ผู้ขอ: " . sanitize($_SESSION['fullname']) . "\n";
    $teleMsg .= "📅 วันที่: " . thaiDate(null, 'full');
    sendTelegram($teleMsg);

    jsonResponse(true, 'ส่งคำขอรหัสสินทรัพย์เรียบร้อยแล้ว (เลขที่: #' . $newId . ')');
} else {
    $stmt->close();
    jsonResponse(false, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
}
