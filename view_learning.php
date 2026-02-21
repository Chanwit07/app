<?php
/**
 * View Learning Material - Video Player Page
 * Embeds YouTube video with lesson details
 */
$pageTitle = 'ดูวิดีโอการเรียนรู้';
require_once __DIR__ . '/includes/header.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>ไม่พบสื่อการเรียนรู้ที่ระบุ</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch the learning material
$stmt = $conn->prepare("SELECT * FROM learning_materials WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$lesson = $result->fetch_assoc();
$stmt->close();

if (!$lesson) {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>ไม่พบสื่อการเรียนรู้นี้ หรือถูกปิดใช้งานแล้ว</div>';
    echo '<a href="' . BASE_URL . '/learning.php" class="btn btn-primary-gradient mt-2"><i class="fas fa-arrow-left me-1"></i>กลับหน้าสื่อการเรียนรู้</a>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = sanitize($lesson['title']);

// Fetch related materials (same category, exclude current)
$related = [];
if (!empty($lesson['category'])) {
    $stmtR = $conn->prepare("SELECT * FROM learning_materials WHERE status = 'active' AND category = ? AND id != ? ORDER BY sort_order ASC, created_at DESC LIMIT 4");
    $stmtR->bind_param("si", $lesson['category'], $id);
    $stmtR->execute();
    $resR = $stmtR->get_result();
    while ($row = $resR->fetch_assoc()) {
        $related[] = $row;
    }
    $stmtR->close();
}
?>

<style>
    .video-player-wrapper {
        background: #000;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
        margin-bottom: 2rem;
    }

    .video-player-wrapper iframe {
        width: 100%;
        aspect-ratio: 16/9;
        display: block;
    }

    .lesson-info {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
    }

    .lesson-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .lesson-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .lesson-meta span {
        font-size: 0.85rem;
        color: #888;
    }

    .lesson-description {
        font-size: 0.95rem;
        line-height: 1.8;
        color: #444;
        white-space: pre-line;
    }

    .related-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .related-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }

    .related-card img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
    }

    .related-card .card-body {
        padding: 0.8rem 1rem;
    }

    .related-card .card-title {
        font-size: 0.85rem;
        font-weight: 600;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin: 0;
    }
</style>

<div class="animate-fadeInUp">

    <!-- Back Button -->
    <a href="<?= BASE_URL ?>/learning.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i>กลับหน้าสื่อการเรียนรู้
    </a>

    <!-- Video Player -->
    <div class="video-player-wrapper">
        <iframe
            src="https://www.youtube.com/embed/<?= htmlspecialchars($lesson['youtube_video_id']) ?>?rel=0&modestbranding=1"
            title="<?= sanitize($lesson['title']) ?>" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen>
        </iframe>
    </div>

    <!-- Lesson Info -->
    <div class="lesson-info mb-4">
        <h1 class="lesson-title">
            <?= sanitize($lesson['title']) ?>
        </h1>
        <div class="lesson-meta">
            <?php if ($lesson['category']): ?>
                <span>
                    <span class="badge bg-info bg-opacity-10 text-info" style="font-size: 0.8rem;">
                        <i class="fas fa-folder me-1"></i>
                        <?= sanitize($lesson['category']) ?>
                    </span>
                </span>
            <?php endif; ?>
            <span><i class="far fa-calendar-alt me-1"></i>
                <?= thaiDate($lesson['created_at'], 'long') ?>
            </span>
        </div>

        <?php if (!empty($lesson['description'])): ?>
            <div class="lesson-description">
                <?= nl2br(sanitize($lesson['description'])) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Videos -->
    <?php if (!empty($related)): ?>
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-video me-2 text-primary"></i>วิดีโอที่เกี่ยวข้อง</h5>
            <div class="row g-3">
                <?php foreach ($related as $r): ?>
                    <div class="col-6 col-md-3">
                        <a href="<?= BASE_URL ?>/view_learning.php?id=<?= $r['id'] ?>" class="text-decoration-none">
                            <div class="related-card">
                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($r['youtube_video_id']) ?>/mqdefault.jpg"
                                    alt="<?= sanitize($r['title']) ?>" loading="lazy">
                                <div class="card-body">
                                    <h6 class="card-title text-dark">
                                        <?= sanitize($r['title']) ?>
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>