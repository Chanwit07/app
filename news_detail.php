<?php
/**
 * News Detail Page — หน้าอ่านข่าวประชาสัมพันธ์ฉบับเต็ม
 */
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'includes/header_frontend.php';

$slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php#news");
    exit;
}

// Fetch news
$sql = "SELECT * FROM landing_news WHERE slug = '$slug'";
$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 ไม่พบข่าว</h1><a href='index.php'>กลับหน้าแรก</a>";
    exit;
}

$news = $res->fetch_assoc();

// Only show active news (unless admin)
if ($news['status'] !== 'active' && !isAdmin()) {
    header("Location: index.php#news");
    exit;
}

// Thai date
$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$d = new DateTime($news['publish_date']);
$thaiDate = $d->format('d') . ' ' . $months[(int) $d->format('m')] . ' ' . ($d->format('Y') + 543);

// Related news
$relatedNews = [];
$relSql = "SELECT * FROM landing_news WHERE status = 'active' AND id != {$news['id']} ORDER BY publish_date DESC LIMIT 3";
$relRes = $conn->query($relSql);
if ($relRes) {
    while ($r = $relRes->fetch_assoc()) {
        $relatedNews[] = $r;
    }
}

$pageTitle = htmlspecialchars($news['title']) . ' - ข่าวประชาสัมพันธ์';
$metaDescription = htmlspecialchars($news['excerpt']);
$metaImage = !empty($news['cover_image']) ? BASE_URL . '/uploads/news/' . htmlspecialchars($news['cover_image']) : BASE_URL . '/assets/img/default-share.jpg';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?>
    </title>
    <meta name="description" content="<?= $metaDescription ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= BASE_URL ?>/news_detail.php?slug=<?= $news['slug'] ?>">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $metaDescription ?>">
    <meta property="og:image" content="<?= $metaImage ?>">

    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">

    <style>
        :root {
            --primary: #1a237e;
            --primary-light: #3949ab;
            --secondary: #e53935;
            --accent: #ffd600;
            --bg: #f5f5f5;
            --text: #333;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.8;
            padding-top: 80px;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #0d1137 0%, #1a237e 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .3);
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            color: #fff !important;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, .8) !important;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            color: #fff !important;
        }

        .news-header {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            padding: 3rem 1rem 2rem;
        }

        .news-category-badge {
            display: inline-block;
            background: var(--secondary);
            color: #fff;
            padding: .3rem 1rem;
            border-radius: 20px;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .news-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #111;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }

        .news-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .news-cover {
            width: 100%;
            max-width: 1000px;
            max-height: 500px;
            object-fit: cover;
            border-radius: 20px;
            margin: 0 auto 3rem;
            display: block;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .news-content-wrapper {
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
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }

        .markdown-body a:hover {
            text-decoration: underline;
        }

        .markdown-body blockquote {
            border-left: 5px solid var(--primary);
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

        /* Excerpt fallback (no markdown content) */
        .news-excerpt-full {
            font-size: 1.2rem;
            line-height: 2;
            color: var(--text);
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

        .related-section {
            background: #fff;
            padding: 5rem 0;
            margin-top: 4rem;
        }

        .related-card {
            background: var(--bg);
            border-radius: 16px;
            overflow: hidden;
            transition: all .3s;
            border: 1px solid rgba(0, 0, 0, .04);
            height: 100%;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, .1);
        }

        .related-card .related-img {
            height: 160px;
            background: linear-gradient(135deg, var(--primary), #1565c0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .3);
            font-size: 3rem;
            overflow: hidden;
        }

        .related-card .related-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-card .related-body {
            padding: 1.2rem;
        }

        .related-card .related-title {
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-card .related-title:hover {
            color: var(--primary);
        }

        .btn-login-pill {
            background: var(--secondary);
            color: #fff !important;
            border: none;
            padding: .4rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div
                    style="width:38px;height:38px;background:linear-gradient(135deg,#e53935,#ff7043);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-train text-white"></i>
                </div>
                <div>
                    <div style="line-height:1.2;font-size:1rem">ระบบบริหารงานช่างกล รฟท.</div>
                    <small style="font-size:.7rem;opacity:.7">การรถไฟแห่งประเทศไทย</small>
                </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fw-medium align-items-center gap-1">
                    <li class="nav-item"><a class="nav-link px-3" href="index.php">หน้าหลัก</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="index.php#news"
                            style="color:var(--accent)!important">ข่าวสาร</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="blog.php">บทความ</a></li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="btn btn-login-pill ms-2" href="index_dashboard.php">Dashboard</a>
                        <?php else: ?>
                            <a class="btn btn-login-pill ms-2" href="login.php"><i
                                    class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบ</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="news-header" data-aos="fade-down">
            <span class="news-category-badge">
                <i class="fas fa-tag me-1"></i>
                <?= htmlspecialchars($news['category']) ?>
            </span>
            <h1 class="news-title">
                <?= htmlspecialchars($news['title']) ?>
            </h1>
            <div class="news-meta">
                <span><i class="far fa-calendar-alt me-1"></i>
                    <?= $thaiDate ?>
                </span>
                <span><i class="fas fa-building me-1"></i>ฝ่ายการช่างกล</span>
            </div>
        </div>

        <?php if (!empty($news['cover_image'])): ?>
            <img src="<?= BASE_URL ?>/uploads/news/<?= htmlspecialchars($news['cover_image']) ?>" class="news-cover"
                data-aos="zoom-in" alt="Cover Image">
        <?php endif; ?>

        <div class="news-content-wrapper markdown-body" data-aos="fade-up">
            <?php if (!empty($news['content'])): ?>
                <textarea id="rawMarkdown" style="display:none;"><?= htmlspecialchars($news['content']) ?></textarea>
                <div id="htmlContent"></div>
            <?php else: ?>
                <div class="news-excerpt-full">
                    <?= nl2br(htmlspecialchars($news['excerpt'])) ?>
                </div>
            <?php endif; ?>

            <!-- Social Share -->
            <div class="share-container">
                <span class="fw-bold me-2">แชร์ข่าวนี้:</span>
                <?php $currentUrl = BASE_URL . "/news_detail.php?slug=" . urlencode($news['slug']); ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank"
                    class="btn-share bg-facebook" title="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode($news['title']) ?>"
                    target="_blank" class="btn-share bg-twitter" title="Share on Twitter"><i
                        class="fab fa-twitter"></i></a>
                <a href="https://social-plugins.line.me/lineit/share?url=<?= urlencode($currentUrl) ?>" target="_blank"
                    class="btn-share bg-line" title="Share on Line"><i class="fab fa-line"></i></a>
                <a href="#" onclick="copyUrl(event, '<?= $currentUrl ?>')" class="btn-share bg-copy"
                    title="Copy Link"><i class="fas fa-link"></i></a>
            </div>
        </div>
    </div>

    <!-- Related News -->
    <?php if (!empty($relatedNews)): ?>
        <section class="related-section">
            <div class="container">
                <h3 class="fw-bold mb-4 border-start border-4 border-danger ps-3" data-aos="fade-right">
                    ข่าวอื่นๆ ที่เกี่ยวข้อง</h3>
                <div class="row g-4">
                    <?php foreach ($relatedNews as $index => $rn): ?>
                        <?php
                        $rnSlug = !empty($rn['slug']) ? $rn['slug'] : 'news-' . $rn['id'];
                        ?>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <a href="news_detail.php?slug=<?= htmlspecialchars($rnSlug) ?>" class="text-decoration-none">
                                <div class="related-card">
                                    <div class="related-img">
                                        <?php if (!empty($rn['cover_image'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/news/<?= htmlspecialchars($rn['cover_image']) ?>">
                                        <?php else: ?>
                                            <i class="fas fa-newspaper"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="related-body">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?= date('d/m/Y', strtotime($rn['publish_date'])) ?>
                                            <span class="ms-2 badge bg-primary">
                                                <?= htmlspecialchars($rn['category']) ?>
                                            </span>
                                        </small>
                                        <h6 class="related-title mt-2">
                                            <?= htmlspecialchars($rn['title']) ?>
                                        </h6>
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
            <h5 class="fw-bold mb-3"><i class="fas fa-train text-danger me-2"></i>ฝ่ายการช่างกล</h5>
            <p class="text-white-50 mb-0">&copy;
                <?= date('Y') + 543 ?> ฝ่ายการช่างกล การรถไฟแห่งประเทศไทย สงวนลิขสิทธิ์
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>

    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });

        document.addEventListener("DOMContentLoaded", function () {
            const rawEl = document.getElementById('rawMarkdown');
            if (rawEl) {
                const rawMd = rawEl.value;
                const html = marked.parse(rawMd);
                const cleanHtml = DOMPurify.sanitize(html);
                document.getElementById('htmlContent').innerHTML = cleanHtml;
                // Highlight Code Blocks
                document.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                });
            }
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