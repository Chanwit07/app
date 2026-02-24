<?php
/**
 * Reports Dashboard - ดูรายงาน Power BI / Dashboard
 */
$pageTitle = 'รายงานอัจฉริยะ';
require_once __DIR__ . '/includes/header.php';

// Fetch active reports
$reports = [];
$res = $conn->query("SELECT * FROM dashboard_reports WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Get selected report (from URL parameter or first report)
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

// Default to first report if none selected
if (!$selectedReport && !empty($reports)) {
    $selectedReport = $reports[0];
    $selectedId = (int) $selectedReport['id'];
}
?>

<style>
    /* ======= Reports Page Styles ======= */
    .reports-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .reports-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .reports-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .reports-hero h3 {
        font-weight: 700;
        font-size: 1.4rem;
        margin-bottom: 0.3rem;
        position: relative;
        z-index: 1;
    }

    .reports-hero p {
        opacity: 0.85;
        font-size: 0.9rem;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .report-tabs {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .report-tab {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.7rem 1.2rem;
        background: var(--card-bg);
        border: 2px solid var(--card-border);
        border-radius: 14px;
        text-decoration: none;
        color: #4a5568;
        font-size: 0.85rem;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .report-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 14px;
    }

    .report-tab:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        color: #4a5568;
        text-decoration: none;
    }

    .report-tab.active {
        border-color: transparent;
        color: #fff;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .report-tab .tab-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: var(--transition);
    }

    .report-tab:not(.active) .tab-icon {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .report-tab.active .tab-icon {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .report-tab .tab-text {
        position: relative;
        z-index: 1;
    }

    .embed-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        position: relative;
        transition: var(--transition);
    }

    .embed-container:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
    }

    .embed-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-bottom: 1px solid var(--card-border);
    }

    .embed-header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .embed-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #fff;
    }

    .embed-header h5 {
        font-weight: 600;
        font-size: 1rem;
        margin: 0;
        color: var(--dark);
    }

    .embed-header p {
        font-size: 0.78rem;
        color: #999;
        margin: 0;
    }

    .embed-actions {
        display: flex;
        gap: 0.5rem;
    }

    .embed-actions .btn {
        border-radius: 10px;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }

    .embed-frame-wrapper {
        position: relative;
        width: 100%;
        height: calc(100vh - 280px);
        min-height: 500px;
        background: #f8f9fa;
    }

    .embed-frame-wrapper iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Loading Skeleton */
    .embed-loader {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        z-index: 5;
        transition: opacity 0.5s ease;
    }

    .embed-loader.loaded {
        opacity: 0;
        pointer-events: none;
    }

    .loader-pulse {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        animation: loaderPulse 1.5s infinite ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    @keyframes loaderPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.7;
        }
    }

    .loader-bar {
        width: 200px;
        height: 4px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.75rem;
    }

    .loader-bar-inner {
        height: 100%;
        width: 40%;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 4px;
        animation: loaderBar 1.5s infinite ease-in-out;
    }

    @keyframes loaderBar {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(350%);
        }
    }

    /* Empty State */
    .reports-empty {
        text-align: center;
        padding: 5rem 2rem;
    }

    .reports-empty-icon {
        width: 100px;
        height: 100px;
        border-radius: 24px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #667eea;
        margin-bottom: 1.5rem;
    }

    .reports-empty h4 {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .reports-empty p {
        color: #999;
        font-size: 0.9rem;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Fullscreen Mode */
    .embed-container.fullscreen-mode {
        position: fixed;
        inset: 0;
        z-index: 9999;
        border-radius: 0;
        margin: 0;
    }

    .embed-container.fullscreen-mode .embed-frame-wrapper {
        height: calc(100vh - 62px);
    }

    @media (max-width: 767.98px) {
        .reports-hero {
            padding: 1.5rem;
        }

        .reports-hero h3 {
            font-size: 1.15rem;
        }

        .report-tabs {
            gap: 0.5rem;
        }

        .report-tab {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .report-tab .tab-icon {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }

        .embed-frame-wrapper {
            height: calc(100vh - 320px);
            min-height: 350px;
        }
    }
</style>

<div class="animate-fadeInUp">
    <!-- Hero Banner -->
    <div class="reports-hero">
        <h3><i class="fas fa-chart-line me-2"></i>รายงานอัจฉริยะ (Dashboard Reports)</h3>
        <p>ดูข้อมูลเชิงวิเคราะห์แบบเรียลไทม์ผ่าน Power BI และ Dashboard อื่นๆ</p>
    </div>

    <?php if (empty($reports)): ?>
        <!-- Empty State -->
        <div class="card-glass">
            <div class="reports-empty">
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
                    <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-primary-gradient mt-3">
                        <i class="fas fa-plus me-2"></i>เพิ่มรายงานใหม่
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Report Tabs -->
        <div class="report-tabs" id="reportTabs">
            <?php foreach ($reports as $r): ?>
                <a href="?id=<?= $r['id'] ?>" class="report-tab <?= (int) $r['id'] === $selectedId ? 'active' : '' ?>"
                    style="<?= (int) $r['id'] === $selectedId ? 'background: linear-gradient(135deg, ' . sanitize($r['color_from']) . ', ' . sanitize($r['color_to']) . ');' : '' ?>">
                    <div class="tab-icon">
                        <i class="fas <?= sanitize($r['icon']) ?>"></i>
                    </div>
                    <span class="tab-text">
                        <?= sanitize($r['title']) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Embed Container -->
        <?php if ($selectedReport): ?>
            <div class="embed-container" id="embedContainer">
                <div class="embed-header">
                    <div class="embed-header-left">
                        <div class="embed-header-icon"
                            style="background: linear-gradient(135deg, <?= sanitize($selectedReport['color_from']) ?>, <?= sanitize($selectedReport['color_to']) ?>);">
                            <i class="fas <?= sanitize($selectedReport['icon']) ?>"></i>
                        </div>
                        <div>
                            <h5>
                                <?= sanitize($selectedReport['title']) ?>
                            </h5>
                            <?php if (!empty($selectedReport['description'])): ?>
                                <p>
                                    <?= sanitize($selectedReport['description']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="embed-actions">
                        <button class="btn btn-outline-secondary btn-sm" onclick="toggleFullscreen()" title="เต็มหน้าจอ">
                            <i class="fas fa-expand" id="fullscreenIcon"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshEmbed()" title="รีเฟรช">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="<?= sanitize($selectedReport['embed_url']) ?>" target="_blank"
                            class="btn btn-outline-primary btn-sm" title="เปิดในแท็บใหม่">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="embed-frame-wrapper">
                    <!-- Loader -->
                    <div class="embed-loader" id="embedLoader">
                        <div class="loader-pulse">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="text-muted" style="font-size: 0.9rem;">กำลังโหลดรายงาน...</span>
                        <div class="loader-bar">
                            <div class="loader-bar-inner"></div>
                        </div>
                    </div>
                    <!-- Iframe -->
                    <iframe id="reportFrame" src="<?= htmlspecialchars($selectedReport['embed_url'], ENT_QUOTES, 'UTF-8') ?>"
                        allowfullscreen="true" onload="onFrameLoaded()"></iframe>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$extraJs = <<<'JS'
<script>
function onFrameLoaded() {
    const loader = document.getElementById('embedLoader');
    if (loader) {
        loader.classList.add('loaded');
    }
}

function toggleFullscreen() {
    const container = document.getElementById('embedContainer');
    const icon = document.getElementById('fullscreenIcon');
    if (!container) return;

    container.classList.toggle('fullscreen-mode');
    if (container.classList.contains('fullscreen-mode')) {
        icon.className = 'fas fa-compress';
        document.body.style.overflow = 'hidden';
    } else {
        icon.className = 'fas fa-expand';
        document.body.style.overflow = '';
    }
}

function refreshEmbed() {
    const frame = document.getElementById('reportFrame');
    const loader = document.getElementById('embedLoader');
    if (!frame) return;

    if (loader) {
        loader.classList.remove('loaded');
    }
    frame.src = frame.src;
}

// ESC key to exit fullscreen
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const container = document.getElementById('embedContainer');
        if (container && container.classList.contains('fullscreen-mode')) {
            toggleFullscreen();
        }
    }
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>