<?php
/**
 * Learning Hub - User-facing course directory
 * Displays all active learning materials as cards
 */
$pageTitle = 'สื่อการเรียนรู้';
require_once __DIR__ . '/includes/header.php';

// Fetch active materials
$materials = [];
$res = $conn->query("SELECT * FROM learning_materials WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $materials[] = $row;
    }
}

// Get unique categories
$categories = [];
foreach ($materials as $m) {
    if (!empty($m['category']) && !in_array($m['category'], $categories)) {
        $categories[] = $m['category'];
    }
}
?>

<style>
    .learning-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .learning-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .learning-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .learning-hero h2 {
        position: relative;
        z-index: 1;
    }

    .learning-hero p {
        position: relative;
        z-index: 1;
        opacity: 0.9;
    }

    .video-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
        height: 100%;
    }

    .video-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
    }

    .video-thumb-wrapper {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16/9;
    }

    .video-thumb-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .video-card:hover .video-thumb-wrapper img {
        transform: scale(1.08);
    }

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .video-card:hover .play-overlay {
        opacity: 1;
    }

    .play-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
    }

    .video-card:hover .play-icon {
        transform: scale(1.1);
    }

    .video-card-body {
        padding: 1.2rem;
    }

    .video-card-title {
        font-weight: 600;
        font-size: 1rem;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .video-card-desc {
        font-size: 0.85rem;
        color: #666;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 0.8rem;
    }

    .category-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        background: linear-gradient(135deg, #667eea20, #764ba220);
        color: #667eea;
        font-weight: 500;
    }

    .filter-btn {
        padding: 0.4rem 1.2rem;
        border-radius: 20px;
        border: 1.5px solid #e0e0e0;
        background: white;
        color: #555;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-btn:hover,
    .filter-btn.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
</style>

<div class="animate-fadeInUp">

    <!-- Hero Section -->
    <div class="learning-hero">
        <h2><i class="fas fa-graduation-cap me-2"></i>ศูนย์การเรียนรู้</h2>
        <p class="mb-0">เรียนรู้ขั้นตอนการทำงาน และเพิ่มทักษะผ่านวิดีโอสื่อการสอนที่คัดสรรมาอย่างดี</p>
    </div>

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
        <div class="d-flex flex-wrap gap-2 mb-4" id="categoryFilter">
            <button class="filter-btn active" data-category="all">
                <i class="fas fa-th-large me-1"></i>ทั้งหมด
            </button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" data-category="<?= sanitize($cat) ?>">
                    <?= sanitize($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Video Grid -->
    <?php if (empty($materials)): ?>
        <div class="empty-state">
            <i class="fas fa-video d-block"></i>
            <h5 class="text-muted">ยังไม่มีสื่อการเรียนรู้ในขณะนี้</h5>
            <p class="text-muted">กรุณาติดต่อผู้ดูแลระบบเพื่อเพิ่มเนื้อหา</p>
        </div>
    <?php else: ?>
        <div class="row g-4" id="videoGrid">
            <?php foreach ($materials as $m): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3 video-item" data-category="<?= sanitize($m['category'] ?? '') ?>">
                    <a href="<?= BASE_URL ?>/view_learning.php?id=<?= $m['id'] ?>" class="text-decoration-none">
                        <div class="video-card">
                            <div class="video-thumb-wrapper">
                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($m['youtube_video_id']) ?>/hqdefault.jpg"
                                    alt="<?= sanitize($m['title']) ?>" loading="lazy">
                                <div class="play-overlay">
                                    <div class="play-icon">
                                        <i class="fas fa-play ms-1"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="video-card-body">
                                <div class="video-card-title">
                                    <?= sanitize($m['title']) ?>
                                </div>
                                <?php if ($m['description']): ?>
                                    <div class="video-card-desc">
                                        <?= sanitize($m['description']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if ($m['category']): ?>
                                        <span class="category-badge">
                                            <?= sanitize($m['category']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>
                                    <small class="text-muted"><i class="far fa-calendar me-1"></i>
                                        <?= thaiDate($m['created_at'], 'compact') ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$extraJs = <<<'JS'
<script>
// Category Filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Active state
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const cat = this.dataset.category;
        document.querySelectorAll('.video-item').forEach(item => {
            if (cat === 'all' || item.dataset.category === cat) {
                item.style.display = '';
                item.style.animation = 'fadeInUp 0.4s ease forwards';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>