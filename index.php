<?php
/**
 * Index - Landing Page / Route based on role
 */
$pageTitle = 'หน้าหลัก';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 animate-fadeInUp">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card-glass p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"
                    style="background: linear-gradient(135deg, var(--primary), var(--secondary)); width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-hand-wave text-white" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1" style="font-weight: 600;">สวัสดี,
                        <?= sanitize($_SESSION['fullname'] ?? '') ?>
                    </h3>
                    <p class="mb-0 text-muted">ยินดีต้อนรับสู่ระบบบริหารจัดการพัสดุและสินทรัพย์ —
                        <?= thaiDate(null, 'long') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isAdmin()): ?>
        <!-- Admin Quick Stats -->
        <?php
        // Fetch stats
        $stats = [];
        $tables = ['asset_requests', 'supply_requests'];
        foreach ($tables as $tbl) {
            foreach (['Pending', 'Processing', 'Completed'] as $st) {
                $r = $conn->query("SELECT COUNT(*) as cnt FROM `$tbl` WHERE status = '$st'");
                $row = $r->fetch_assoc();
                $stats[$tbl][$st] = $row['cnt'];
            }
        }
        $totalPending = ($stats['asset_requests']['Pending'] ?? 0) + ($stats['supply_requests']['Pending'] ?? 0);
        $totalProcessing = ($stats['asset_requests']['Processing'] ?? 0) + ($stats['supply_requests']['Processing'] ?? 0);
        $totalCompleted = ($stats['asset_requests']['Completed'] ?? 0) + ($stats['supply_requests']['Completed'] ?? 0);
        $totalAll = $totalPending + $totalProcessing + $totalCompleted;
        ?>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);"><i
                            class="fas fa-layer-group"></i></div>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f6d365, #fda085);"><i
                            class="fas fa-clock"></i></div>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a1c4fd, #c2e9fb);"><i
                            class="fas fa-cog fa-spin"></i></div>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);"><i
                            class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value">
                    <?= number_format($totalCompleted) ?>
                </div>
                <div class="stat-label">เสร็จสิ้น</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="col-12">
        <h5 class="mb-3" style="font-weight: 600;"><i class="fas fa-bolt text-warning me-2"></i>เมนูลัด</h5>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="form-asset.php" class="text-decoration-none">
            <div class="card-glass p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-building" style="font-size: 2.5rem; color: var(--primary);"></i>
                </div>
                <h6 class="fw-bold mb-1">ขอรหัส/ยูนิตสินทรัพย์</h6>
                <small class="text-muted">แบบฟอร์มขอรหัสสินทรัพย์ใหม่</small>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="form-supply.php" class="text-decoration-none">
            <div class="card-glass p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-box-open" style="font-size: 2.5rem; color: var(--secondary);"></i>
                </div>
                <h6 class="fw-bold mb-1">ขอรหัสพัสดุ</h6>
                <small class="text-muted">ขอรหัสใหม่หรือแก้ไขรายละเอียด</small>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="tracking.php" class="text-decoration-none">
            <div class="card-glass p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-search-location" style="font-size: 2.5rem; color: var(--info);"></i>
                </div>
                <h6 class="fw-bold mb-1">ติดตามสถานะ</h6>
                <small class="text-muted">ค้นหาด้วยเลขที่สิ่งของ/สินทรัพย์</small>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>