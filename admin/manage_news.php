<?php
/**
 * Admin - จัดการข่าวประชาสัมพันธ์หน้าเว็บ
 */
$pageTitle = 'จัดการข่าวประชาสัมพันธ์';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all news
$news = [];
$res = $conn->query("SELECT * FROM landing_news ORDER BY publish_date DESC, id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $news[] = $row;
    }
}
?>

<style>
    .news-manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .news-manage-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .news-manage-card .card-gradient-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1a237e, #3949ab);
    }

    .news-category-badge {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 600;
        background: rgba(26, 35, 126, .1);
        color: #1a237e;
    }

    .status-badge {
        font-size: .8rem;
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
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-newspaper me-2 text-primary"></i>จัดการข่าวประชาสัมพันธ์</h4>
        <p class="text-muted mb-0">เพิ่ม แก้ไข และจัดการข่าวสารที่แสดงบนหน้าเว็บไซต์</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus me-1"></i>เพิ่มข่าวใหม่
    </button>
</div>

<?php if (empty($news)): ?>
    <div class="empty-state">
        <i class="fas fa-newspaper"></i>
        <h5>ยังไม่มีข่าวประชาสัมพันธ์</h5>
        <p>กดปุ่ม "เพิ่มข่าวใหม่" เพื่อเริ่มต้น</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($news as $item): ?>
            <div class="col-12" id="news-card-<?= $item['id'] ?>">
                <div class="news-manage-card">
                    <div class="card-gradient-bar"></div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="news-category-badge">
                                    <?= htmlspecialchars($item['category']) ?>
                                </span>
                                <?php if ($item['status'] === 'active'): ?>
                                    <span class="badge bg-success status-badge"><i class="fas fa-eye me-1"></i>แสดงผล</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary status-badge"><i class="fas fa-eye-slash me-1"></i>ซ่อน</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($item['title']) ?>
                            </h6>
                            <p class="text-muted mb-1" style="font-size:.88rem">
                                <?= htmlspecialchars($item['excerpt']) ?>
                            </p>
                            <small class="text-muted"><i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/', strtotime($item['publish_date'])) . (date('Y', strtotime($item['publish_date'])) + 543) ?>
                            </small>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" <?= $item['status'] === 'active' ? 'checked' : '' ?>
                                onchange="toggleStatus(
                        <?= $item['id'] ?>, this.checked)">
                            </div>
                            <button class="btn btn-sm btn-outline-primary btn-action"
                                onclick='editNews(<?= json_encode($item, JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action"
                                onclick="deleteNews(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['title']), ENT_QUOTES) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal -->
<div class="modal fade" id="newsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #1a237e, #3949ab); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-newspaper me-2"></i>เพิ่มข่าวใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="newsId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">หัวข้อข่าว <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newsTitle" placeholder="กรอกหัวข้อข่าว">
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">หมวดหมู่</label>
                        <select class="form-select" id="newsCategory">
                            <option value="ข่าวองค์กร">ข่าวองค์กร</option>
                            <option value="แผนงาน">แผนงาน</option>
                            <option value="ฝึกอบรม">ฝึกอบรม</option>
                            <option value="ประกาศ">ประกาศ</option>
                            <option value="กิจกรรม">กิจกรรม</option>
                            <option value="ทั่วไป">ทั่วไป</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">วันที่ประกาศ</label>
                        <input type="date" class="form-control" id="newsDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">สถานะ</label>
                        <select class="form-select" id="newsStatus">
                            <option value="active">แสดงผล</option>
                            <option value="inactive">ซ่อน</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">รูปภาพปก</label>
                    <input type="file" class="form-control" id="newsCoverImage" accept="image/*">
                    <div id="coverPreview" class="mt-2" style="display:none;">
                        <img id="coverPreviewImg" src="" style="max-height:120px;border-radius:10px;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">เนื้อหาย่อ <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="newsExcerpt" rows="2"
                        placeholder="กรอกเนื้อหาย่อของข่าว..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">เนื้อหาเต็ม (Markdown)</label>
                    <textarea class="form-control" id="newsContent"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveNews()">
                    <i class="fas fa-save me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- EasyMDE CSS/JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<?php
$baseUrl = BASE_URL;
$extraJs = <<<JS
<script>
var BASE_URL = '{$baseUrl}';
var easyMDE = null;

document.getElementById('newsModal').addEventListener('shown.bs.modal', function() {
    if (!easyMDE) {
        easyMDE = new EasyMDE({
            element: document.getElementById('newsContent'),
            spellChecker: false,
            placeholder: 'เขียนเนื้อหาข่าวเต็มที่นี่ (รองรับ Markdown)...',
            minHeight: '250px',
            status: false,
            toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image', 'code', '|', 'preview', 'side-by-side', 'fullscreen']
        });
    }
});

// Cover image preview
document.getElementById('newsCoverImage').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('coverPreviewImg').src = ev.target.result;
            document.getElementById('coverPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function openModal() {
    document.getElementById('newsId').value = '';
    document.getElementById('newsTitle').value = '';
    document.getElementById('newsCategory').value = 'ข่าวองค์กร';
    document.getElementById('newsDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('newsExcerpt').value = '';
    document.getElementById('newsStatus').value = 'active';
    document.getElementById('newsCoverImage').value = '';
    document.getElementById('coverPreview').style.display = 'none';
    if (easyMDE) easyMDE.value('');
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มข่าวใหม่';
    new bootstrap.Modal(document.getElementById('newsModal')).show();
}

function editNews(item) {
    // For simple inline data only — we also fetch full content from server
    document.getElementById('newsId').value = item.id;
    document.getElementById('newsTitle').value = item.title;
    document.getElementById('newsCategory').value = item.category;
    document.getElementById('newsDate').value = item.publish_date;
    document.getElementById('newsExcerpt').value = item.excerpt;
    document.getElementById('newsStatus').value = item.status;
    document.getElementById('newsCoverImage').value = '';
    document.getElementById('coverPreview').style.display = 'none';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขข่าว';

    // Show cover preview if exists
    if (item.cover_image) {
        document.getElementById('coverPreviewImg').src = BASE_URL + '/uploads/news/' + item.cover_image;
        document.getElementById('coverPreview').style.display = 'block';
    }

    // Fetch full content
    var fd = new FormData();
    fd.append('action', 'get');
    fd.append('id', item.id);
    fetch(BASE_URL + '/actions/save_news.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                var modal = new bootstrap.Modal(document.getElementById('newsModal'));
                modal.show();
                setTimeout(() => {
                    if (easyMDE) easyMDE.value(data.data.content || '');
                }, 300);
            } else {
                new bootstrap.Modal(document.getElementById('newsModal')).show();
            }
        })
        .catch(() => {
            new bootstrap.Modal(document.getElementById('newsModal')).show();
        });
}

function saveNews() {
    var id = document.getElementById('newsId').value;
    var formData = new FormData();
    formData.append('action', id ? 'update' : 'create');
    if (id) formData.append('id', id);
    formData.append('title', document.getElementById('newsTitle').value);
    formData.append('category', document.getElementById('newsCategory').value);
    formData.append('publish_date', document.getElementById('newsDate').value);
    formData.append('excerpt', document.getElementById('newsExcerpt').value);
    formData.append('content', easyMDE ? easyMDE.value() : '');
    formData.append('status', document.getElementById('newsStatus').value);

    // Cover image
    var fileInput = document.getElementById('newsCoverImage');
    if (fileInput.files.length > 0) {
        formData.append('cover_image', fileInput.files[0]);
    }

    fetch(BASE_URL + '/actions/save_news.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('newsModal')).hide();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => showToast('เกิดข้อผิดพลาด', 'danger'));
}

function toggleStatus(id, checked) {
    var formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', id);
    formData.append('status', checked ? 'active' : 'inactive');

    fetch(BASE_URL + '/actions/save_news.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => location.reload(), 800);
        });
}

function deleteNews(id, title) {
    if (!confirm('ต้องการลบข่าว "' + title + '" หรือไม่?')) return;
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(BASE_URL + '/actions/save_news.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('news-card-' + id)?.remove();
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