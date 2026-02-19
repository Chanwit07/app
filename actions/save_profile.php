<?php
/**
 * Save Profile / Change Password - AJAX endpoint
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkAuth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$action = $_POST['action'] ?? '';
$userId = (int) $_SESSION['user_id'];

// ============================
// Update Profile Info
// ============================
if ($action === 'update_profile') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (empty($fullname)) {
        jsonResponse(false, 'กรุณากรอกชื่อ-นามสกุล');
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'รูปแบบอีเมลไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, department = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullname, $email, $department, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        // Update session
        $_SESSION['fullname'] = sanitize($fullname);

        logAudit($conn, 'อัปเดตโปรไฟล์', 'users', $userId, json_encode([
            'fullname' => $fullname,
            'email' => $email
        ]));

        jsonResponse(true, 'อัปเดตโปรไฟล์เรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาดในการบันทึก');
    }
}

// ============================
// Change Password
// ============================
if ($action === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
    }

    if (strlen($newPassword) < 4) {
        jsonResponse(false, 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 ตัวอักษร');
    }

    if ($newPassword !== $confirmPassword) {
        jsonResponse(false, 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน');
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        jsonResponse(false, 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
    }

    // Update password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $newHash, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        logAudit($conn, 'เปลี่ยนรหัสผ่าน', 'users', $userId, '');
        jsonResponse(true, 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน');
    }
}

jsonResponse(false, 'Invalid action');
