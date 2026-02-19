<?php
/**
 * My Requests - คำขอของฉัน
 * แสดงทุกรายการในระบบ (ขอสินทรัพย์ + ขอรหัสพัสดุ) พร้อมสถานะ
 */
$pageTitle = 'คำขอของฉัน';
$extraCss = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
require_once __DIR__ . '/includes/header.php';

// =======================================
// Filter parameters
// =======================================
$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['q'] ?? '');
$activeTab = $_GET['tab'] ?? 'asset';

// =======================================
// Build queries — show ALL requests in the system
// =======================================

// --- Asset Requests ---
$assetWhere = "1=1";
$assetParams = [];
$assetTypes = '';

if (!empty($filterStatus)) {
    $assetWhere .= " AND ar.status = ?";
    $assetParams[] = $filterStatus;
    $assetTypes .= 's';
}
if (!empty($filterSearch)) {
    $assetWhere .= " AND (ar.asset_id LIKE ? OR ar.department LIKE ? OR ar.asset_group LIKE ? OR ar.serial_number LIKE ? OR u.fullname LIKE ?)";
    $searchLike = "%{$conn->real_escape_string($filterSearch)}%";
    for ($i = 0; $i < 5; $i++) {
        $assetParams[] = $searchLike;
        $assetTypes .= 's';
    }
}

$assetSql = "SELECT ar.*, u.fullname as requester, u.department as user_dept,
                    admin.fullname as updated_by_name
             FROM asset_requests ar
             LEFT JOIN users u ON ar.user_id = u.id
             LEFT JOIN users admin ON ar.updated_by = admin.id
             WHERE $assetWhere
             ORDER BY ar.created_at DESC";

if (!empty($assetParams)) {
    $stmt = $conn->prepare($assetSql);
    $stmt->bind_param($assetTypes, ...$assetParams);
    $stmt->execute();
    $assetResult = $stmt->get_result();
} else {
    $assetResult = $conn->query($assetSql);
}

$assetRequests = [];
while ($row = $assetResult->fetch_assoc()) {
    $assetRequests[] = $row;
}
if (isset($stmt)) {
    $stmt->close();
    unset($stmt);
}

// --- Supply Requests ---
$supplyWhere = "1=1";
$supplyParams = [];
$supplyTypes = '';

if (!empty($filterStatus)) {
    $supplyWhere .= " AND sr.status = ?";
    $supplyParams[] = $filterStatus;
    $supplyTypes .= 's';
}
if (!empty($filterSearch)) {
    $supplyWhere .= " AND (sr.item_number LIKE ? OR sr.item_name LIKE ? OR sr.new_item_name LIKE ? OR sr.unit LIKE ? OR u.fullname LIKE ?)";
    $searchLike2 = "%{$conn->real_escape_string($filterSearch)}%";
    for ($i = 0; $i < 5; $i++) {
        $supplyParams[] = $searchLike2;
        $supplyTypes .= 's';
    }
}

$supplySql = "SELECT sr.*, u.fullname as requester, u.department as user_dept,
                     admin.fullname as updated_by_name
              FROM supply_requests sr
              LEFT JOIN users u ON sr.user_id = u.id
              LEFT JOIN users admin ON sr.updated_by = admin.id
              WHERE $supplyWhere
              ORDER BY sr.created_at DESC";

if (!empty($supplyParams)) {
    $stmt = $conn->prepare($supplySql);
    $stmt->bind_param($supplyTypes, ...$supplyParams);
    $stmt->execute();
    $supplyResult = $stmt->get_result();
} else {
    $supplyResult = $conn->query($supplySql);
}

$supplyRequests = [];
while ($row = $supplyResult->fetch_assoc()) {
    $supplyRequests[] = $row;
}
if (isset($stmt)) {
    $stmt->close();
    unset($stmt);
}

// =======================================
// Summary counts
// =======================================
$assetCounts = ['Pending' => 0, 'Processing' => 0, 'Completed' => 0];
$supplyCounts = ['Pending' => 0, 'Processing' => 0, 'Completed' => 0];

foreach ($assetRequests as $r) {
    $assetCounts[$r['status']]++;
}
foreach ($supplyRequests as $r) {
    $supplyCounts[$r['status']]++;
}

$totalPending = $assetCounts['Pending'] + $supplyCounts['Pending'];
$totalProcessing = $assetCounts['Processing'] + $supplyCounts['Processing'];
$totalCompleted = $assetCounts['Completed'] + $supplyCounts['Completed'];
$totalAll = $totalPending + $totalProcessing + $totalCompleted;
?>

<div class="animate-fadeInUp">
    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?= number_format($totalAll) ?>
                </div>
                <div class="stat-label">คำขอทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f6d365, #fda085);">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?= number_format($totalPending) ?>
                </div>
                <div class="stat-label">รอดำเนินการ</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a1c4fd, #c2e9fb);">
                        <i class="fas fa-cog fa-spin"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?= number_format($totalProcessing) ?>
                </div>
                <div class="stat-label">กำลังดำเนินการ</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?= number_format($totalCompleted) ?>
                </div>
                <div class="stat-label">เสร็จสิ้น</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="form-section mb-4">
        <div class="section-title">
            <i class="fas fa-filter"></i>
            ตัวกรองและค้นหา
        </div>
        <form method="GET" class="row g-3" id="filterForm">
            <input type="hidden" name="tab" id="hiddenTab" value="<?= sanitize($activeTab) ?>">
            <div class="col-md-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>ทั้งหมด</option>
                    <option value="Pending" <?= $filterStatus === 'Pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                    <option value="Processing" <?= $filterStatus === 'Processing' ? 'selected' : '' ?>>กำลังดำเนินการ
                    </option>
                    <option value="Completed" <?= $filterStatus === 'Completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                </select>
            </div>
            <div class="col-md-7">
                <label class="form-label">ค้นหา</label>
                <input type="text" class="form-control" name="q" value="<?= sanitize($filterSearch) ?>"
                    placeholder="ค้นหาด้วย เลขที่สินทรัพย์, ชื่อรายการ, ผู้ขอ, หน่วยงาน...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-gradient w-100">
                    <i class="fas fa-search me-1"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Category Tabs -->
    <ul class="nav nav-tabs mb-0" id="requestTabs" role="tablist" style="border-bottom: none;">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'asset' ? 'active' : '' ?>" id="asset-tab" data-bs-toggle="tab"
                data-bs-target="#assetPane" type="button" role="tab"
                onclick="document.getElementById('hiddenTab').value='asset'">
                <i class="fas fa-building me-1"></i>ขอรหัสสินทรัพย์
                <span class="badge bg-primary ms-1">
                    <?= count($assetRequests) ?>
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'supply' ? 'active' : '' ?>" id="supply-tab" data-bs-toggle="tab"
                data-bs-target="#supplyPane" type="button" role="tab"
                onclick="document.getElementById('hiddenTab').value='supply'">
                <i class="fas fa-box-open me-1"></i>ขอรหัสพัสดุ
                <span class="badge bg-secondary ms-1">
                    <?= count($supplyRequests) ?>
                </span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- ========== Asset Requests Tab ========== -->
        <div class="tab-pane fade <?= $activeTab === 'asset' ? 'show active' : '' ?>" id="assetPane" role="tabpanel">
            <div class="form-section" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                <?php if (empty($assetRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox d-block"></i>
                        <p>ไม่พบรายการขอรหัสสินทรัพย์</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern" id="assetTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>วันที่ส่ง</th>
                                    <th>ผู้ขอ</th>
                                    <th>หน่วยงาน</th>
                                    <th>เลขที่สินทรัพย์</th>
                                    <th>กลุ่มสินทรัพย์/ยูนิต</th>
                                    <th>Serial Number</th>
                                    <th>ประเภทบัญชี</th>
                                    <th>สถานะ</th>
                                    <th>วันที่เสร็จ</th>
                                    <th>หมายเหตุ Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assetRequests as $item): ?>
                                    <tr>
                                        <td>
                                            <?= $item['id'] ?>
                                        </td>
                                        <td><small>
                                                <?= thaiDate($item['created_at'], 'compact') ?>
                                            </small></td>
                                        <td>
                                            <strong>
                                                <?= sanitize($item['requester'] ?? '-') ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= sanitize($item['department']) ?>
                                        </td>
                                        <td><code><?= sanitize($item['asset_id']) ?></code></td>
                                        <td>
                                            <?= sanitize($item['asset_group']) ?>
                                        </td>
                                        <td><small>
                                                <?= sanitize($item['serial_number']) ?>
                                            </small></td>
                                        <td>
                                            <?= sanitize($item['account_type']) ?>
                                        </td>
                                        <td>
                                            <?= statusBadge($item['status']) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['finished_at']): ?>
                                                <small>
                                                    <?= thaiDate($item['finished_at'], 'compact') ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['admin_note']): ?>
                                                <small class="text-muted">
                                                    <?= sanitize($item['admin_note']) ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========== Supply Requests Tab ========== -->
        <div class="tab-pane fade <?= $activeTab === 'supply' ? 'show active' : '' ?>" id="supplyPane" role="tabpanel">
            <div class="form-section" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                <?php if (empty($supplyRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox d-block"></i>
                        <p>ไม่พบรายการขอรหัสพัสดุ</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern" id="supplyTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>วันที่ส่ง</th>
                                    <th>ผู้ขอ</th>
                                    <th>ประเภทคำขอ</th>
                                    <th>เลขที่สิ่งของ</th>
                                    <th>ชื่อรายการ</th>
                                    <th>ชื่อใหม่</th>
                                    <th>หน่วยนับ</th>
                                    <th>ปริมาณ/ปี</th>
                                    <th>สถานะ</th>
                                    <th>วันที่เสร็จ</th>
                                    <th>หมายเหตุ Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($supplyRequests as $item): ?>
                                    <tr>
                                        <td>
                                            <?= $item['id'] ?>
                                        </td>
                                        <td><small>
                                                <?= thaiDate($item['created_at'], 'compact') ?>
                                            </small></td>
                                        <td>
                                            <strong>
                                                <?= sanitize($item['requester'] ?? '-') ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= requestTypeLabel($item['request_type']) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['item_number']): ?>
                                                <code><?= sanitize($item['item_number']) ?></code>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= sanitize($item['item_name']) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['new_item_name']): ?>
                                                <?= sanitize($item['new_item_name']) ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= sanitize($item['unit']) ?>
                                        </td>
                                        <td>
                                            <?= $item['annual_usage'] ? number_format($item['annual_usage']) : '-' ?>
                                        </td>
                                        <td>
                                            <?= statusBadge($item['status']) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['finished_at']): ?>
                                                <small>
                                                    <?= thaiDate($item['finished_at'], 'compact') ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['admin_note']): ?>
                                                <small class="text-muted">
                                                    <?= sanitize($item['admin_note']) ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var dtConfig = {
        language: {
            search: "ค้นหาในตาราง:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ไม่มีข้อมูล",
            infoFiltered: "(กรองจาก _MAX_ รายการ)",
            paginate: { first: "หน้าแรก", last: "หน้าสุดท้าย", next: "ถัดไป", previous: "ก่อนหน้า" },
            zeroRecords: "ไม่พบรายการที่ค้นหา"
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
    };

    if ($('#assetTable').length) {
        $('#assetTable').DataTable(dtConfig);
    }
    if ($('#supplyTable').length) {
        $('#supplyTable').DataTable(dtConfig);
    }
});
</script>
<style>
    .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-radius: 12px 12px 0 0;
        padding: 0.7rem 1.5rem;
        font-weight: 500;
        font-size: 0.9rem;
        color: #718096;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link:hover {
        color: var(--primary);
        border-color: #e2e8f0 #e2e8f0 transparent;
    }
    .nav-tabs .nav-link.active {
        color: var(--primary);
        background: #fff;
        border-color: #e2e8f0 #e2e8f0 #fff;
        font-weight: 600;
    }
    .nav-tabs {
        border-bottom: 1px solid #e2e8f0;
    }
    /* DataTables overrides */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        outline: none;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 0.3rem 0.6rem;
        font-size: 0.85rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.8rem;
        color: #999;
    }
</style>
JS;

require_once __DIR__ . '/includes/footer.php';
?>