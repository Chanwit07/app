<?php
/**
 * Learning Materials - CRUD Actions (AJAX)
 * Handles: create, update, delete, toggle_status
 */
require_once __DIR__ . '/../auth.php';
checkAuth();

// Only admins can manage learning materials
if (!isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์ดำเนินการ');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Extract YouTube Video ID from various URL formats
 */
function getYoutubeVideoId($url)
{
    $url = trim($url);
    // Match various YouTube URL formats
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
        return $match[1];
    }
    // If it's already just a video ID (11 chars)
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        return $url;
    }
    return null;
}

// ============================
// CREATE
// ============================
if ($action === 'create') {
    $title = sanitize($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    if (empty($title) || empty($youtube_url)) {
        jsonResponse(false, 'กรุณากรอกชื่อบทเรียนและลิงก์ YouTube');
    }

    $video_id = getYoutubeVideoId($youtube_url);
    if (!$video_id) {
        jsonResponse(false, 'ลิงก์ YouTube ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง');
    }

    $created_by = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO learning_materials (title, description, youtube_url, youtube_video_id, category, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssii", $title, $description, $youtube_url, $video_id, $category, $sort_order, $created_by);

    if ($stmt->execute()) {
        logAudit($conn, 'เพิ่มสื่อการเรียนรู้: ' . $title, 'learning_materials', $stmt->insert_id);
        jsonResponse(true, 'เพิ่มสื่อการเรียนรู้สำเร็จ', ['id' => $stmt->insert_id]);
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
    $stmt->close();
}

// ============================
// UPDATE
// ============================
if ($action === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    if ($id <= 0 || empty($title) || empty($youtube_url)) {
        jsonResponse(false, 'ข้อมูลไม่ครบถ้วน');
    }

    $video_id = getYoutubeVideoId($youtube_url);
    if (!$video_id) {
        jsonResponse(false, 'ลิงก์ YouTube ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE learning_materials SET title=?, description=?, youtube_url=?, youtube_video_id=?, category=?, sort_order=? WHERE id=?");
    $stmt->bind_param("sssssii", $title, $description, $youtube_url, $video_id, $category, $sort_order, $id);

    if ($stmt->execute()) {
        logAudit($conn, 'แก้ไขสื่อการเรียนรู้ #' . $id, 'learning_materials', $id);
        jsonResponse(true, 'อัปเดตสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
    $stmt->close();
}

// ============================
// DELETE
// ============================
if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("DELETE FROM learning_materials WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAudit($conn, 'ลบสื่อการเรียนรู้ #' . $id, 'learning_materials', $id);
        jsonResponse(true, 'ลบสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
    $stmt->close();
}

// ============================
// TOGGLE STATUS
// ============================
if ($action === 'toggle_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    if ($id <= 0 || !in_array($new_status, ['active', 'inactive'])) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE learning_materials SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);

    if ($stmt->execute()) {
        logAudit($conn, "เปลี่ยนสถานะสื่อการเรียนรู้ #{$id} เป็น {$new_status}", 'learning_materials', $id);
        jsonResponse(true, 'อัปเดตสถานะสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
    $stmt->close();
}

// ============================
// GET (single item for edit)
// ============================
if ($action === 'get') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("SELECT * FROM learning_materials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        jsonResponse(true, '', ['item' => $item]);
    } else {
        jsonResponse(false, 'ไม่พบข้อมูล');
    }
}

jsonResponse(false, 'ไม่ระบุ action');
