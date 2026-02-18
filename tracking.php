<?php
/**
 * Tracking - ติดตามสถานะคำขอ
 */
$pageTitle = 'ติดตามสถานะ';
require_once __DIR__ . '/includes/header.php';

$searchQuery = trim($_GET['q'] ?? '');
$searchType = $_GET['type'] ?? 'all';
$results = [];
$searched = false;

if (!empty($searchQuery)) {
    $searched = true;
    $like = '%' . $conn->real_escape_string($searchQuery) . '%';
    
    // Search asset_requests
    if ($searchType === 'all' || $searchType === 'asset') {
        $stmt = $conn->prepare("SELECT ar.*, u.fullname as requester FROM asset_requests ar LEFT JOIN users u ON ar.user_id = u.id WHERE ar.asset_id LIKE ? OR ar.department LIKE ? OR ar.serial_number LIKE ? ORDER BY ar.created_at DESC LIMIT 20");
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['_type'] = 'asset';
            $results[] = $row;
        }
        $stmt->close();
    }
    
    // Search supply_requests
    if ($searchType === 'all' || $searchType === 'supply') {
        $stmt = $conn->prepare("SELECT sr.*, u.fullname as requester FROM supply_requests sr LEFT JOIN users u ON sr.user_id = u.id WHERE sr.item_number LIKE ? OR sr.item_name LIKE ? OR sr.new_item_name LIKE ? ORDER BY sr.created_at DESC LIMIT 20");
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['_type'] = 'supply';
            $results[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="animate-fadeInUp">
    <!-- Search Bar -->
    <div class="form-section mb-4">
        <div class="section-title">
            <i class="fas fa-search-location"></i>
            ค้นหาและติดตามสถานะคำขอ
        </div>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="type">
                    <option value="all" <?= $searchType === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                    <option value="asset" <?= $searchType === 'asset' ? 'selected' : '' ?>>สินทรัพย์</option>
                    <option value="supply" <?= $searchType === 'supply' ? 'selected' : '' ?>>พัสดุ</option>
                </select>
            </div>
            <div class="col-md-7">
                <input type="text" class="form-control" name="q" value="<?= sanitize($searchQuery) ?>" placeholder="ค้นหาด้วย เลขที่สินทรัพย์, เลขที่สิ่งของ, ชื่อรายการ, หน่วยงาน...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-gradient w-100">
                    <i class="fas fa-search me-1"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Results -->
    <?php if ($searched): ?>
        <?php if (empty($results)): ?>
            <div class="form-section">
                <div class="empty-state">
                    <i class="fas fa-search d-block"></i>
                    <p>ไม่พบผลลัพธ์สำหรับ "<?= sanitize($searchQuery) ?>"</p>
                </div>
            </div>
        <?php else: ?>
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-list-alt"></i>
                    ผลการค้นหา (<?= count($results) ?> รายการ)
                </div>

                <?php foreach ($results as $idx => $item): ?>
                <div class="card-glass p-3 mb-3 animate-slideIn" style="animation-delay: <?= $idx * 0.05 ?>s;">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <?php if ($item['_type'] === 'asset'): ?>
                                    <span class="badge" style="background: linear-gradient(135deg, #667eea, #764ba2); font-size: 0.7rem;">สินทรัพย์</span>
                                    <strong>#<?= $item['id'] ?></strong>
                                    <span class="text-muted">|</span>
                                    <span><?= sanitize($item['asset_id']) ?></span>
                                <?php else: ?>
                                    <span class="badge" style="background: linear-gradient(135deg, #f093fb, #f5576c); font-size: 0.7rem;"><?= requestTypeLabel($item['request_type']) ?></span>
                                    <strong>#<?= $item['id'] ?></strong>
                                    <?php if ($item['item_number']): ?>
                                        <span class="text-muted">|</span>
                                        <span><?= sanitize($item['item_number']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($item['_type'] === 'asset'): ?>
                                <div class="small text-muted mb-1">
                                    <i class="fas fa-building me-1"></i><?= sanitize($item['department']) ?>
                                    <span class="mx-1">•</span>
                                    <i class="fas fa-layer-group me-1"></i><?= sanitize($item['asset_group']) ?>
                                    <span class="mx-1">•</span>
                                    <i class="fas fa-barcode me-1"></i><?= sanitize($item['serial_number']) ?>
                                </div>
                            <?php else: ?>
                                <div class="small mb-1"><?= sanitize($item['item_name']) ?></div>
                                <?php if ($item['new_item_name']): ?>
                                    <div class="small text-info"><i class="fas fa-arrow-right me-1"></i><?= sanitize($item['new_item_name']) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Timeline -->
                            <div class="mt-3">
                                <div class="d-flex gap-4 small">
                                    <div class="<?= in_array($item['status'], ['Pending', 'Processing', 'Completed']) ? 'text-success' : 'text-muted' ?>">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        ส่งคำขอ<br>
                                        <small class="text-muted"><?= thaiDate($item['created_at'], 'compact') ?></small>
                                    </div>
                                    <div class="<?= in_array($item['status'], ['Processing', 'Completed']) ? 'text-success' : 'text-muted' ?>">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        กำลังดำเนินการ
                                    </div>
                                    <div class="<?= $item['status'] === 'Completed' ? 'text-success' : 'text-muted' ?>">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        เสร็จสิ้น<br>
                                        <?php if ($item['finished_at']): ?>
                                            <small class="text-muted"><?= thaiDate($item['finished_at'], 'compact') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <?= statusBadge($item['status']) ?>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-user me-1"></i><?= sanitize($item['requester'] ?? 'N/A') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="form-section">
            <div class="empty-state">
                <i class="fas fa-search d-block"></i>
                <p>กรุณาป้อนคำค้นหาเพื่อติดตามสถานะ</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
