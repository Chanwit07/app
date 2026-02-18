<?php
/**
 * History - ประวัติการดำเนินการ (Audit Trail)
 */
$pageTitle = 'ประวัติการดำเนินการ';
$extraCss = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
require_once __DIR__ . '/includes/header.php';

// Filter parameters
$filterUser = $_GET['user'] ?? '';
$filterAction = $_GET['action'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Build query
$where = "1=1";
$params = [];
$types = '';

if (!empty($filterUser)) {
    $where .= " AND (u.fullname LIKE ? OR u.username LIKE ?)";
    $filterUserLike = "%{$filterUser}%";
    $params[] = &$filterUserLike;
    $params[] = &$filterUserLike;
    $types .= 'ss';
}
if (!empty($filterAction)) {
    $where .= " AND at.action LIKE ?";
    $filterActionLike = "%{$filterAction}%";
    $params[] = &$filterActionLike;
    $types .= 's';
}
if (!empty($filterDateFrom)) {
    $where .= " AND DATE(at.created_at) >= ?";
    $params[] = &$filterDateFrom;
    $types .= 's';
}
if (!empty($filterDateTo)) {
    $where .= " AND DATE(at.created_at) <= ?";
    $params[] = &$filterDateTo;
    $types .= 's';
}

// For non-admin users, only show own history
if (!isAdmin()) {
    $userId = $_SESSION['user_id'];
    $where .= " AND at.user_id = ?";
    $params[] = &$userId;
    $types .= 'i';
}

$sql = "SELECT at.*, u.fullname, u.username 
        FROM audit_trail at 
        LEFT JOIN users u ON at.user_id = u.id 
        WHERE $where 
        ORDER BY at.created_at DESC 
        LIMIT 500";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
if (isset($stmt))
    $stmt->close();
?>

<div class="animate-fadeInUp">
    <!-- Filters -->
    <div class="form-section mb-4">
        <div class="section-title">
            <i class="fas fa-filter"></i>
            ตัวกรอง
        </div>
        <form method="GET" class="row g-3">
            <?php if (isAdmin()): ?>
                <div class="col-md-3">
                    <label class="form-label">ผู้ใช้</label>
                    <input type="text" class="form-control" name="user" value="<?= sanitize($filterUser) ?>"
                        placeholder="ชื่อ / username">
                </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label">การดำเนินการ</label>
                <input type="text" class="form-control" name="action" value="<?= sanitize($filterAction) ?>"
                    placeholder="เช่น เข้าสู่ระบบ">
            </div>
            <div class="col-md-2">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" class="form-control" name="date_from" value="<?= sanitize($filterDateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" class="form-control" name="date_to" value="<?= sanitize($filterDateTo) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-gradient w-100">
                    <i class="fas fa-search me-1"></i>กรอง
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="form-section">
        <div class="section-title">
            <i class="fas fa-history"></i>
            บันทึกการดำเนินการ (
            <?= count($logs) ?> รายการ)
        </div>

        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list d-block"></i>
                <p>ไม่พบประวัติการดำเนินการ</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-modern" id="historyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>วันที่/เวลา</th>
                            <th>ผู้ใช้</th>
                            <th>การดำเนินการ</th>
                            <th>ตาราง</th>
                            <th>ID</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <?= $log['id'] ?>
                                </td>
                                <td><small>
                                        <?= thaiDate($log['created_at'], 'full') ?>
                                    </small></td>
                                <td>
                                    <strong>
                                        <?= sanitize($log['fullname'] ?? 'System') ?>
                                    </strong>
                                    <br><small class="text-muted">
                                        <?= sanitize($log['username'] ?? '') ?>
                                    </small>
                                </td>
                                <td>
                                    <?= sanitize($log['action']) ?>
                                </td>
                                <td><code class="small"><?= sanitize($log['target_table'] ?? '-') ?></code></td>
                                <td>
                                    <?= $log['target_id'] ?? '-' ?>
                                </td>
                                <td><small class="text-muted">
                                        <?= sanitize($log['ip_address'] ?? '-') ?>
                                    </small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#historyTable').DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ไม่มีข้อมูล",
            infoFiltered: "(กรองจาก _MAX_ รายการ)",
            paginate: { first: "หน้าแรก", last: "หน้าสุดท้าย", next: "ถัดไป", previous: "ก่อนหน้า" },
            zeroRecords: "ไม่พบรายการ"
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>