<?php
/**
 * Admin - เขียน/แก้ไข บทความ
 */
$pageTitle = isset($_GET['id']) ? 'แก้ไขบทความ' : 'เขียนบทความใหม่';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'cover_image' => '',
    'status' => 'published',
    'tags' => []
];

// If editing, fetch existing data
if ($id > 0) {
    $res = $conn->query("SELECT * FROM blog_posts WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $post = $res->fetch_assoc();

        // Fetch tags mapping
        $tagsRes = $conn->query("SELECT tag_id FROM blog_post_tags WHERE post_id = $id");
        $post['tags'] = [];
        while ($t = $tagsRes->fetch_assoc()) {
            $post['tags'][] = $t['tag_id'];
        }
    } else {
        echo "<script>alert('ไม่พบบทความ'); window.location='manage_posts.php';</script>";
        exit;
    }
}

// Fetch all available tags
$allTags = [];
$resTags = $conn->query("SELECT * FROM blog_tags ORDER BY name ASC");
if ($resTags) {
    while ($r = $resTags->fetch_assoc()) {
        $allTags[] = $r;
    }
}
?>

<!-- EasyMDE Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<!-- Select2 Styles -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .editor-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #3949ab;
        box-shadow: 0 0 0 0.25rem rgba(57, 73, 171, 0.25);
    }

    /* Select2 overrides for Bootstrap 5 feel */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 38px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3949ab;
        box-shadow: 0 0 0 0.25rem rgba(57, 73, 171, 0.25);
    }

    .cover-preview {
        width: 100%;
        max-height: 250px;
        object-fit: contain;
        border-radius: 8px;
        margin-top: 10px;
        display: <?= !empty($post['cover_image']) ? 'block' : 'none' ?>;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <a href="manage_posts.php" class="text-muted text-decoration-none me-2"><i
                    class="fas fa-arrow-left"></i></a>
            <?= $id > 0 ? 'แก้ไขบทความ: ' . htmlspecialchars($post['title']) : 'เขียนบทความใหม่' ?>
        </h4>
    </div>
    <button class="btn btn-primary px-4" id="btnSave" onclick="submitPost()">
        <i class="fas fa-save me-2"></i>บันทึกบทความ
    </button>
</div>

<div class="row">
    <div class="col-xl-8">
        <!-- Main Editor Area -->
        <div class="editor-container mb-4">
            <div class="mb-4">
                <label class="form-label fw-bold">หัวข้อบทความ (Title) <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="postTitle"
                    value="<?= htmlspecialchars($post['title']) ?>" placeholder="พิมพ์หัวข้อบทความที่น่าสนใจ..."
                    required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold text-muted">URL Slug (เว้นว่างไว้เพื่อสร้างอัตโนมัติจากหัวข้อ)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted">
                        <?= BASE_URL ?>/blog_detail.php?slug=
                    </span>
                    <input type="text" class="form-control" id="postSlug" value="<?= htmlspecialchars($post['slug']) ?>"
                        placeholder="my-awesome-post">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">เนื้อหา (Markdown) <span class="text-danger">*</span></label>
                <textarea id="postContent"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <!-- Sidebar Options -->
        <div class="editor-container mb-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">การเผยแพร่ & แสดงผล</h5>

            <div class="mb-4">
                <label class="form-label fw-bold">สถานะบทความ</label>
                <select class="form-select text-dark fw-bold" id="postStatus" style="font-size: 1.05rem;">
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>✅ เผยแพร่ทันที
                        (Published)</option>
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>📝 บันทึกแบบร่าง (Draft)
                    </option>
                    <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>🔒 เก็บถาวร (Archived)
                    </option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">หมวดหมู่ / Tags</label>
                <select class="form-select" id="postTags" multiple="multiple">
                    <?php foreach ($allTags as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= in_array($t['id'], $post['tags']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">ข้อความย่อ (Excerpt)</label>
                <textarea class="form-control" id="postExcerpt" rows="3"
                    placeholder="ข้อความสรุป 2-3 บรรทัด เพื่อใช้แสดงเป็นคำโปรยบนการ์ด"><?= htmlspecialchars($post['excerpt']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">รูปภาพหน้าปก (Cover Image)</label>
                <input type="file" class="form-control" id="postCover" accept="image/*" onchange="previewImage(this)">
                <img id="imgPreview" class="cover-preview mt-3"
                    src="<?= !empty($post['cover_image']) ? BASE_URL . '/uploads/blog/' . $post['cover_image'] : '' ?>">
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<?php
$baseUrl = BASE_URL;
$postId = $id;
$extraJs = <<<JS
<script>
var BASE_URL = '{$baseUrl}';
var POST_ID = {$postId};
var easyMDE;

$(document).ready(function() {
    // Initialize Select2
    $('#postTags').select2({
        placeholder: "ค้นหาหรือเลือกแท็ก...",
        allowClear: true,
        width: '100%'
    });
    
    // Initialize EasyMDE
    easyMDE = new EasyMDE({ 
        element: document.getElementById('postContent'),
        spellChecker: false,
        placeholder: "เริ่มเขียนบทความแบบ Markdown ได้ที่นี่...",
        minHeight: "400px",
        maxHeight: "800px",
        toolbar: [
            "bold", "italic", "heading", "|", 
            "quote", "unordered-list", "ordered-list", "|", 
            "link", "image", "code", "table", "|", 
            "preview", "side-by-side", "fullscreen", "|", 
            "guide"
        ]
    });
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').style.display = 'block';
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function submitPost() {
    var title = document.getElementById('postTitle').value.trim();
    var content = easyMDE.value().trim();
    
    if (!title || !content) {
        showToast('กรุณากรอกหัวข้อและเนื้อหาบทความ', 'warning');
        return;
    }
    
    var btn = document.getElementById('btnSave');
    var originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('action', POST_ID > 0 ? 'update' : 'create');
    if (POST_ID > 0) formData.append('id', POST_ID);
    
    formData.append('title', title);
    formData.append('slug', document.getElementById('postSlug').value.trim());
    formData.append('content', content);
    formData.append('excerpt', document.getElementById('postExcerpt').value.trim());
    formData.append('status', document.getElementById('postStatus').value);
    
    var tags = $('#postTags').val();
    if (tags) {
        tags.forEach(function(tag) {
            formData.append('tags[]', tag);
        });
    }
    
    var coverInput = document.getElementById('postCover');
    if (coverInput.files.length > 0) {
        formData.append('cover_image', coverInput.files[0]);
    }

    fetch(BASE_URL + '/actions/save_blog_posts.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'manage_posts.php';
                }, 1500);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'danger');
        });
}

function showToast(msg, type) {
    var html = '<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed" style="top:80px;right:20px;z-index:9999;min-width:300px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15)">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    document.body.insertAdjacentHTML('beforeend', html);
    setTimeout(() => { var a = document.querySelector('.alert'); if(a) a.remove(); }, 3000);
}
</script>
JS;
require_once __DIR__ . '/../includes/footer.php';
?>