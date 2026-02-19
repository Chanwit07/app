<?php
/**
 * Dashboard - สรุปสถิติและ KPI
 */
$pageTitle = 'แดชบอร์ด';
$extraCss = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>';
require_once __DIR__ . '/includes/header.php';
checkRole(['admin', 'super_admin']);

// ====== Statistics ======
$stats = ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0];
$tables = ['asset_requests', 'supply_requests'];

foreach ($tables as $tbl) {
    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM `$tbl` GROUP BY status");
    while ($row = $res->fetch_assoc()) {
        $statusKey = strtolower(trim($row['status']));
        if (isset($stats[$statusKey])) {
            $stats[$statusKey] += $row['cnt'];
        }
        $stats['total'] += $row['cnt'];
    }
}

// KPI: Average processing time (days) for completed requests
$avgDays = 0;
$avgCount = 0;
foreach ($tables as $tbl) {
    $res = $conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, finished_at)) as avg_hours FROM `$tbl` WHERE status = 'Completed' AND finished_at IS NOT NULL");
    $row = $res->fetch_assoc();
    if ($row['avg_hours'] !== null) {
        $avgDays += $row['avg_hours'];
        $avgCount++;
    }
}
$avgHours = $avgCount > 0 ? round($avgDays / $avgCount, 1) : 0;
$avgDaysDisplay = $avgHours > 0 ? round($avgHours / 24, 1) : 0;

// Monthly data for chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months", strtotime(date('Y-m-01'))));
    $monthEnd = date('Y-m-t', strtotime("-$i months", strtotime(date('Y-m-01'))));
    $monthLabel = thaiDate($monthStart, 'month');

    $assetCount = 0;
    $supplyCount = 0;

    $res = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests WHERE created_at BETWEEN '$monthStart' AND '$monthEnd 23:59:59'");
    $assetCount = $res->fetch_assoc()['cnt'];

    $res = $conn->query("SELECT COUNT(*) as cnt FROM supply_requests WHERE created_at BETWEEN '$monthStart' AND '$monthEnd 23:59:59'");
    $supplyCount = $res->fetch_assoc()['cnt'];

    $monthlyData[] = [
        'label' => $monthLabel,
        'asset' => $assetCount,
        'supply' => $supplyCount
    ];
}

// Today's count
$today = date('Y-m-d');
$todayAsset = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests WHERE DATE(created_at) = '$today'")->fetch_assoc()['cnt'];
$todaySupply = $conn->query("SELECT COUNT(*) as cnt FROM supply_requests WHERE DATE(created_at) = '$today'")->fetch_assoc()['cnt'];
$todayTotal = $todayAsset + $todaySupply;
?>

<div class="animate-fadeInUp">
    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);"><i
                            class="fas fa-layer-group"></i></div>
                    <span class="badge bg-light text-dark">ทั้งหมด</span>
                </div>
                <div class="stat-value">
                    <?= number_format($stats['total']) ?>
                </div>
                <div class="stat-label">คำขอทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f6d365, #fda085);"><i
                            class="fas fa-clock"></i></div>
                    <span class="badge bg-warning text-dark">
                        <?= $todayTotal ?> วันนี้
                    </span>
                </div>
                <div class="stat-value">
                    <?= number_format($stats['pending']) ?>
                </div>
                <div class="stat-label">รอดำเนินการ</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a1c4fd, #c2e9fb);"><i
                            class="fas fa-cog"></i></div>
                </div>
                <div class="stat-value">
                    <?= number_format($stats['processing']) ?>
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
                    <?= number_format($stats['completed']) ?>
                </div>
                <div class="stat-label">เสร็จสิ้น</div>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-glass p-4 text-center">
                <div class="mb-2"><i class="fas fa-tachometer-alt" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <div class="stat-value" style="font-size: 2.2rem;">
                    <?= $avgDaysDisplay ?>
                </div>
                <div class="stat-label">วัน - ระยะเวลาดำเนินการเฉลี่ย</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-glass p-4 text-center">
                <div class="mb-2"><i class="fas fa-building" style="font-size: 2rem; color: #667eea;"></i></div>
                <?php $totalAsset = $conn->query("SELECT COUNT(*) as c FROM asset_requests")->fetch_assoc()['c']; ?>
                <div class="stat-value" style="font-size: 2.2rem;">
                    <?= number_format($totalAsset) ?>
                </div>
                <div class="stat-label">คำขอสินทรัพย์</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-glass p-4 text-center">
                <div class="mb-2"><i class="fas fa-box-open" style="font-size: 2rem; color: #f093fb;"></i></div>
                <?php $totalSupply = $conn->query("SELECT COUNT(*) as c FROM supply_requests")->fetch_assoc()['c']; ?>
                <div class="stat-value" style="font-size: 2.2rem;">
                    <?= number_format($totalSupply) ?>
                </div>
                <div class="stat-label">คำขอพัสดุ</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-glass p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>คำขอรายเดือน (6 เดือนล่าสุด)
                </h6>
                <canvas id="monthlyChart" height="220"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-glass p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-doughnut me-2 text-primary"></i>สัดส่วนสถานะ</h6>
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$monthlyJson = json_encode($monthlyData, JSON_UNESCAPED_UNICODE);
$extraJs = <<<JS
<script>
// Monthly Chart
const monthlyData = {$monthlyJson};
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.label),
        datasets: [
            {
                label: 'สินทรัพย์',
                data: monthlyData.map(d => d.asset),
                backgroundColor: 'rgba(102, 126, 234, 0.7)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 6
            },
            {
                label: 'พัสดุ',
                data: monthlyData.map(d => d.supply),
                backgroundColor: 'rgba(240, 147, 251, 0.7)',
                borderColor: '#f093fb',
                borderWidth: 2,
                borderRadius: 6
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { family: 'Prompt' } } } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Prompt' } } },
            x: { ticks: { font: { family: 'Prompt' } } }
        }
    }
});

// Status Doughnut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['รอดำเนินการ', 'กำลังดำเนินการ', 'เสร็จสิ้น'],
        datasets: [{
            data: [{$stats['pending']}, {$stats['processing']}, {$stats['completed']}],
            backgroundColor: ['#fda085', '#a1c4fd', '#43e97b'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { family: 'Prompt' }, padding: 15 } }
        }
    }
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>