<?php
/**
 * Admin - จัดการหน่วยงานในสังกัดหน้าเว็บ
 */
$pageTitle = 'จัดการหน่วยงานในสังกัด';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all divisions
$divisions = [];
$res = $conn->query("SELECT * FROM landing_divisions ORDER BY sort_order ASC, id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $divisions[] = $row;
    }
}

// Icon options for picker
$iconOptions = [
    'fa-warehouse',
    'fa-gears',
    'fa-map-location-dot',
    'fa-train',
    'fa-train-tram',
    'fa-couch',
    'fa-building',
    'fa-industry',
    'fa-wrench',
    'fa-screwdriver-wrench',
    'fa-toolbox',
    'fa-cogs',
    'fa-truck',
    'fa-map-marker-alt',
    'fa-city',
    'fa-hard-hat',
    'fa-bolt',
    'fa-shield-halved',
    'fa-users',
    'fa-sitemap'
];
?>

<style>
    .div-manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .div-manage-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .div-manage-card .card-gradient-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1a237e, #3949ab);
    }

    .div-manage-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #1a237e, #3949ab);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        flex-shrink: 0;
    }

    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: .5rem;
    }

    .icon-picker-item {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .3s;
        font-size: 1.1rem;
        color: #555;
    }

    .icon-picker-item:hover {
        border-color: #1a237e;
        color: #1a237e;
    }

    .icon-picker-item.selected {
        border-color: #1a237e;
        background: rgba(26, 35, 126, .1);
        color: #1a237e;
    }

    .sort-badge {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(26, 35, 126, .1);
        color: #1a237e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
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
        <h4 class="fw-bold mb-1"><i class="fas fa-sitemap me-2 text-primary"></i>จัดการหน่วยงานในสังกัด</h4>
        <p class="text-muted mb-0">เพิ่ม แก้ไข จัดลำดับ และจัดการหน่วยงานที่แสดงบนหน้าเว็บไซต์</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus me-1"></i>เพิ่มหน่วยงาน
    </button>
</div>

<?php if (empty($divisions)): ?>
    <div class="empty-state">
        <i class="fas fa-sitemap"></i>
        <h5>ยังไม่มีหน่วยงาน</h5>
        <p>กดปุ่ม "เพิ่มหน่วยงาน" เพื่อเริ่มต้น</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($divisions as $item): ?>
            <div class="col-12 col-md-6" id="div-card-<?= $item['id'] ?>">
                <div class="div-manage-card">
                    <div class="card-gradient-bar"></div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="card-icon">
                            <i class="fas <?= htmlspecialchars($item['icon']) ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="sort-badge">
                                    <?= $item['sort_order'] ?>
                                </span>
                                <h6 class="fw-bold mb-0">
                                    <?= htmlspecialchars($item['name']) ?>
                                </h6>
                                <?php if ($item['status'] === 'active'): ?>
                                    <span class="badge bg-success status-badge">แสดง</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary status-badge">ซ่อน</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($item['location']) ?>
                            </small>
                            <p class="text-muted mb-0 mt-1" style="font-size:.85rem">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0 align-items-start">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" <?= $item['status'] === 'active' ? 'checked' : '' ?>
                                onchange="toggleStatus(
                        <?= $item['id'] ?>, this.checked)">
                            </div>
                            <button class="btn btn-sm btn-outline-primary btn-action"
                                onclick='editDiv(<?= json_encode($item, JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action"
                                onclick="deleteDiv(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name']), ENT_QUOTES) ?>')">
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
<div class="modal fade" id="divModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #1a237e, #3949ab); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-sitemap me-2"></i>เพิ่มหน่วยงาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="divId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">ชื่อหน่วยงาน <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="divName" placeholder="เช่น ศูนย์ลากเลื่อน">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">สถานที่ตั้ง</label>
                        <input type="text" class="form-control" id="divLocation" placeholder="เช่น บางซื่อ กรุงเทพฯ">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">ลำดับ</label>
                        <input type="number" class="form-control" id="divSortOrder" value="0" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">สถานะ</label>
                        <select class="form-select" id="divStatus">
                            <option value="active">แสดง</option>
                            <option value="inactive">ซ่อน</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">คำอธิบาย</label>
                    <textarea class="form-control" id="divDesc" rows="3"
                        placeholder="คำอธิบายสั้นๆ เกี่ยวกับหน่วยงาน..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ไอคอน</label>
                    <input type="hidden" id="divIcon" value="fa-building">
                    <div class="icon-picker-grid" id="iconPicker">
                        <?php foreach ($iconOptions as $ico): ?>
                            <div class="icon-picker-item <?= $ico === 'fa-building' ? 'selected' : '' ?>"
                                data-icon="<?= $ico ?>" onclick="selectIcon(this, '<?= $ico ?>')">
                                <i class="fas <?= $ico ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveDiv()">
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

function selectIcon(el, icon) {
    document.querySelectorAll('.icon-picker-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('divIcon').value = icon;
}

function openModal() {
    document.getElementById('divId').value = '';
    document.getElementById('divName').value = '';
    document.getElementById('divLocation').value = '';
    document.getElementById('divDesc').value = '';
    document.getElementById('divSortOrder').value = 0;
    document.getElementById('divStatus').value = 'active';
    document.getElementById('divIcon').value = 'fa-building';
    document.querySelectorAll('.icon-picker-item').forEach(i => {
        i.classList.toggle('selected', i.dataset.icon === 'fa-building');
    });
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มหน่วยงาน';
    new bootstrap.Modal(document.getElementById('divModal')).show();
}

function editDiv(item) {
    document.getElementById('divId').value = item.id;
    document.getElementById('divName').value = item.name;
    document.getElementById('divLocation').value = item.location;
    document.getElementById('divDesc').value = item.description;
    document.getElementById('divSortOrder').value = item.sort_order;
    document.getElementById('divStatus').value = item.status;
    document.getElementById('divIcon').value = item.icon;
    document.querySelectorAll('.icon-picker-item').forEach(i => {
        i.classList.toggle('selected', i.dataset.icon === item.icon);
    });
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขหน่วยงาน';
    new bootstrap.Modal(document.getElementById('divModal')).show();
}

function saveDiv() {
    var id = document.getElementById('divId').value;
    var formData = new FormData();
    formData.append('action', id ? 'update' : 'create');
    if (id) formData.append('id', id);
    formData.append('name', document.getElementById('divName').value);
    formData.append('location', document.getElementById('divLocation').value);
    formData.append('description', document.getElementById('divDesc').value);
    formData.append('icon', document.getElementById('divIcon').value);
    formData.append('sort_order', document.getElementById('divSortOrder').value);
    formData.append('status', document.getElementById('divStatus').value);

    fetch(BASE_URL + '/actions/save_divisions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('divModal')).hide();
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

    fetch(BASE_URL + '/actions/save_divisions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => location.reload(), 800);
        });
}

function deleteDiv(id, name) {
    if (!confirm('ต้องการลบหน่วยงาน "' + name + '" หรือไม่?')) return;
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(BASE_URL + '/actions/save_divisions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('div-card-' + id)?.remove();
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