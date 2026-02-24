<?php
/**
 * CRUD Actions — Landing News (ข่าวประชาสัมพันธ์)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkRole(['admin', 'super_admin']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$action = $_POST['action'] ?? '';

/**
 * Helper: Generate slug from title
 */
function generateNewsSlug($conn, $title, $excludeId = 0)
{
    // Transliterate Thai -> simple slug
    $slug = trim($title);
    $slug = preg_replace('/[^a-zA-Z0-9\p{Thai}\s-]/u', '', $slug);
    $slug = preg_replace('/[\s]+/', '-', $slug);
    $slug = strtolower($slug);
    if (empty($slug)) {
        $slug = 'news-' . time();
    }
    // Ensure uniqueness
    $base = $slug;
    $i = 1;
    while (true) {
        $check = $conn->prepare("SELECT id FROM landing_news WHERE slug = ? AND id != ?");
        $check->bind_param("si", $slug, $excludeId);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $check->close();
            break;
        }
        $check->close();
        $slug = $base . '-' . $i;
        $i++;
    }
    return $slug;
}

// ============================
// CREATE
// ============================
if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'ข่าวองค์กร');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $publishDate = trim($_POST['publish_date'] ?? date('Y-m-d'));
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if (empty($title) || empty($excerpt)) {
        jsonResponse(false, 'กรุณากรอกหัวข้อและเนื้อหาย่อ');
    }

    $slug = generateNewsSlug($conn, $title);

    // Handle cover image upload
    $coverImage = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $coverImage = 'news_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $coverImage);
        }
    }

    $stmt = $conn->prepare("INSERT INTO landing_news (title, slug, category, excerpt, content, cover_image, publish_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $title, $slug, $category, $excerpt, $content, $coverImage, $publishDate, $status);

    if ($stmt->execute()) {
        logAudit($conn, 'สร้างข่าวประชาสัมพันธ์', 'landing_news', $stmt->insert_id, json_encode(['title' => $title]));
        $stmt->close();
        jsonResponse(true, 'เพิ่มข่าวเรียบร้อยแล้ว');
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
    $category = trim($_POST['category'] ?? 'ข่าวองค์กร');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $publishDate = trim($_POST['publish_date'] ?? date('Y-m-d'));
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if ($id <= 0 || empty($title) || empty($excerpt)) {
        jsonResponse(false, 'ข้อมูลไม่ครบถ้วน');
    }

    $slug = generateNewsSlug($conn, $title, $id);

    // Handle cover image upload
    $coverImageSql = '';
    $params = [$title, $slug, $category, $excerpt, $content, $publishDate, $status];
    $types = "sssssss";

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $coverImage = 'news_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $coverImage);
            $coverImageSql = ', cover_image = ?';
            $params[] = $coverImage;
            $types .= 's';
        }
    }

    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare("UPDATE landing_news SET title = ?, slug = ?, category = ?, excerpt = ?, content = ?, publish_date = ?, status = ?{$coverImageSql} WHERE id = ?");
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        logAudit($conn, 'แก้ไขข่าวประชาสัมพันธ์', 'landing_news', $id, json_encode(['title' => $title]));
        $stmt->close();
        jsonResponse(true, 'แก้ไขข่าวเรียบร้อยแล้ว');
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

    $stmt = $conn->prepare("UPDATE landing_news SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        logAudit($conn, ($status === 'active' ? 'เปิด' : 'ปิด') . 'ข่าวประชาสัมพันธ์', 'landing_news', $id, '');
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

    $titleRes = $conn->query("SELECT title FROM landing_news WHERE id = $id");
    $titleRow = $titleRes ? $titleRes->fetch_assoc() : null;

    $stmt = $conn->prepare("DELETE FROM landing_news WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAudit($conn, 'ลบข่าวประชาสัมพันธ์', 'landing_news', $id, json_encode(['title' => $titleRow['title'] ?? '']));
        $stmt->close();
        jsonResponse(true, 'ลบข่าวเรียบร้อยแล้ว');
    } else {
        $stmt->close();
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
}

// ============================
// GET single news (for editing)
// ============================
if ($action === 'get') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }
    $res = $conn->query("SELECT * FROM landing_news WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        jsonResponse(true, 'success', $row);
    } else {
        jsonResponse(false, 'ไม่พบข้อมูล');
    }
}

jsonResponse(false, 'Invalid action');
