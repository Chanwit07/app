<?php
// actions/save_blog_posts.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json; charset=utf-8');

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

// Function to generate slug safely
function createSlug($string)
{
    // Replace non-alphanumeric with hyphens
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($string));
    return trim($slug, '-');
}

// Function to calculate read time (approximated)
function calculateReadTime($text)
{
    $wordCount = mb_substr_count($text, ' ') + (mb_strlen(strip_tags($text)) / 10); // Thai doesn't use spaces much, approx by char length / 10 
    $minutes = ceil($wordCount / 200);
    return max(1, $minutes);
}


if ($action === 'delete') {
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    // Get image to delete file
    $imgRes = $conn->query("SELECT cover_image FROM blog_posts WHERE id = $id");
    if ($imgRes && $imgRes->num_rows > 0) {
        $img = $imgRes->fetch_assoc()['cover_image'];
        if (!empty($img) && file_exists(__DIR__ . '/../uploads/blog/' . $img)) {
            unlink(__DIR__ . '/../uploads/blog/' . $img);
        }
    }

    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'ลบบทความเรียบร้อย']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบ']);
    }
    exit;
}


// Fields for create/update
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$content = trim($_POST['content'] ?? '');
$status = $_POST['status'] ?? 'draft';
$tags = isset($_POST['tags']) ? $_POST['tags'] : []; // array of tag IDs
$author_id = $_SESSION['user_id'] ?? 1;

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกหัวข้อและเนื้อหาบทความ']);
    exit;
}

if (empty($slug)) {
    $slug = createSlug($title);
    if (empty($slug))
        $slug = 'post-' . time();
}

$read_time = calculateReadTime($content);

// File Upload Logic
$cover_image = null;
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/blog/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileInfo = pathinfo($_FILES['cover_image']['name']);
    $ext = strtolower($fileInfo['extension']);
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (in_array($ext, $allowed)) {
        $cover_image = 'cover_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $cover_image);
    } else {
        echo json_encode(['success' => false, 'message' => 'รูปแบบไฟล์รูปภาพไม่รองรับ']);
        exit;
    }
}

if ($action === 'create') {
    // Check duplicate slug
    $checkRes = $conn->query("SELECT id FROM blog_posts WHERE slug = '" . $conn->real_escape_string($slug) . "'");
    if ($checkRes->num_rows > 0) {
        $slug .= '-' . rand(100, 999);
    }

    $stmt = $conn->prepare("INSERT INTO blog_posts (slug, title, excerpt, content, cover_image, author_id, read_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $slug, $title, $excerpt, $content, $cover_image, $author_id, $read_time, $status);

    if ($stmt->execute()) {
        $post_id = $stmt->insert_id;

        // Insert tags mapping
        if (!empty($tags) && is_array($tags)) {
            $tagStmt = $conn->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($tags as $t_id) {
                $t_id = (int) $t_id;
                $tagStmt->bind_param("ii", $post_id, $t_id);
                $tagStmt->execute();
            }
        }

        echo json_encode(['success' => true, 'message' => 'สร้างบทความเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    exit;

} elseif ($action === 'update') {
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    // Check duplicate slug
    $checkRes = $conn->query("SELECT id FROM blog_posts WHERE slug = '" . $conn->real_escape_string($slug) . "' AND id != $id");
    if ($checkRes->num_rows > 0) {
        $slug .= '-' . rand(100, 999);
    }

    if ($cover_image) {
        // delete old image
        $oldRes = $conn->query("SELECT cover_image FROM blog_posts WHERE id = $id");
        if ($oldRes && $oldRes->num_rows > 0) {
            $oldImg = $oldRes->fetch_assoc()['cover_image'];
            if (!empty($oldImg) && file_exists(__DIR__ . '/../uploads/blog/' . $oldImg)) {
                unlink(__DIR__ . '/../uploads/blog/' . $oldImg);
            }
        }

        $stmt = $conn->prepare("UPDATE blog_posts SET slug=?, title=?, excerpt=?, content=?, cover_image=?, read_time=?, status=? WHERE id=?");
        $stmt->bind_param("sssssssi", $slug, $title, $excerpt, $content, $cover_image, $read_time, $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE blog_posts SET slug=?, title=?, excerpt=?, content=?, read_time=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssi", $slug, $title, $excerpt, $content, $read_time, $status, $id);
    }

    if ($stmt->execute()) {
        // Delete old tags mapping
        $conn->query("DELETE FROM blog_post_tags WHERE post_id = $id");

        // Insert new tags mapping
        if (!empty($tags) && is_array($tags)) {
            $tagStmt = $conn->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($tags as $t_id) {
                $t_id = (int) $t_id;
                $tagStmt->bind_param("ii", $id, $t_id);
                $tagStmt->execute();
            }
        }

        echo json_encode(['success' => true, 'message' => 'อัปเดตบทความเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
