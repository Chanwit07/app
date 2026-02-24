<?php
// actions/save_blog_tags.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์ใช้งานส่วนนี้']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$color_code = trim($_POST['color_code'] ?? 'primary');

switch ($action) {
    case 'create':
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อแท็ก']);
            exit;
        }

        // Check if name already exists
        $checkStmt = $conn->prepare("SELECT id FROM blog_tags WHERE name = ?");
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'ชื่อแท็กนี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO blog_tags (name, color_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $color_code);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'เพิ่มแท็กเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'update':
        if ($id <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        // Check if name already exists (excluding this ID)
        $checkStmt = $conn->prepare("SELECT id FROM blog_tags WHERE name = ? AND id != ?");
        $checkStmt->bind_param("si", $name, $id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'ชื่อแท็กนี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE blog_tags SET name = ?, color_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $color_code, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'อัปเดตแท็กเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ระบุ ID ไม่ถูกต้อง']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM blog_tags WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'ลบแท็กเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $conn->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        break;
}
