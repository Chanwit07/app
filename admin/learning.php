<?php
/**
 * Admin - Learning Materials Management (CRUD)
 */
$pageTitle = 'จัดการสื่อการเรียนรู้';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all learning materials
$materials = [];
$res = $conn->query("SELECT lm.*, u.fullname as creator_name FROM learning_materials lm LEFT JOIN users u ON lm.created_by = u.id ORDER BY lm.sort_order ASC, lm.created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $materials[] = $row;
    }
}
?>

<div class="animate-fadeInUp">

    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1"><i class="fas fa-graduation-cap me-2 text-primary"></i>สื่อการเรียนรู้ทั้งหมด (
                <?= count($materials) ?> รายการ)
            </h5>
            <small class="text-muted">จัดการวิดีโอสื่อการเรียนรู้จาก YouTube</small>
        </div>
        <button class="btn btn-primary-gradient" onclick="openAddModal()">
            <i class="fas fa-plus me-1"></i>เพิ่มสื่อการเรียนรู้
        </button>
    </div>

    <!-- Materials Table -->
    <div class="form-section">
        <div class="table-responsive">
            <table class="table table-modern" id="learningTable">
                <thead>
                    <tr>
                        <th style="width:50px">ลำดับ</th>
                        <th style="width:120px">ตัวอย่าง</th>
                        <th>ชื่อบทเรียน</th>
                        <th>หมวดหมู่</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th style="width:150px">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-video fa-3x mb-3 d-block opacity-25"></i>
                                ยังไม่มีสื่อการเรียนรู้ คลิก "เพิ่มสื่อการเรียนรู้" เพื่อเริ่มต้น
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($materials as $idx => $m): ?>
                            <tr id="row-<?= $m['id'] ?>">
                                <td class="text-center fw-bold">
                                    <?= $m['sort_order'] ?: ($idx + 1) ?>
                                </td>
                                <td>
                                    <img src="https://img.youtube.com/vi/<?= htmlspecialchars($m['youtube_video_id']) ?>/mqdefault.jpg"
                                        alt="thumbnail" class="rounded shadow-sm"
                                        style="width:100px; height:56px; object-fit:cover; cursor:pointer;"
                                        onclick="window.open('https://www.youtube.com/watch?v=<?= htmlspecialchars($m['youtube_video_id']) ?>', '_blank')">
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= sanitize($m['title']) ?>
                                    </div>
                                    <?php if ($m['description']): ?>
                                        <small class="text-muted">
                                            <?= mb_strimwidth(sanitize($m['description']), 0, 80, '...') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['category']): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?= sanitize($m['category']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'active'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>เปิดใช้งาน</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-eye-slash me-1"></i>ปิดใช้งาน</span>
                                    <?php endif; ?>
                                </td>
                                <td><small>
                                        <?= thaiDate($m['created_at'], 'compact') ?>
                                    </small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" onclick="editItem(<?= $m['id'] ?>)"
                                            title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            class="btn btn-outline-<?= $m['status'] === 'active' ? 'warning' : 'success' ?> btn-sm"
                                            onclick="toggleStatus(<?= $m['id'] ?>, '<?= $m['status'] === 'active' ? 'inactive' : 'active' ?>')"
                                            title="<?= $m['status'] === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                                            <i class="fas fa-<?= $m['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="deleteItem(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>')" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="learningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <h5 class="modal-title text-white" id="modalTitle">
                    <i class="fas fa-graduation-cap me-2"></i>เพิ่มสื่อการเรียนรู้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="learningForm">
                    <input type="hidden" name="id" id="formId">
                    <input type="hidden" name="action" id="formAction" value="create">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อบทเรียน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="formTitle" required
                            placeholder="เช่น วิธีการตรวจสอบสินทรัพย์">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ลิงก์ YouTube <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger text-white"><i class="fab fa-youtube"></i></span>
                            <input type="url" class="form-control" name="youtube_url" id="formYoutubeUrl" required
                                placeholder="https://www.youtube.com/watch?v=xxxxx หรือ https://youtu.be/xxxxx">
                        </div>
                        <div class="form-text">รองรับลิงก์ทุกรูปแบบจาก YouTube (แบบเต็ม, แบบย่อ, embed)</div>
                        <!-- Preview -->
                        <div id="videoPreview" class="mt-2 d-none">
                            <img id="previewThumb" class="rounded shadow-sm"
                                style="width:200px; height:112px; object-fit:cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รายละเอียด</label>
                        <textarea class="form-control" name="description" id="formDescription" rows="3"
                            placeholder="อธิบายเนื้อหาของวิดีโอโดยย่อ..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">หมวดหมู่</label>
                            <input type="text" class="form-control" name="category" id="formCategory"
                                placeholder="เช่น สินทรัพย์, พัสดุ, ทั่วไป">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">ลำดับการแสดงผล</label>
                            <input type="number" class="form-control" name="sort_order" id="formSortOrder" value="0"
                                min="0" placeholder="0 = ไม่จัดลำดับ">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary-gradient" onclick="submitForm()">
                    <i class="fas fa-save me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
const BASE = document.querySelector('link[href*="style.css"]').href.replace('/assets/css/style.css', '');
const modal = new bootstrap.Modal(document.getElementById('learningModal'));

// ---- YouTube Preview ----
document.getElementById('formYoutubeUrl').addEventListener('input', function() {
    const url = this.value;
    const videoId = extractYoutubeId(url);
    const preview = document.getElementById('videoPreview');
    if (videoId) {
        document.getElementById('previewThumb').src = 'https://img.youtube.com/vi/' + videoId + '/mqdefault.jpg';
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

function extractYoutubeId(url) {
    const match = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
    return match ? match[1] : null;
}

// ---- Modal Open ----
function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>เพิ่มสื่อการเรียนรู้';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('learningForm').reset();
    document.getElementById('videoPreview').classList.add('d-none');
    modal.show();
}

function editItem(id) {
    fetch(BASE + '/actions/save_learning.php?action=get&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast(data.message, 'error'); return; }
            const item = data.item;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขสื่อการเรียนรู้';
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = item.id;
            document.getElementById('formTitle').value = item.title;
            document.getElementById('formYoutubeUrl').value = item.youtube_url;
            document.getElementById('formDescription').value = item.description || '';
            document.getElementById('formCategory').value = item.category || '';
            document.getElementById('formSortOrder').value = item.sort_order || 0;
            // Show preview
            document.getElementById('previewThumb').src = 'https://img.youtube.com/vi/' + item.youtube_video_id + '/mqdefault.jpg';
            document.getElementById('videoPreview').classList.remove('d-none');
            modal.show();
        })
        .catch(() => showToast('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error'));
}

// ---- Submit Form ----
function submitForm() {
    const form = document.getElementById('learningForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const fd = new FormData(form);
    fetch(BASE + '/actions/save_learning.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                modal.hide();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(() => showToast('เกิดข้อผิดพลาดในการบันทึก', 'error'));
}

// ---- Toggle Status ----
function toggleStatus(id, newStatus) {
    const label = newStatus === 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
    confirmAction('ยืนยันการ' + label, 'ต้องการ' + label + 'สื่อการเรียนรู้นี้?', function() {
        const fd = new FormData();
        fd.append('action', 'toggle_status');
        fd.append('id', id);
        fd.append('new_status', newStatus);
        fetch(BASE + '/actions/save_learning.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 800);
            });
    });
}

// ---- Delete ----
function deleteItem(id, title) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        html: 'ต้องการลบ <b>"' + title + '"</b> ?<br><small class="text-muted">การลบจะไม่สามารถกู้คืนได้</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: '<i class="fas fa-trash me-1"></i>ลบ',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'font-prompt' }
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(BASE + '/actions/save_learning.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        document.getElementById('row-' + id)?.remove();
                    }
                });
        }
    });
}
</script>
JS;

require_once __DIR__ . '/../includes/footer.php';
?>