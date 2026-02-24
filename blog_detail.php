<?php
// blog_detail.php
session_start();
require_once 'config.php';
require_once 'auth.php';

$slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: blog.php");
    exit;
}

// Fetch Post
$sql = "SELECT p.*, u.username as author_name 
        FROM blog_posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.slug = '$slug'";
$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    // 404 Not Found
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 ไม่พบบทความ</h1><a href='blog.php'>กลับหน้าแรก</a>";
    exit;
}

$post = $res->fetch_assoc();

// Check if published or admin
if ($post['status'] !== 'published' && !isAdmin()) {
    header("Location: blog.php");
    exit;
}

// Update View Count
if (!isset($_SESSION['viewed_posts'])) {
    $_SESSION['viewed_posts'] = [];
}
if (!in_array($post['id'], $_SESSION['viewed_posts'])) {
    $_SESSION['viewed_posts'][] = $post['id'];
    $conn->query("UPDATE blog_posts SET views = views + 1 WHERE id = " . $post['id']);
    $post['views']++;
}

// Fetch Tags
$tags = [];
$tagRes = $conn->query("SELECT t.* FROM blog_post_tags pt JOIN blog_tags t ON pt.tag_id = t.id WHERE pt.post_id = " . $post['id']);
$tagIds = [];
if ($tagRes) {
    while ($r = $tagRes->fetch_assoc()) {
        $tags[] = $r;
        $tagIds[] = $r['id'];
    }
}

// Fetch Related Posts (Matching Tags)
$relatedPosts = [];
if (!empty($tagIds)) {
    $tagIdsStr = implode(',', $tagIds);
    $relSql = "SELECT p.*, u.username as author_name 
               FROM blog_posts p 
               LEFT JOIN users u ON p.author_id = u.id 
               JOIN blog_post_tags pt ON p.id = pt.post_id
               WHERE pt.tag_id IN ($tagIdsStr) 
               AND p.id != {$post['id']} 
               AND p.status = 'published'
               GROUP BY p.id
               ORDER BY p.published_at DESC LIMIT 3";
    $relRes = $conn->query($relSql);
    if ($relRes) {
        while ($r = $relRes->fetch_assoc()) {
            $relatedPosts[] = $r;
        }
    }
}

$pageTitle = htmlspecialchars($post['title']) . ' - CMMS Blog';
$metaDescription = htmlspecialchars($post['excerpt']);
$metaImage = !empty($post['cover_image']) ? BASE_URL . '/uploads/blog/' . htmlspecialchars($post['cover_image']) : BASE_URL . '/assets/img/default-share.jpg';

require_once 'includes/header_frontend.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?>
    </title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $metaDescription ?>">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= BASE_URL ?>/blog_detail.php?slug=<?= $post['slug'] ?>">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $metaDescription ?>">
    <meta property="og:image" content="<?= $metaImage ?>">

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Highlight.js Theme -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">

    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3949ab;
            --bg-color: #f8f9fa;
            --text-color: #333;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.8;
            padding-top: 80px;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .post-header {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            padding: 3rem 1rem 2rem;
        }

        .post-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #111;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }

        .post-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .author-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .post-cover {
            width: 100%;
            max-width: 1000px;
            max-height: 500px;
            object-fit: cover;
            border-radius: 20px;
            margin: 0 auto 3rem;
            display: block;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .post-content-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.03);
            font-size: 1.15rem;
        }

        /* Markdown Styles */
        .markdown-body h1,
        .markdown-body h2,
        .markdown-body h3,
        .markdown-body h4 {
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            color: #111;
        }

        .markdown-body h2 {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }

        .markdown-body p {
            margin-bottom: 1.5rem;
        }

        .markdown-body img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 2rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .markdown-body a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .markdown-body a:hover {
            text-decoration: underline;
        }

        .markdown-body blockquote {
            border-left: 5px solid var(--primary-color);
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-radius: 0 12px 12px 0;
            font-style: italic;
            color: #555;
            margin: 2rem 0;
        }

        .markdown-body pre {
            background: #0d1117;
            border-radius: 12px;
            padding: 1.5rem;
            overflow-x: auto;
            margin: 2rem 0;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .markdown-body code {
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
        }

        .markdown-body p code,
        .markdown-body li code {
            background: #f1f3f5;
            color: #d63384;
            padding: 0.2rem 0.4rem;
            border-radius: 6px;
            font-size: 0.9em;
        }

        .share-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .btn-share {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s;
        }

        .btn-share:hover {
            transform: translateY(-3px);
            color: white;
        }

        .bg-facebook {
            background: #1877F2;
        }

        .bg-twitter {
            background: #1DA1F2;
        }

        .bg-line {
            background: #00B900;
        }

        .bg-copy {
            background: #6c757d;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            color: white;
            font-size: 0.85rem;
            text-decoration: none;
            margin-right: 8px;
            margin-bottom: 8px;
            transition: opacity 0.3s;
        }

        .tag-pill:hover {
            opacity: 0.8;
            color: white;
        }

        .related-posts-section {
            background: #fff;
            padding: 5rem 0;
            margin-top: 4rem;
        }

        .blog-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .blog-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }

        .blog-img-wrapper {
            height: 180px;
            overflow: hidden;
        }

        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .blog-title-card {
            font-weight: 700;
            font-size: 1.1rem;
            color: #111;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 1rem;
        }

        .blog-title-card:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <i class="fas fa-layer-group text-primary fs-3 me-2"></i>
                <span style="color: var(--primary-color); font-size: 1.5rem;">CMMS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fw-medium">
                    <li class="nav-item"><a class="nav-link px-3" href="index.php">หน้าหลัก</a></li>
                    <li class="nav-item"><a class="nav-link px-3 active" href="blog.php"
                            style="color: var(--primary-color);">บทความ</a></li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="btn btn-primary rounded-pill px-4 ms-2" href="index_dashboard.php">Dashboard</a>
                        <?php else: ?>
                            <a class="btn btn-outline-primary rounded-pill px-4 ms-2" href="login.php">เข้าสู่ระบบ</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="post-header" data-aos="fade-down">
            <?php if (!empty($tags)): ?>
                <div class="mb-3">
                    <?php foreach ($tags as $t): ?>
                        <a href="blog.php?tag=<?= $t['id'] ?>" class="tag-pill bg-<?= $t['color_code'] ?>">
                            <i class="fas fa-hashtag me-1"></i>
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1 class="post-title">
                <?= htmlspecialchars($post['title']) ?>
            </h1>

            <div class="post-meta">
                <div class="d-flex align-items-center">
                    <div
                        class="author-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="fw-bold text-dark">
                        <?= htmlspecialchars($post['author_name']) ?>
                    </span>
                </div>
                <span><i class="far fa-calendar-alt me-1"></i>
                    <?= date('d M Y H:i', strtotime($post['published_at'])) ?>
                </span>
                <span><i class="far fa-clock me-1"></i>
                    <?= $post['read_time'] ?> นาที
                </span>
                <span><i class="far fa-eye me-1"></i>
                    <?= number_format($post['views']) ?> ครั้ง
                </span>
            </div>
        </div>

        <?php if (!empty($post['cover_image'])): ?>
            <img src="<?= BASE_URL ?>/uploads/blog/<?= htmlspecialchars($post['cover_image']) ?>" class="post-cover"
                data-aos="zoom-in" alt="Cover Image">
        <?php endif; ?>

        <div class="post-content-wrapper markdown-body" data-aos="fade-up">
            <textarea id="rawMarkdown" style="display:none;"><?= htmlspecialchars($post['content']) ?></textarea>
            <div id="htmlContent"></div>

            <!-- Social Share -->
            <div class="share-container">
                <span class="fw-bold me-2">แชร์บทความนี้:</span>
                <?php $currentUrl = BASE_URL . "/blog_detail.php?slug=" . urlencode($post['slug']); ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank"
                    class="btn-share bg-facebook" title="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode($post['title']) ?>"
                    target="_blank" class="btn-share bg-twitter" title="Share on Twitter"><i
                        class="fab fa-twitter"></i></a>
                <a href="https://social-plugins.line.me/lineit/share?url=<?= urlencode($currentUrl) ?>" target="_blank"
                    class="btn-share bg-line" title="Share on Line"><i class="fab fa-line"></i></a>
                <a href="#" onclick="copyUrl(event, '<?= $currentUrl ?>')" class="btn-share bg-copy"
                    title="Copy Link"><i class="fas fa-link"></i></a>
            </div>
        </div>
    </div>

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
        <section class="related-posts-section">
            <div class="container">
                <h3 class="fw-bold mb-4 border-start border-4 border-primary ps-3" data-aos="fade-right">
                    บทความที่เปิดอ่านบ่อยคล้ายกัน</h3>
                <div class="row g-4">
                    <?php foreach ($relatedPosts as $index => $rp): ?>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <a href="blog_detail.php?slug=<?= htmlspecialchars($rp['slug']) ?>" class="text-decoration-none">
                                <div class="blog-card">
                                    <div class="blog-img-wrapper">
                                        <?php if (!empty($rp['cover_image'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/blog/<?= htmlspecialchars($rp['cover_image']) ?>"
                                                class="blog-img">
                                        <?php else: ?>
                                            <div class="blog-img d-flex align-items-center justify-content-center text-muted"
                                                style="background:#e9ecef;"><i class="fas fa-image fs-1"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-3">
                                        <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>
                                            <?= date('d M Y', strtotime($rp['published_at'])) ?>
                                        </small>
                                        <h5 class="blog-title-card">
                                            <?= htmlspecialchars($rp['title']) ?>
                                        </h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-auto">
        <div class="container text-center">
            <h5 class="fw-bold mb-3"><i class="fas fa-layer-group text-primary me-2"></i>CMMS</h5>
            <p class="text-white-50 mb-0">&copy;
                <?= date('Y') ?> Computerized Maintenance Management System. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Marked.js for parsing markdown in frontend -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- DOMPurify to prevent XSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <!-- Highlight.js for Syntax Highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>

    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });

        document.addEventListener("DOMContentLoaded", function () {
            // Render Markdown
            const rawMd = document.getElementById('rawMarkdown').value;
            const html = marked.parse(rawMd);
            const cleanHtml = DOMPurify.sanitize(html);
            document.getElementById('htmlContent').innerHTML = cleanHtml;

            // Highlight Code Blocks
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        });

        function copyUrl(e, url) {
            e.preventDefault();
            navigator.clipboard.writeText(url).then(() => {
                alert('คัดลอกลิงก์สำเร็จแล้ว!');
            });
        }
    </script>
</body>

</html>