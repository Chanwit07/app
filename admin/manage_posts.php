<?php
/**
 * Admin - จัดการเว็บบล็อก (บทความทั้งหมด)
 */
$pageTitle = 'จัดการบทความทั้งหมด';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all posts with author name
$posts = [];
$sql = "SELECT p.*, u.username as author_name 
        FROM blog_posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        ORDER BY p.id DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<style>
    .post-manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .post-manage-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .post-manage-card .card-gradient-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1a237e, #3949ab);
    }

    .post-cover-thumb {
        width: 100px;
        height: 70px;
        border-radius: 8px;
        object-fit: cover;
        background-color: #f0f2f5;
        border: 1px solid #e0e0e0;
    }

    .status-badge {
        font-size: .8rem;
    }

    .btn-action {
        border-radius: 8px;
        transition: all .3s;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
        opacity: .7;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        display: block;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-file-alt me-2 text-primary"></i>จัดการบทความทั้งหมด</h4>
        <p class="text-muted mb-0">เพิ่ม แก้ไข เผยแพร่บทความบนระบบ Blog</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/post_form.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>เขียนบทความใหม่
    </a>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <i class="fas fa-file-alt"></i>
        <h5>ยังไม่มีบทความ</h5>
        <p>กดปุ่ม "เขียนบทความใหม่" เพื่อเริ่มต้นเขียนบล็อก</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($posts as $item): ?>
            <div class="col-12" id="post-card-<?= $item['id'] ?>">
                <div class="post-manage-card">
                    <div class="card-gradient-bar"></div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start flex-grow-1 me-3">
                            <?php if (!empty($item['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/blog/<?= htmlspecialchars($item['cover_image']) ?>"
                                    class="post-cover-thumb me-3" alt="Cover">
                            <?php else: ?>
                                <div class="post-cover-thumb me-3 d-flex align-items-center justify-content-center text-muted">
                                    <i class="fas fa-image fs-4"></i>
                                </div>
                            <?php endif; ?>

                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <?php if ($item['status'] === 'published'): ?>
                                        <span class="badge bg-success status-badge"><i
                                                class="fas fa-check-circle me-1"></i>เผยแพร่แล้ว</span>
                                    <?php elseif ($item['status'] === 'draft'): ?>
                                        <span class="badge bg-warning text-dark status-badge"><i
                                                class="fas fa-pen me-1"></i>ฉบับร่าง</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge"><i
                                                class="fas fa-archive me-1"></i>เก็บถาวร</span>
                                    <?php endif; ?>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i>อ่าน
                                        <?= $item['read_time'] ?> นาที
                                    </small>
                                </div>
                                <h6 class="fw-bold mb-1">
                                    <a href="<?= BASE_URL ?>/blog_detail.php?slug=<?= htmlspecialchars($item['slug']) ?>"
                                        target="_blank" class="text-dark text-decoration-none hover-primary">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h6>
                                <p class="text-muted mb-1 d-none d-md-block"
                                    style="font-size:.88rem; max-width: 800px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($item['excerpt']) ?>
                                </p>
                                <div class="text-muted" style="font-size: .85rem;">
                                    <i class="fas fa-user me-1"></i>
                                    <?= htmlspecialchars($item['author_name']) ?> &nbsp;|&nbsp;
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($item['published_at'])) ?> &nbsp;|&nbsp;
                                    <i class="fas fa-eye me-1"></i>
                                    <?= number_format($item['views']) ?> ครั้ง
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2 flex-shrink-0">
                            <a href="<?= BASE_URL ?>/admin/post_form.php?id=<?= $item['id'] ?>"
                                class="btn btn-sm btn-outline-primary btn-action">
                                <i class="fas fa-edit me-1"></i>แก้ไข
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-action"
                                onclick="deletePost(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['title']), ENT_QUOTES) ?>')">
                                <i class="fas fa-trash me-1"></i>ลบ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$baseUrl = BASE_URL;
$extraJs = <<<JS
<script>
var BASE_URL = '{$baseUrl}';

function deletePost(id, title) {
    if (!confirm('ต้องการลบบทความ "' + title + '" หรือไม่? ข้อมูลจะไม่สามารถกู้คืนได้')) return;
    
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(BASE_URL + '/actions/save_blog_posts.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('post-card-' + id)?.remove();
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        });
}

function showToast(msg, type) {
    var html = '<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed" style="top:80px;right:20px;z-index:9999;min-width:300px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15)">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    document.body.insertAdjacentHTML('beforeend', html);
    setTimeout(() => { var a = document.querySelector('.alert'); if(a) a.remove(); }, 3000);
}
</script>
JS;
require_once __DIR__ . '/../includes/footer.php';
?>