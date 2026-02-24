<?php
/**
 * Admin - จัดการหมวดหมู่/แท็ก (Blog Tags)
 */
$pageTitle = 'จัดการหมวดหมู่/แท็ก';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all tags
$tags = [];
$res = $conn->query("SELECT * FROM blog_tags ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // format date optionally
        $tags[] = $row;
    }
}

// color map translation
$colorNames = [
    'primary' => 'สีน้ำเงิน (Primary)',
    'secondary' => 'สีเทา (Secondary)',
    'success' => 'สีเขียว (Success)',
    'danger' => 'สีแดง (Danger)',
    'warning' => 'สีเหลือง (Warning)',
    'info' => 'สีฟ้า (Info)',
    'dark' => 'สีดำ (Dark)'
];
?>

<style>
    .tag-manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .tag-manage-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .btn-action {
        border-radius: 8px;
        transition: all .3s;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
        opacity: .7;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        display: block;
    }

    .blog-tag-badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.8rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-tags me-2 text-primary"></i>จัดการหมวดหมู่/แท็ก</h4>
        <p class="text-muted mb-0">เพิ่ม แก้ไข และลบแท็กสำหรับจัดหมวดหมู่บทความ Blog</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus me-1"></i>เพิ่มแท็กใหม่
    </button>
</div>

<?php if (empty($tags)): ?>
    <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h5>ยังไม่มีแท็ก</h5>
        <p>กดปุ่ม "เพิ่มแท็กใหม่" เพื่อเริ่มต้น</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($tags as $item): ?>
            <div class="col-12 col-md-4 col-lg-3" id="tag-card-<?= $item['id'] ?>">
                <div class="tag-manage-card text-center d-flex flex-column h-100">
                    <div class="mb-3 flex-grow-1 d-flex align-items-center justify-content-center">
                        <span class="badge bg-<?= htmlspecialchars($item['color_code']) ?> blog-tag-badge">
                            <i class="fas fa-hashtag me-1"></i>
                            <?= htmlspecialchars($item['name']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-auto">
                        <button class="btn btn-sm btn-outline-primary btn-action"
                            onclick='editTag(<?= json_encode($item, JSON_UNESCAPED_UNICODE) ?>)'>
                            <i class="fas fa-edit me-1"></i>แก้ไข
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action"
                            onclick="deleteTag(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name']), ENT_QUOTES) ?>')">
                            <i class="fas fa-trash me-1"></i>ลบ
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #1a237e, #3949ab); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-tag me-2"></i>เพิ่มแท็กใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="tagId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">ชื่อแท็ก <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tagName" placeholder="เช่น เทคโนโลยี, ข่าวสาร, คู่มือ">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">สีของป้ายกำกับ (Color) <span
                            class="text-danger">*</span></label>
                    <select class="form-select" id="tagColor">
                        <?php foreach ($colorNames as $code => $label): ?>
                            <option value="<?= $code ?>">
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-4 p-3 bg-light rounded text-center">
                    <label class="form-label fw-semibold d-block mb-2">ตัวอย่างผลลัพธ์:</label>
                    <span id="previewTag" class="badge bg-primary blog-tag-badge">
                        <i class="fas fa-hashtag me-1"></i><span id="previewText">ชื่อแท็ก</span>
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveTag()">
                    <i class="fas fa-save me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$baseUrl = BASE_URL;
$extraJs = <<<JS
<script>
var BASE_URL = '{$baseUrl}';

// Live Preview
document.getElementById('tagName').addEventListener('input', updatePreview);
document.getElementById('tagColor').addEventListener('change', updatePreview);

function updatePreview() {
    var name = document.getElementById('tagName').value;
    var color = document.getElementById('tagColor').value;
    if(!name) name = 'ชื่อแท็ก';
    var previewText = document.getElementById('previewText');
    var previewTag = document.getElementById('previewTag');
    
    previewText.textContent = name;
    previewTag.className = 'badge bg-' + color + ' blog-tag-badge';
}

function openModal() {
    document.getElementById('tagId').value = '';
    document.getElementById('tagName').value = '';
    document.getElementById('tagColor').value = 'primary';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มแท็กใหม่';
    updatePreview();
    new bootstrap.Modal(document.getElementById('tagModal')).show();
}

function editTag(item) {
    document.getElementById('tagId').value = item.id;
    document.getElementById('tagName').value = item.name;
    document.getElementById('tagColor').value = item.color_code;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขแท็ก';
    updatePreview();
    new bootstrap.Modal(document.getElementById('tagModal')).show();
}

function saveTag() {
    var id = document.getElementById('tagId').value;
    var name = document.getElementById('tagName').value;
    var color = document.getElementById('tagColor').value;
    
    if(!name) {
        showToast('กรุณากรอกชื่อแท็ก', 'warning');
        return;
    }

    var formData = new FormData();
    formData.append('action', id ? 'update' : 'create');
    if(id) formData.append('id', id);
    formData.append('name', name);
    formData.append('color_code', color);

    fetch(BASE_URL + '/actions/save_blog_tags.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => showToast('เกิดข้อผิดพลาด', 'danger'));
}

function deleteTag(id, name) {
    if (!confirm('ต้องการลบแท็ก "' + name + '" หรือไม่? บทความที่มีแท็กนี้จะถูกนำแท็กออกอัตโนมัติ')) return;
    
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(BASE_URL + '/actions/save_blog_tags.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tag-card-' + id)?.remove();
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
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