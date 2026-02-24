<?php
/**
 * Save/Update/Delete Dashboard Reports
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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $embedUrl = trim($_POST['embed_url'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $icon = trim($_POST['icon'] ?? 'fa-chart-bar');
    $colorFrom = trim($_POST['color_from'] ?? '#667eea');
    $colorTo = trim($_POST['color_to'] ?? '#764ba2');
    $isActive = isset($_POST['is_active']) ? ((int) $_POST['is_active']) : 1;
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $createdBy = (int) $_SESSION['user_id'];

    if (empty($title) || empty($embedUrl)) {
        jsonResponse(false, 'กรุณากรอกชื่อรายงานและ URL');
    }

    $stmt = $conn->prepare("INSERT INTO dashboard_reports (title, description, embed_url, category, icon, color_from, color_to, is_active, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssiii", $title, $description, $embedUrl, $category, $icon, $colorFrom, $colorTo, $isActive, $sortOrder, $createdBy);

    if ($stmt->execute()) {
        logAudit($conn, 'สร้างรายงาน Dashboard', 'dashboard_reports', $stmt->insert_id, json_encode(['title' => $title]));
        $stmt->close();
        jsonResponse(true, 'เพิ่มรายงานใหม่เรียบร้อยแล้ว');
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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $embedUrl = trim($_POST['embed_url'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $icon = trim($_POST['icon'] ?? 'fa-chart-bar');
    $colorFrom = trim($_POST['color_from'] ?? '#667eea');
    $colorTo = trim($_POST['color_to'] ?? '#764ba2');
    $isActive = isset($_POST['is_active']) ? ((int) $_POST['is_active']) : 1;
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($id <= 0 || empty($title) || empty($embedUrl)) {
        jsonResponse(false, 'ข้อมูลไม่ครบถ้วน');
    }

    $stmt = $conn->prepare("UPDATE dashboard_reports SET title = ?, description = ?, embed_url = ?, category = ?, icon = ?, color_from = ?, color_to = ?, is_active = ?, sort_order = ? WHERE id = ?");
    $stmt->bind_param("sssssssiii", $title, $description, $embedUrl, $category, $icon, $colorFrom, $colorTo, $isActive, $sortOrder, $id);

    if ($stmt->execute()) {
        logAudit($conn, 'แก้ไขรายงาน Dashboard', 'dashboard_reports', $id, json_encode(['title' => $title]));
        $stmt->close();
        jsonResponse(true, 'แก้ไขรายงานเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
}

// ============================
// TOGGLE ACTIVE
// ============================
if ($action === 'toggle_active') {
    $id = (int) ($_POST['id'] ?? 0);
    $isActive = (int) ($_POST['is_active'] ?? 0);

    if ($id <= 0) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE dashboard_reports SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $isActive, $id);

    if ($stmt->execute()) {
        $statusText = $isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        logAudit($conn, $statusText . 'รายงาน Dashboard', 'dashboard_reports', $id, '');
        $stmt->close();
        jsonResponse(true, $statusText . 'รายงานเรียบร้อยแล้ว');
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

    // Get title for audit log
    $titleRes = $conn->query("SELECT title FROM dashboard_reports WHERE id = $id");
    $titleRow = $titleRes ? $titleRes->fetch_assoc() : null;

    $stmt = $conn->prepare("DELETE FROM dashboard_reports WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAudit($conn, 'ลบรายงาน Dashboard', 'dashboard_reports', $id, json_encode(['title' => $titleRow['title'] ?? '']));
        $stmt->close();
        jsonResponse(true, 'ลบรายงานเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
}

jsonResponse(false, 'Invalid action');
