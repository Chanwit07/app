<?php
/**
 * Reports Dashboard - ดูรายงาน Power BI / Dashboard
 * Redesigned with premium card-based gallery layout
 */
$pageTitle = 'รายงานอัจฉริยะ';
require_once __DIR__ . '/includes/header.php';
checkRole(['user', 'admin', 'super_admin']);

// Fetch active reports
$reports = [];
$res = $conn->query("SELECT * FROM dashboard_reports WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Get unique categories
$categories = array_unique(array_column($reports, 'category'));

// Get selected report (from URL parameter)
$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$selectedReport = null;
if ($selectedId > 0) {
    foreach ($reports as $r) {
        if ((int) $r['id'] === $selectedId) {
            $selectedReport = $r;
            break;
        }
    }
}
?>

<style>
    /* ======= Reports Dashboard ======= */
    .reports-hero {
        background: linear-gradient(135deg, #0d1b3e 0%, #1a237e 30%, #4a148c 70%, #7b1fa2 100%);
        border-radius: 24px;
        padding: 2.5rem 3rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .reports-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -60px;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        animation: heroFloat 6s ease-in-out infinite;
    }

    .reports-hero::after {
        content: '';
        position: absolute;
        bottom: -120px;
        left: 30%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(103, 58, 183, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        animation: heroFloat 8s ease-in-out infinite reverse;
    }

    @keyframes heroFloat {

        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-20px) scale(1.05);
        }
    }

    .reports-hero .hero-content {
        position: relative;
        z-index: 2;
    }

    .reports-hero h3 {
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .reports-hero h3 i {
        background: linear-gradient(135deg, #ffd54f, #ff8a65);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-right: 8px;
    }

    .reports-hero p {
        opacity: 0.8;
        font-size: 0.92rem;
        margin: 0;
        max-width: 500px;
    }

    .hero-decoration {
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        display: flex;
        gap: 16px;
    }

    .hero-deco-item {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.5);
        font-size: 1.2rem;
        animation: decoFloat 3s ease-in-out infinite;
    }

    .hero-deco-item:nth-child(2) {
        animation-delay: -1s;
        transform: translateY(-10px);
    }

    .hero-deco-item:nth-child(3) {
        animation-delay: -2s;
        transform: translateY(5px);
    }

    .hero-deco-item:nth-child(4) {
        animation-delay: -0.5s;
    }

    @keyframes decoFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    /* Quick Stats */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 18px;
        padding: 1.2rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    }

    .stat-card .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
        flex-shrink: 0;
    }

    .stat-card .stat-number {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        color: var(--dark);
    }

    .stat-card .stat-text {
        font-size: 0.78rem;
        color: #888;
        margin-top: 2px;
    }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        border: 1.5px solid var(--card-border);
        background: var(--card-bg);
        color: #666;
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
    }

    .filter-btn:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    }

    /* Report Cards Grid */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .report-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }

    .report-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .report-card-gradient {
        height: 6px;
        width: 100%;
    }

    .report-card-body {
        padding: 1.5rem;
    }

    .report-card-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .report-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .report-card-info h5 {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 0.3rem;
        color: var(--dark);
        line-height: 1.4;
    }

    .report-card-info .category-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .report-card-desc {
        font-size: 0.82rem;
        color: #888;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .report-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .report-card-url {
        font-size: 0.7rem;
        color: #aaa;
        max-width: 160px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-card-url i {
        margin-right: 4px;
    }

    .btn-view-report {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.45rem 1rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-view-report:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: #fff;
    }

    /* Open in New Tab button */
    .btn-open-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.45rem 0.8rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        border: 1.5px solid rgba(102, 126, 234, 0.2);
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-open-tab:hover {
        background: rgba(102, 126, 234, 0.15);
        color: #5a6fd6;
    }

    /* ======= Report Viewer (Overlay) ======= */
    .report-viewer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.3s ease;
    }

    .report-viewer-overlay.active {
        display: flex;
        flex-direction: column;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .viewer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem 1.5rem;
        background: linear-gradient(135deg, #0d1b3e, #1a237e);
        color: #fff;
        flex-shrink: 0;
    }

    .viewer-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .viewer-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .viewer-header h5 {
        font-weight: 600;
        font-size: 0.95rem;
        margin: 0;
    }

    .viewer-header p {
        font-size: 0.75rem;
        opacity: 0.65;
        margin: 0;
    }

    .viewer-actions {
        display: flex;
        gap: 0.5rem;
    }

    .viewer-actions .btn {
        border-radius: 10px;
        font-size: 0.8rem;
        padding: 0.4rem 0.7rem;
        color: rgba(255, 255, 255, 0.8);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .viewer-actions .btn:hover {
        color: #fff;
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.1);
    }

    .viewer-frame {
        flex: 1;
        background: #f0f2f5;
        position: relative;
    }

    .viewer-frame iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .viewer-loader {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f0f2f5;
        z-index: 3;
        transition: opacity 0.5s ease;
    }

    .viewer-loader.loaded {
        opacity: 0;
        pointer-events: none;
    }

    .viewer-loader .pulse-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
        }

        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
        }
    }

    .viewer-loader .loader-bar {
        width: 200px;
        height: 4px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.75rem;
    }

    .viewer-loader .loader-bar-inner {
        height: 100%;
        width: 40%;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 4px;
        animation: loaderSlide 1.5s infinite ease-in-out;
    }

    @keyframes loaderSlide {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(350%);
        }
    }

    /* Empty State */
    .reports-empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 24px;
    }

    .reports-empty-icon {
        width: 100px;
        height: 100px;
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #667eea;
        margin-bottom: 1.5rem;
    }

    .reports-empty-state h4 {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .reports-empty-state p {
        color: #999;
        font-size: 0.9rem;
        max-width: 400px;
        margin: 0 auto 1rem;
    }

    @media (max-width: 991.98px) {
        .quick-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .hero-decoration {
            display: none;
        }

        .reports-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .reports-hero {
            padding: 1.5rem;
        }

        .reports-hero h3 {
            font-size: 1.2rem;
        }

        .quick-stats {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<div class="animate-fadeInUp">
    <!-- Hero Banner -->
    <div class="reports-hero">
        <div class="hero-content">
            <h3><i class="fas fa-chart-line"></i>รายงานอัจฉริยะ</h3>
            <p>ดูข้อมูลเชิงวิเคราะห์แบบเรียลไทม์ผ่าน Power BI และ Dashboard ของฝ่ายการช่างกล</p>
        </div>
        <div class="hero-decoration">
            <div class="hero-deco-item"><i class="fas fa-chart-bar"></i></div>
            <div class="hero-deco-item"><i class="fas fa-chart-pie"></i></div>
            <div class="hero-deco-item"><i class="fas fa-chart-area"></i></div>
            <div class="hero-deco-item"><i class="fas fa-database"></i></div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="stat-card">
            <div class="stat-icon-box" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-number"><?= count($reports) ?></div>
                <div class="stat-text">รายงานทั้งหมด</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="stat-number"><?= count(array_filter($reports, fn($r) => $r['is_active'])) ?></div>
                <div class="stat-text">เปิดใช้งาน</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <div class="stat-number"><?= count($categories) ?></div>
                <div class="stat-text">หมวดหมู่</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <div class="stat-number"><i class="fas fa-external-link-alt" style="font-size:1rem"></i></div>
                <div class="stat-text">เปิดในแท็บใหม่ได้</div>
            </div>
        </div>
    </div>

    <?php if (empty($reports)): ?>
        <!-- Empty State -->
        <div class="reports-empty-state">
            <div class="reports-empty-icon">
                <i class="fas fa-chart-area"></i>
            </div>
            <h4>ยังไม่มีรายงาน Dashboard</h4>
            <p>ยังไม่มีการเพิ่มรายงาน Dashboard ในระบบ
                <?php if (isAdmin()): ?>
                    กรุณาเพิ่มรายงานใหม่ผ่านหน้าจัดการรายงาน
                <?php else: ?>
                    กรุณาติดต่อผู้ดูแลระบบเพื่อเพิ่มรายงาน
                <?php endif; ?>
            </p>
            <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/reports.php" class="btn-view-report mt-2">
                    <i class="fas fa-plus"></i>เพิ่มรายงานใหม่
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Category Filter -->
        <div class="filter-bar">
            <span style="font-size:0.85rem;font-weight:600;color:var(--dark);margin-right:4px;">
                <i class="fas fa-filter me-1"></i>กรองหมวดหมู่:
            </span>
            <button class="filter-btn active" onclick="filterReports('all', this)">ทั้งหมด</button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" onclick="filterReports('<?= htmlspecialchars($cat) ?>', this)">
                    <?= sanitize($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Report Cards Grid -->
        <div class="reports-grid" id="reportsGrid">
            <?php foreach ($reports as $idx => $r):
                $colorFrom = sanitize($r['color_from']);
                $colorTo = sanitize($r['color_to']);
                $icon = sanitize($r['icon']);
                $embedUrl = htmlspecialchars($r['embed_url'], ENT_QUOTES, 'UTF-8');
                ?>
                <div class="report-card" data-category="<?= htmlspecialchars($r['category']) ?>"
                    style="animation-delay: <?= $idx * 80 ?>ms">
                    <div class="report-card-gradient"
                        style="background: linear-gradient(90deg, <?= $colorFrom ?>, <?= $colorTo ?>);"></div>
                    <div class="report-card-body">
                        <div class="report-card-header">
                            <div class="report-card-icon"
                                style="background: linear-gradient(135deg, <?= $colorFrom ?>, <?= $colorTo ?>);">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div class="report-card-info">
                                <h5><?= sanitize($r['title']) ?></h5>
                                <span class="category-badge" style="background: <?= $colorFrom ?>15; color: <?= $colorFrom ?>;">
                                    <?= sanitize($r['category']) ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($r['description'])): ?>
                            <p class="report-card-desc"><?= sanitize($r['description']) ?></p>
                        <?php endif; ?>
                        <div class="report-card-footer">
                            <a href="<?= $embedUrl ?>" target="_blank" class="btn-open-tab" onclick="event.stopPropagation()">
                                <i class="fas fa-external-link-alt"></i>เปิดแท็บใหม่
                            </a>
                            <button class="btn-view-report"
                                onclick="openReportViewer('<?= $embedUrl ?>', '<?= addslashes(sanitize($r['title'])) ?>', '<?= addslashes(sanitize($r['description'] ?? '')) ?>', '<?= $icon ?>', '<?= $colorFrom ?>', '<?= $colorTo ?>')">
                                <i class="fas fa-play-circle"></i>ดูรายงาน
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isAdmin()): ?>
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline-primary"
                    style="border-radius:14px;padding:0.6rem 1.5rem;font-size:0.85rem;">
                    <i class="fas fa-cog me-2"></i>จัดการรายงาน Dashboard
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Report Viewer Overlay -->
<div class="report-viewer-overlay" id="reportViewer">
    <div class="viewer-header">
        <div class="viewer-header-left">
            <div class="viewer-header-icon" id="viewerIcon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <h5 id="viewerTitle">รายงาน</h5>
                <p id="viewerDesc"></p>
            </div>
        </div>
        <div class="viewer-actions">
            <button class="btn btn-outline-light btn-sm" onclick="refreshViewer()" title="รีเฟรช">
                <i class="fas fa-sync-alt"></i>
            </button>
            <a href="#" class="btn btn-outline-light btn-sm" id="viewerNewTab" target="_blank" title="เปิดแท็บใหม่">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <button class="btn btn-outline-light btn-sm" onclick="closeViewer()" title="ปิด">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="viewer-frame">
        <div class="viewer-loader" id="viewerLoader">
            <div class="pulse-icon"><i class="fas fa-chart-bar"></i></div>
            <span class="text-muted" style="font-size:0.9rem;">กำลังโหลดรายงาน...</span>
            <div class="loader-bar">
                <div class="loader-bar-inner"></div>
            </div>
        </div>
        <iframe id="viewerFrame" src="about:blank" allowfullscreen="true" onload="onViewerLoaded()"></iframe>
    </div>
</div>

<?php
// Build auto-open script if a specific report is selected via URL
$autoOpenScript = '';
if ($selectedReport) {
    $aoUrl = htmlspecialchars($selectedReport['embed_url'], ENT_QUOTES, 'UTF-8');
    $aoTitle = addslashes(sanitize($selectedReport['title']));
    $aoDesc = addslashes(sanitize($selectedReport['description'] ?? ''));
    $aoIcon = sanitize($selectedReport['icon']);
    $aoColorFrom = sanitize($selectedReport['color_from']);
    $aoColorTo = sanitize($selectedReport['color_to']);
    $autoOpenScript = "
    document.addEventListener('DOMContentLoaded', function() {
        openReportViewer('{$aoUrl}', '{$aoTitle}', '{$aoDesc}', '{$aoIcon}', '{$aoColorFrom}', '{$aoColorTo}');
    });";
}

$extraJs = <<<JS
<script>
// Filter reports by category
function filterReports(category, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.report-card').forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = '';
            card.style.animation = 'fadeInUp 0.4s ease forwards';
        } else {
            card.style.display = 'none';
        }
    });
}

// Open report viewer overlay
function openReportViewer(url, title, desc, icon, colorFrom, colorTo) {
    const viewer = document.getElementById('reportViewer');
    const frame = document.getElementById('viewerFrame');
    const loader = document.getElementById('viewerLoader');

    document.getElementById('viewerTitle').textContent = title;
    document.getElementById('viewerDesc').textContent = desc;
    document.getElementById('viewerNewTab').href = url;

    const iconEl = document.getElementById('viewerIcon');
    iconEl.style.background = 'linear-gradient(135deg, ' + colorFrom + ', ' + colorTo + ')';
    iconEl.innerHTML = '<i class="fas ' + icon + '"></i>';

    loader.classList.remove('loaded');
    frame.src = url;

    viewer.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function onViewerLoaded() {
    const loader = document.getElementById('viewerLoader');
    if (loader) loader.classList.add('loaded');
}

function closeViewer() {
    const viewer = document.getElementById('reportViewer');
    const frame = document.getElementById('viewerFrame');
    viewer.classList.remove('active');
    document.body.style.overflow = '';
    frame.src = 'about:blank';
}

function refreshViewer() {
    const frame = document.getElementById('viewerFrame');
    const loader = document.getElementById('viewerLoader');
    if (loader) loader.classList.remove('loaded');
    frame.src = frame.src;
}

// ESC key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeViewer();
});

// Staggered card animation on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.report-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + i * 100);
    });
});

{$autoOpenScript}
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>