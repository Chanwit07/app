<?php
/**
 * Admin - จัดการรายงาน Dashboard (Power BI / URL)
 */
$pageTitle = 'จัดการรายงาน Dashboard';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all reports
$reports = [];
$res = $conn->query("SELECT r.*, u.fullname as creator_name FROM dashboard_reports r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.sort_order ASC, r.id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reports[] = $row;
    }
}
?>

<style>
    .report-manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .report-manage-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .report-manage-card .card-gradient-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .report-manage-card .card-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .report-manage-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        flex-shrink: 0;
    }

    .report-manage-card .card-info {
        flex: 1;
        min-width: 0;
    }

    .report-manage-card .card-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .report-manage-card .card-desc {
        font-size: 0.78rem;
        color: #999;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .report-manage-card .card-url {
        font-size: 0.72rem;
        color: #a0aec0;
        word-break: break-all;
        max-height: 2.5em;
        overflow: hidden;
    }

    .report-manage-card .card-actions {
        display: flex;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    .report-manage-card .card-actions .btn {
        border-radius: 10px;
        font-size: 0.78rem;
        padding: 0.35rem 0.6rem;
    }

    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 0.4rem;
        max-height: 200px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .icon-picker-item {
        width: 40px;
        height: 40px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        color: #4a5568;
        transition: var(--transition);
    }

    .icon-picker-item:hover,
    .icon-picker-item.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .color-preview {
        width: 100%;
        height: 40px;
        border-radius: 12px;
        margin-top: 0.5rem;
    }

    @media (max-width: 767.98px) {
        .icon-picker-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }
</style>

<div class="animate-fadeInUp">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon"
                style="background: linear-gradient(135deg, #667eea, #764ba2); width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chart-line text-white" style="font-size: 1.3rem;"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">จัดการรายงาน Dashboard</h5>
                <small class="text-muted">เพิ่ม/แก้ไข ลิงก์ Power BI หรือ Dashboard URL</small>
            </div>
        </div>
        <button class="btn btn-primary-gradient" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>เพิ่มรายงานใหม่
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-glass p-3 text-center">
                <div class="stat-value" style="font-size: 1.8rem;">
                    <?= count($reports) ?>
                </div>
                <div class="stat-label">รายงานทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-glass p-3 text-center">
                <div class="stat-value text-success" style="font-size: 1.8rem;">
                    <?= count(array_filter($reports, fn($r) => $r['is_active'])) ?>
                </div>
                <div class="stat-label">เปิดใช้งาน</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-glass p-3 text-center">
                <div class="stat-value text-warning" style="font-size: 1.8rem;">
                    <?= count(array_filter($reports, fn($r) => !$r['is_active'])) ?>
                </div>
                <div class="stat-label">ปิดใช้งาน</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-glass p-3 text-center">
                <a href="<?= BASE_URL ?>/reports.php" class="text-decoration-none">
                    <div class="stat-value text-primary" style="font-size: 1.8rem;">
                        <i class="fas fa-external-link-alt" style="font-size: 1.4rem;"></i>
                    </div>
                    <div class="stat-label">ดูหน้ารายงาน</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <?php if (empty($reports)): ?>
        <div class="card-glass">
            <div class="empty-state">
                <i class="fas fa-chart-area d-block"></i>
                <p>ยังไม่มีรายงาน Dashboard — คลิก "เพิ่มรายงานใหม่" เพื่อเริ่มต้น</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reports as $idx => $r): ?>
                <div class="col-12" id="report-card-<?= $r['id'] ?>">
                    <div class="report-manage-card">
                        <div class="card-gradient-bar"
                            style="background: linear-gradient(90deg, <?= sanitize($r['color_from']) ?>, <?= sanitize($r['color_to']) ?>);">
                        </div>
                        <div class="card-content">
                            <div class="card-icon"
                                style="background: linear-gradient(135deg, <?= sanitize($r['color_from']) ?>, <?= sanitize($r['color_to']) ?>);">
                                <i class="fas <?= sanitize($r['icon']) ?>"></i>
                            </div>
                            <div class="card-info">
                                <div class="card-title">
                                    <?= sanitize($r['title']) ?>
                                    <?php if (!$r['is_active']): ?>
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">ปิดใช้งาน</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($r['description'])): ?>
                                    <div class="card-desc">
                                        <?= sanitize($r['description']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="card-url">
                                    <i class="fas fa-link me-1"></i>
                                    <?= sanitize($r['embed_url']) ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-sort me-1"></i>ลำดับ:
                                        <?= $r['sort_order'] ?>
                                        <?php if (!empty($r['creator_name'])): ?>
                                            &nbsp;|&nbsp;
                                            <i class="fas fa-user me-1"></i>
                                            <?= sanitize($r['creator_name']) ?>
                                        <?php endif; ?>
                                        &nbsp;|&nbsp;
                                        <i class="fas fa-clock me-1"></i>
                                        <?= thaiDate($r['created_at'], 'compact') ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick='editReport(<?= json_encode($r, JSON_UNESCAPED_UNICODE) ?>)' title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-<?= $r['is_active'] ? 'warning' : 'success' ?> btn-sm"
                                    onclick="toggleActive(<?= $r['id'] ?>, <?= $r['is_active'] ? 0 : 1 ?>)"
                                    title="<?= $r['is_active'] ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                                    <i class="fas <?= $r['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteReport(<?= $r['id'] ?>, '<?= sanitize($r['title']) ?>')" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 pb-0"
                style="background: linear-gradient(135deg, rgba(102,126,234,0.05), rgba(118,75,162,0.05));">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>เพิ่มรายงานใหม่
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="reportForm">
                    <input type="hidden" id="reportId" name="id" value="">

                    <div class="row g-3">
                        <!-- Title -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-1 text-primary"></i>ชื่อรายงาน <span
                                    class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="reportTitle" name="title" required
                                placeholder="เช่น Power BI - สรุปยอดรายเดือน">
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-folder me-1 text-info"></i>หมวดหมู่
                            </label>
                            <select class="form-select" id="reportCategory" name="category">
                                <option value="general">ทั่วไป</option>
                                <option value="finance">การเงิน</option>
                                <option value="operations">ปฏิบัติการ</option>
                                <option value="maintenance">บำรุงรักษา</option>
                                <option value="inventory">ทรัพย์สิน</option>
                                <option value="hr">ทรัพยากรบุคคล</option>
                                <option value="other">อื่นๆ</option>
                            </select>
                        </div>

                        <!-- URL -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-link me-1 text-primary"></i>Embed URL <span
                                    class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="reportUrl" name="embed_url" rows="3" required
                                placeholder="วาง URL ของ Power BI Embed หรือ URL ของ Dashboard อื่นๆ ที่นี่"></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>สำหรับ Power BI: ใช้ลิงก์ "Publish to web"
                                หรือ "Embed" URL
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1 text-muted"></i>คำอธิบาย
                            </label>
                            <input type="text" class="form-control" id="reportDesc" name="description"
                                placeholder="คำอธิบายสั้นๆ (ไม่บังคับ)">
                        </div>

                        <!-- Icon Picker -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-icons me-1 text-warning"></i>ไอคอน
                            </label>
                            <input type="hidden" id="reportIcon" name="icon" value="fa-chart-bar">
                            <div class="icon-picker-grid" id="iconPicker">
                                <!-- Icons populated by JS -->
                            </div>
                        </div>

                        <!-- Color & Sort -->
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-palette me-1" style="color: #667eea;"></i>สีเริ่มต้น
                                    </label>
                                    <input type="color" class="form-control form-control-color w-100"
                                        id="reportColorFrom" name="color_from" value="#667eea"
                                        onchange="updateColorPreview()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-palette me-1" style="color: #764ba2;"></i>สีปลาย
                                    </label>
                                    <input type="color" class="form-control form-control-color w-100" id="reportColorTo"
                                        name="color_to" value="#764ba2" onchange="updateColorPreview()">
                                </div>
                                <div class="col-12">
                                    <div class="color-preview" id="colorPreview"
                                        style="background: linear-gradient(135deg, #667eea, #764ba2);"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-sort-numeric-down me-1 text-muted"></i>ลำดับการแสดงผล
                                    </label>
                                    <input type="number" class="form-control" id="reportSort" name="sort_order"
                                        value="0" min="0">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="reportActive"
                                            name="is_active" checked style="width: 2.5em; height: 1.25em;">
                                        <label class="form-check-label fw-semibold ms-2"
                                            for="reportActive">เปิดใช้งาน</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>ยกเลิก
                </button>
                <button type="button" class="btn btn-primary-gradient px-4" onclick="saveReport()">
                    <i class="fas fa-save me-2"></i>บันทึก
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
var reportModal;

// Icon Options
var iconOptions = [
    'fa-chart-bar', 'fa-chart-pie', 'fa-chart-line', 'fa-chart-area',
    'fa-tachometer-alt', 'fa-database', 'fa-table', 'fa-th-large',
    'fa-file-invoice', 'fa-file-alt', 'fa-money-bill-wave', 'fa-coins',
    'fa-warehouse', 'fa-cogs', 'fa-tools', 'fa-wrench',
    'fa-users', 'fa-user-tie', 'fa-building', 'fa-industry',
    'fa-truck', 'fa-boxes', 'fa-clipboard-list', 'fa-tasks',
    'fa-globe', 'fa-map-marked-alt', 'fa-calendar-alt', 'fa-clock',
    'fa-bolt', 'fa-fire', 'fa-shield-alt', 'fa-star'
];

document.addEventListener('DOMContentLoaded', function() {
    reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
    renderIconPicker('fa-chart-bar');
});

function renderIconPicker(selected) {
    var grid = document.getElementById('iconPicker');
    grid.innerHTML = '';
    iconOptions.forEach(function(icon) {
        var div = document.createElement('div');
        div.className = 'icon-picker-item' + (icon === selected ? ' selected' : '');
        div.innerHTML = '<i class="fas ' + icon + '"></i>';
        div.onclick = function() {
            document.querySelectorAll('.icon-picker-item').forEach(el => el.classList.remove('selected'));
            div.classList.add('selected');
            document.getElementById('reportIcon').value = icon;
        };
        grid.appendChild(div);
    });
}

function updateColorPreview() {
    var from = document.getElementById('reportColorFrom').value;
    var to = document.getElementById('reportColorTo').value;
    document.getElementById('colorPreview').style.background = 'linear-gradient(135deg, ' + from + ', ' + to + ')';
}

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i>เพิ่มรายงานใหม่';
    document.getElementById('reportForm').reset();
    document.getElementById('reportId').value = '';
    document.getElementById('reportColorFrom').value = '#667eea';
    document.getElementById('reportColorTo').value = '#764ba2';
    document.getElementById('reportActive').checked = true;
    renderIconPicker('fa-chart-bar');
    updateColorPreview();
    reportModal.show();
}

function editReport(report) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>แก้ไขรายงาน';
    document.getElementById('reportId').value = report.id;
    document.getElementById('reportTitle').value = report.title;
    document.getElementById('reportCategory').value = report.category || 'general';
    document.getElementById('reportUrl').value = report.embed_url;
    document.getElementById('reportDesc').value = report.description || '';
    document.getElementById('reportIcon').value = report.icon || 'fa-chart-bar';
    document.getElementById('reportColorFrom').value = report.color_from || '#667eea';
    document.getElementById('reportColorTo').value = report.color_to || '#764ba2';
    document.getElementById('reportSort').value = report.sort_order || 0;
    document.getElementById('reportActive').checked = report.is_active == 1;
    renderIconPicker(report.icon || 'fa-chart-bar');
    updateColorPreview();
    reportModal.show();
}

function saveReport() {
    var title = document.getElementById('reportTitle').value.trim();
    var url = document.getElementById('reportUrl').value.trim();

    if (!title || !url) {
        Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูล', text: 'ชื่อรายงานและ URL จำเป็นต้องกรอก', customClass: { popup: 'font-prompt' }, confirmButtonColor: '#667eea' });
        return;
    }

    var formData = new FormData(document.getElementById('reportForm'));
    formData.append('action', formData.get('id') ? 'update' : 'create');
    if (!document.getElementById('reportActive').checked) {
        formData.set('is_active', '0');
    } else {
        formData.set('is_active', '1');
    }

    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'font-prompt' } });

    fetch(BASE_URL + '/actions/save_reports.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'สำเร็จ!' : 'ผิดพลาด',
            text: data.message,
            customClass: { popup: 'font-prompt' },
            confirmButtonColor: '#667eea'
        }).then(() => { if (data.success) location.reload(); });
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', customClass: { popup: 'font-prompt' } });
    });
}

function toggleActive(id, newState) {
    var formData = new FormData();
    formData.append('action', 'toggle_active');
    formData.append('id', id);
    formData.append('is_active', newState);

    fetch(BASE_URL + '/actions/save_reports.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message, customClass: { popup: 'font-prompt' } });
        }
    });
}

function deleteReport(id, title) {
    confirmAction('ลบรายงาน', 'คุณต้องการลบรายงาน "' + title + '" หรือไม่?', function() {
        var formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch(BASE_URL + '/actions/save_reports.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'ลบแล้ว!' : 'ผิดพลาด',
                text: data.message,
                customClass: { popup: 'font-prompt' },
                confirmButtonColor: '#667eea'
            }).then(() => { if (data.success) location.reload(); });
        });
    });
}
</script>
JS;

require_once __DIR__ . '/../includes/footer.php';
?>