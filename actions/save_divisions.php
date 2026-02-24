<?php
/**
 * CRUD Actions — Landing Divisions (หน่วยงานในสังกัด)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkRole(['admin', 'super_admin']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$action = $_POST['action'] ?? '';

// ============================
// CREATE
// ============================
if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-building');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if (empty($name)) {
        jsonResponse(false, 'กรุณากรอกชื่อหน่วยงาน');
    }

    // Auto sort_order
    if ($sortOrder <= 0) {
        $maxRes = $conn->query("SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM landing_divisions");
        $sortOrder = ($maxRes && $r = $maxRes->fetch_assoc()) ? (int) $r['next_order'] : 1;
    }

    $stmt = $conn->prepare("INSERT INTO landing_divisions (name, location, description, icon, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $name, $location, $description, $icon, $sortOrder, $status);

    if ($stmt->execute()) {
        logAudit($conn, 'สร้างหน่วยงาน', 'landing_divisions', $stmt->insert_id, json_encode(['name' => $name]));
        $stmt->close();
        jsonResponse(true, 'เพิ่มหน่วยงานเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
}

// ============================
// UPDATE
// ============================
if ($action === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-building');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if ($id <= 0 || empty($name)) {
        jsonResponse(false, 'ข้อมูลไม่ครบถ้วน');
    }

    $stmt = $conn->prepare("UPDATE landing_divisions SET name = ?, location = ?, description = ?, icon = ?, sort_order = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssisi", $name, $location, $description, $icon, $sortOrder, $status, $id);

    if ($stmt->execute()) {
        logAudit($conn, 'แก้ไขหน่วยงาน', 'landing_divisions', $id, json_encode(['name' => $name]));
        $stmt->close();
        jsonResponse(true, 'แก้ไขหน่วยงานเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
}

// ============================
// TOGGLE STATUS
// ============================
if ($action === 'toggle_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';

    if ($id <= 0) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE landing_divisions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        logAudit($conn, ($status === 'active' ? 'เปิด' : 'ปิด') . 'หน่วยงาน', 'landing_divisions', $id, '');
        $stmt->close();
        jsonResponse(true, 'เปลี่ยนสถานะเรียบร้อย');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
}

// ============================
// DELETE
// ============================
if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    $nameRes = $conn->query("SELECT name FROM landing_divisions WHERE id = $id");
    $nameRow = $nameRes ? $nameRes->fetch_assoc() : null;

    $stmt = $conn->prepare("DELETE FROM landing_divisions WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAudit($conn, 'ลบหน่วยงาน', 'landing_divisions', $id, json_encode(['name' => $nameRow['name'] ?? '']));
        $stmt->close();
        jsonResponse(true, 'ลบหน่วยงานเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
}

jsonResponse(false, 'Invalid action');
