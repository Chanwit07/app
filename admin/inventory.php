<?php
/**
 * Admin - Inventory Management
 * Handles Fixed Assets, Containers, and Computers in a single view
 */
$pageTitle = 'จัดการทะเบียนทรัพย์สิน';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Support filtering by type
$filterType = $_GET['type'] ?? 'all';

// Build Query
$sql = "SELECT i.*, u.fullname as creator_name 
        FROM inventory_assets i 
        LEFT JOIN users u ON i.created_by = u.id ";
if ($filterType !== 'all') {
    $safeType = $conn->real_escape_string($filterType);
    $sql .= " WHERE i.asset_type = '$safeType' ";
}
$sql .= " ORDER BY i.created_at DESC";

$assets = [];
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $assets[] = $row;
    }
}

// Helpers
function getAssetTypeName($type)
{
    $types = [
        'fixed_asset' => '<span class="badge bg-primary text-white"><i class="fas fa-building me-1"></i>สินทรัพย์ถาวร</span>',
        'container' => '<span class="badge bg-warning text-dark"><i class="fas fa-box-open me-1"></i>ภาชนะถาวร</span>',
        'computer' => '<span class="badge bg-info text-white"><i class="fas fa-laptop me-1"></i>อุปกรณ์คอมพิวเตอร์</span>'
    ];
    return $types[$type] ?? $type;
}
?>

<div class="animate-fadeInUp">

    <!-- Header & Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h5 class="mb-1"><i class="fas fa-boxes-stacked me-2 text-primary"></i>ทะเบียนทรัพย์สินรวม (
                <?= count($assets) ?> รายการ)
            </h5>
            <small class="text-muted">จัดการข้อมูลสินทรัพย์ถาวร, ภาชนะถาวร, และอุปกรณ์คอมพิวเตอร์</small>
        </div>

        <div class="d-flex gap-2">
            <!-- Filter Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i>
                    <?= $filterType === 'all' ? 'ทุกประเภท' : strip_tags(getAssetTypeName($filterType)) ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?= $filterType === 'all' ? 'active' : '' ?>"
                            href="?type=all">ทุกประเภท</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item <?= $filterType === 'fixed_asset' ? 'active' : '' ?>"
                            href="?type=fixed_asset">สินทรัพย์ถาวร</a></li>
                    <li><a class="dropdown-item <?= $filterType === 'container' ? 'active' : '' ?>"
                            href="?type=container">ภาชนะถาวร</a></li>
                    <li><a class="dropdown-item <?= $filterType === 'computer' ? 'active' : '' ?>"
                            href="?type=computer">อุปกรณ์คอมพิวเตอร์</a></li>
                </ul>
            </div>

            <button class="btn btn-primary-gradient" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i>เพิ่มรายการใหม่
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="form-section">
        <div class="table-responsive">
            <table class="table table-modern table-hover align-middle" id="inventoryTable">
                <thead>
                    <tr>
                        <th>รหัส/รายการ</th>
                        <th>ประเภท</th>
                        <th>จำนวน/หน่วย</th>
                        <th>จุดติดตั้ง</th>
                        <th>สถานะ</th>
                        <th style="width:120px">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-box fa-3x mb-3 d-block opacity-25"></i>
                                ไม่พบข้อมูลทรัพย์สิน
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr id="row-<?= $a['id'] ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($a['image_path']): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($a['image_path']) ?>"
                                                class="rounded"
                                                style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd;"
                                                onclick="Swal.fire({imageUrl: this.src, imageAlt: 'Asset Image', showConfirmButton: false, width: 'auto'})"
                                                role="button" title="คลิกเพื่อขยาย">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border"
                                                style="width:48px; height:48px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?= sanitize($a['asset_code']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?= sanitize($a['asset_name']) ?>
                                            </div>
                                            <!-- If computer, show warning if expired -->
                                            <?php if ($a['asset_type'] === 'computer' && $a['acquisition_date'] && $a['warranty_years']):
                                                $start = new DateTime($a['acquisition_date']);
                                                $expire = clone $start;
                                                $expire->modify("+{$a['warranty_years']} years");
                                                $today = new DateTime();
                                                if ($today > $expire):
                                                    ?>
                                                    <span class="badge bg-danger mt-1" style="font-size:0.65rem;">หมดประกันแล้ว</span>
                                                <?php else:
                                                    $diff = $today->diff($expire);
                                                    ?>
                                                    <span class="badge bg-success mt-1" style="font-size:0.65rem;">เหลือประกัน
                                                        <?= $diff->y > 0 ? $diff->y . ' ปี ' : '' ?>
                                                        <?= $diff->m ?> เดือน
                                                    </span>
                                                <?php endif; endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= getAssetTypeName($a['asset_type']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= number_format($a['quantity']) ?>
                                        <?= sanitize($a['unit']) ?>
                                    </div>
                                    <div class="text-muted small">฿
                                        <?= number_format($a['asset_value'], 2) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($a['install_department'] || $a['install_section']): ?>
                                        <div class="small"><i class="fas fa-building text-muted me-1"></i>
                                            <?= sanitize($a['install_department'] ?: '-') ?>
                                        </div>
                                        <div class="small"><i class="fas fa-sitemap text-muted me-1"></i>
                                            <?= sanitize($a['install_section'] ?: '-') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = ['active' => 'success', 'broken' => 'warning text-dark', 'written_off' => 'danger'];
                                    $statusLabel = ['active' => 'ใช้งาน', 'broken' => 'ชำรุด', 'written_off' => 'แทงจำหน่าย'];
                                    $sc = $statusClass[$a['status']] ?? 'secondary';
                                    $sl = $statusLabel[$a['status']] ?? $a['status'];
                                    ?>
                                    <span class="badge bg-<?= $sc ?>">
                                        <?= $sl ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            จัดการ
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.9rem;">
                                            <li><a class="dropdown-item" href="#" onclick="editItem(<?= $a['id'] ?>)"><i
                                                        class="fas fa-edit text-primary me-2"></i>แก้ไขข้อมูล</a></li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <h6 class="dropdown-header">เปลี่ยนสถานะ:</h6>
                                            </li>
                                            <li><a class="dropdown-item" href="#"
                                                    onclick="updateStatus(<?= $a['id'] ?>, 'active')"><i
                                                        class="fas fa-check-circle text-success me-2"></i>ใช้งานปกติ</a></li>
                                            <li><a class="dropdown-item" href="#"
                                                    onclick="updateStatus(<?= $a['id'] ?>, 'broken')"><i
                                                        class="fas fa-exclamation-triangle text-warning me-2"></i>แจ้งชำรุด</a>
                                            </li>
                                            <li><a class="dropdown-item" href="#"
                                                    onclick="updateStatus(<?= $a['id'] ?>, 'written_off')"><i
                                                        class="fas fa-ban text-danger me-2"></i>แทงจำหน่าย</a></li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item text-danger" href="#"
                                                    onclick="deleteItem(<?= $a['id'] ?>, '<?= addslashes($a['asset_code']) ?>')"><i
                                                        class="fas fa-trash-alt me-2"></i>ลบรายการ (ถาวร)</a></li>
                                        </ul>
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

<!-- ==========================================
     Add/Edit Modal (Dynamic Form)
=========================================== -->
<div class="modal fade" id="assetModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1f4037, #99f2c8);">
                <h5 class="modal-title text-white" id="modalTitle">
                    <i class="fas fa-box-open me-2"></i>เพิ่มรายการทรัพย์สิน
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="assetForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="formId">
                    <input type="hidden" name="action" id="formAction" value="create">

                    <div class="row g-4">

                        <!-- Left Column: Primary Details -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold mb-3 text-primary border-bottom pb-2">ข้อมูลหลัก</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">ประเภททรัพย์สิน <span
                                                class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="asset_type"
                                                    id="type_fixed" value="fixed_asset" checked
                                                    onchange="toggleFormFields()">
                                                <label class="form-check-label" for="type_fixed"><i
                                                        class="fas fa-building text-primary me-1"></i>สินทรัพย์ถาวร</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="asset_type"
                                                    id="type_container" value="container" onchange="toggleFormFields()">
                                                <label class="form-check-label" for="type_container"><i
                                                        class="fas fa-box-open text-warning me-1"></i>ภาชนะถาวร</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="asset_type"
                                                    id="type_computer" value="computer" onchange="toggleFormFields()">
                                                <label class="form-check-label" for="type_computer"><i
                                                        class="fas fa-laptop text-info me-1"></i>อุปกรณ์คอมพิวเตอร์</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">รหัสอ้างอิง <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="asset_code" id="formCode"
                                                required placeholder="เช่น AST-001">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">ชื่อรายการ <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="asset_name" id="formName"
                                                required placeholder="เช่น โต๊ะทำงานไม้ / Notebook Dell">
                                        </div>
                                    </div>

                                    <!-- Section: Computer Only (Hidden by default) -->
                                    <div id="computerSection"
                                        class="p-3 bg-info bg-opacity-10 border border-info rounded mb-3"
                                        style="display: none;">
                                        <h6 class="text-info fw-bold mb-3"><i
                                                class="fas fa-laptop-code me-2"></i>ข้อมูลคอมพิวเตอร์เฉพาะ</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">ประเภท</label>
                                                <select class="form-select" name="computer_type" id="formCompType">
                                                    <option value="own">เครื่อง งทป.</option>
                                                    <option value="rent">เครื่องคอมเช่า</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ยี่ห้อ (Brand)</label>
                                                <input type="text" class="form-control" name="brand" id="formBrand">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">รุ่น (Model)</label>
                                                <input type="text" class="form-control" name="model" id="formModel">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ปีรับประกัน</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="warranty_years"
                                                        id="formWarranty" min="0" placeholder="เช่น 3">
                                                    <span class="input-group-text">ปี</span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Serial Number</label>
                                                <input type="text" class="form-control" name="serial_number"
                                                    id="formSerial">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">จำนวน <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="quantity" id="formQty"
                                                value="1" min="1" required>
                                            <small class="text-muted" id="qtyHelper">คอมพิวเตอร์มักจะมีจำนวน 1</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">หน่วยนับ <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="unit" id="formUnit" required
                                                placeholder="เช่น เครื่อง, ใบ, ตัว">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">มูลค่า (บาท)</label>
                                            <input type="number" class="form-control" name="asset_value" id="formValue"
                                                step="0.01" min="0" value="0.00">
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">หมายเหตุ</label>
                                            <textarea class="form-control" name="remarks" id="formRemarks"
                                                rows="2"></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Location & Files -->
                        <div class="col-lg-4">

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold mb-3 text-secondary border-bottom pb-2"><i
                                            class="far fa-calendar-alt me-2"></i>ข้อมูลลการใช้งาน</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" id="labelDate">วันที่ได้มา</label>
                                        <input type="date" class="form-control" name="acquisition_date" id="formDate">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">หน่วยงานผู้รับผิดชอบ</label>
                                        <input type="text" class="form-control" name="responsible_dept"
                                            id="formRespDept">
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body bg-light">
                                    <h6 class="card-title fw-bold mb-3 text-secondary border-bottom pb-2"><i
                                            class="fas fa-map-marker-alt me-2"></i>จุดติดตั้ง (สำหรับ Filter)</h6>
                                    <div class="mb-2">
                                        <label class="form-label">หน่วยงาน</label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="install_department" id="formInstallDept"
                                            placeholder="เช่น กพท., ฝ่ายบัญชี">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">แผนก/ส่วน</label>
                                        <input type="text" class="form-control form-control-sm" name="install_section"
                                            id="formInstallSec" placeholder="เช่น แผนกเงินเดือน">
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold mb-3 text-secondary border-bottom pb-2"><i
                                            class="fas fa-camera me-2"></i>รูปภาพแนบ</h6>
                                    <div class="mb-2 text-center">
                                        <img id="imagePreview" src="" class="img-fluid rounded d-none mb-2"
                                            style="max-height: 150px; border: 1px solid #ddd;">
                                    </div>
                                    <input class="form-control form-control-sm" type="file" name="image" id="formImage"
                                        accept="image/jpeg, image/png, image/webp">
                                    <small class="text-muted d-block mt-1">ไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" onclick="submitForm()">
                    <i class="fas fa-save me-1"></i>บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<!-- jQuery & DataTables for better search/filter (We'll load from CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
const BASE = document.querySelector('link[href*="style.css"]').href.replace('/assets/css/style.css', '');
let modal;

$(document.ready(function() {
    // Initialize DataTable
    $('#inventoryTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json' },
        pageLength: 25,
        ordering: false // Custom sorting already done in SQL
    });
    
    modal = new bootstrap.Modal(document.getElementById('assetModal'));
    
    // Image Preview
    $('#formImage').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
}));

// ---- Dynamic Form Logic ----
function toggleFormFields() {
    const type = $('input[name="asset_type"]:checked').val();
    const compSection = $('#computerSection');
    const lblDate = $('#labelDate');
    const qty = $('#formQty');
    
    if (type === 'computer') {
        compSection.slideDown(200);
        lblDate.text('วันที่เริ่มสัญญา หรือ ซื้อ');
        qty.val(1).prop('readonly', true);
        $('#qtyHelper').removeClass('text-muted').addClass('text-info').text('อุปกรณ์คอมพิวเตอร์ผูกพัน 1 เครื่องต่อ 1 รหัส');
    } else {
        compSection.slideUp(200);
        lblDate.text('วันที่ได้มา');
        qty.prop('readonly', false);
        $('#qtyHelper').removeClass('text-info').addClass('text-muted').text('เพิ่ม/ลดจำนวนได้ตามจริง');
        
        // clear comp fields internally
        $('#formCompType').val('own');
        $('#formBrand, #formModel, #formSerial, #formWarranty').val('');
    }
}

// ---- Modal Actions ----
function openAddModal() {
    $('#modalTitle').html('<i class="fas fa-plus-circle me-2"></i>เพิ่มรายการทรัพย์สิน');
    $('#formAction').val('create');
    $('#formId').val('');
    $('#assetForm')[0].reset();
    $('#imagePreview').addClass('d-none').attr('src', '');
    toggleFormFields();
    modal.show();
}

function editItem(id) {
    fetch(`${BASE}/actions/save_inventory.php?action=get&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success) { showToast(data.message, 'error'); return; }
            const item = data.item;
            
            $('#modalTitle').html(`<i class="fas fa-edit me-2"></i>แก้ไขข้อมูล (${item.asset_code})`);
            $('#formAction').val('update');
            $('#formId').val(item.id);
            
            // Set Radios
            $(`input[name=asset_type][value=${item.asset_type}]`).prop('checked', true);
            toggleFormFields();
            
            // Map common fields
            $('#formCode').val(item.asset_code);
            $('#formName').val(item.asset_name);
            $('#formQty').val(item.quantity);
            $('#formUnit').val(item.unit);
            $('#formValue').val(item.asset_value);
            $('#formDate').val(item.acquisition_date);
            $('#formRespDept').val(item.responsible_dept);
            $('#formInstallDept').val(item.install_department);
            $('#formInstallSec').val(item.install_section);
            $('#formRemarks').val(item.remarks);
            
            // Map computer fields
            if(item.asset_type === 'computer') {
                $('#formCompType').val(item.computer_type || 'own');
                $('#formBrand').val(item.brand);
                $('#formModel').val(item.model);
                $('#formSerial').val(item.serial_number);
                $('#formWarranty').val(item.warranty_years);
            }
            
            // Image
            if(item.full_image_url) {
                $('#imagePreview').attr('src', item.full_image_url).removeClass('d-none');
            } else {
                $('#imagePreview').addClass('d-none').attr('src', '');
            }
            
            modal.show();
        })
        .catch(() => showToast('ไม่สามารถดึงข้อมูลได้', 'error'));
}

function submitForm() {
    const form = document.getElementById('assetForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const fd = new FormData(form);
    fetch(`${BASE}/actions/save_inventory.php`, {
        method: 'POST', body: fd
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showToast(data.message, 'success');
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    });
}

// ---- Manage Actions ----
function updateStatus(id, newStatus) {
    const textMap = {'active':'ใช้งานปกติ', 'broken':'แจ้งชำรุด', 'written_off':'แทงจำหน่าย'};
    confirmAction('เปลี่ยนสถานะ', `ต้องการเปลี่ยนสถานะเป็น "${textMap[newStatus]}" หรือไม่?`, function() {
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', id);
        fd.append('new_status', newStatus);
        
        fetch(`${BASE}/actions/save_inventory.php`, { method:'POST', body:fd })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if(data.success) setTimeout(()=>location.reload(), 800);
            });
    });
}

function deleteItem(id, code) {
    Swal.fire({
        title: 'ยืนยันการลบ (ถาวร)',
        html: `ต้องการลบรายการ <b>${code}</b> ออกจากระบบ?<br><strong class="text-danger">ไม่สามารถกู้คืนได้</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'ลบข้อมูล'
    }).then((res) => {
        if(res.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            
            fetch(`${BASE}/actions/save_inventory.php`, { method:'POST', body:fd })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        $(`#row-${id}`).fadeOut(400, function() { $(this).remove(); });
                        showToast('ลบสำเร็จ');
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        }
    });
}
</script>
<style>
/* Custom radio container for asset types */
.custom-radio .form-check-input {
    width: 1.25em; height: 1.25em;
    margin-top: 0.15em;
    cursor: pointer;
}
.custom-radio .form-check-label {
    cursor: pointer;
    font-weight: 500;
}
</style>
JS;

require_once __DIR__ . '/../includes/footer.php';
?>