<?php
// blog.php
session_start();
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Blog & Articles - อ่านบทความล่าสุด';
require_once 'includes/header_frontend.php'; // Using a frontend-specific header if it exists, otherwise header.php
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?> | CMMS
    </title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3949ab;
            --accent-color: #00bcd4;
            --bg-color: #f4f6fb;
            --text-color: #333;
            --card-bg: #fff;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            overflow-x: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 5rem 0 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.1;
        }

        /* Navbar overrides for landing */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .blog-card {
            background: var(--card-bg);
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .blog-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: #e9ecef;
        }

        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .blog-card:hover .blog-img {
            transform: scale(1.05);
        }

        .blog-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 2;
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .blog-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .blog-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            line-height: 1.4;
            color: var(--text-color);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-title:hover {
            color: var(--secondary-color);
        }

        .blog-excerpt {
            color: #555;
            font-size: 0.95rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .blog-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }

        .read-more {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            transition: gap 0.3s;
            gap: 5px;
        }

        .read-more:hover {
            gap: 10px;
            color: var(--primary-color);
        }

        .search-box {
            border-radius: 30px;
            padding: 1rem 1.5rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            font-size: 1.1rem;
        }

        .search-btn {
            border-radius: 0 30px 30px 0;
            padding: 0 2rem;
            background: var(--accent-color);
            border: none;
            color: white;
            font-weight: 600;
        }

        .tag-filter {
            background: white;
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .tag-pill {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            background: #f8f9fa;
            color: #555;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 0 5px;
            transition: all 0.3s;
            border: 1px solid #eee;
        }

        .tag-pill:hover,
        .tag-pill.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-custom .page-link {
            border-radius: 8px;
            margin: 0 5px;
            border: none;
            color: #555;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .pagination-custom .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 35, 126, 0.3);
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

    <!-- Hero Section -->
    <section class="hero-section text-center pt-5">
        <div class="container mt-5" data-aos="fade-up">
            <h1 class="display-4 fw-bold mb-3">คลังบทความและความรู้</h1>
            <p class="lead mb-5 opacity-75">อัปเดตข่าวสาร เทคนิคการใช้งาน และความรู้ใหม่ๆ จากทีมงาน</p>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form action="blog.php" method="GET" class="d-flex position-relative">
                        <input type="text" name="q" class="form-control search-box" placeholder="ค้นหาบทความ..."
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="btn search-btn position-absolute end-0 top-0 bottom-0"><i
                                class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Content Section -->
    <section class="py-5">
        <div class="container">

            <?php
            // Fetch Tags for Filter
            $activeTag = isset($_GET['tag']) ? (int) $_GET['tag'] : 0;
            $tagsRes = $conn->query("SELECT * FROM blog_tags ORDER BY name ASC");
            $tags = [];
            if ($tagsRes) {
                while ($r = $tagsRes->fetch_assoc()) {
                    $tags[] = $r;
                }
            }
            ?>

            <!-- Tags Filter -->
            <div class="text-center" data-aos="fade-up">
                <div class="tag-filter overflow-auto text-nowrap py-2" style="max-width: 100%;">
                    <a href="blog.php" class="tag-pill <?= $activeTag === 0 ? 'active' : '' ?>">ทั้งหมด</a>
                    <?php foreach ($tags as $t): ?>
                        <a href="blog.php?tag=<?= $t['id'] ?>"
                            class="tag-pill <?= $activeTag === (int) $t['id'] ? 'active' : '' ?>">
                            <i class="fas fa-hashtag me-1"></i>
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php
            // Pagination logic
            $limit = 6;
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            if ($page < 1)
                $page = 1;
            $offset = ($page - 1) * $limit;

            // Build query
            $q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
            $where = ["p.status = 'published'"];

            if (!empty($q)) {
                $where[] = "(p.title LIKE '%$q%' OR p.content LIKE '%$q%')";
            }

            if ($activeTag > 0) {
                $where[] = "p.id IN (SELECT post_id FROM blog_post_tags WHERE tag_id = $activeTag)";
            }

            $whereSql = "WHERE " . implode(' AND ', $where);

            // Count total
            $countQuery = "SELECT COUNT(p.id) as total FROM blog_posts p $whereSql";
            $countRes = $conn->query($countQuery);
            $totalRows = $countRes->fetch_assoc()['total'];
            $totalPages = ceil($totalRows / $limit);

            // Fetch posts
            $sql = "SELECT p.*, u.username as author_name 
                FROM blog_posts p 
                LEFT JOIN users u ON p.author_id = u.id 
                $whereSql 
                ORDER BY p.published_at DESC 
                LIMIT $offset, $limit";

            $posts = [];
            $res = $conn->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    // Fetch first tag for badge
                    $pid = $row['id'];
                    $tagRes = $conn->query("SELECT t.name, t.color_code FROM blog_post_tags pt JOIN blog_tags t ON pt.tag_id = t.id WHERE pt.post_id = $pid LIMIT 1");
                    $firstTag = $tagRes->fetch_assoc();
                    $row['primary_tag'] = $firstTag ? $firstTag : ['name' => 'ทั่วไป', 'color_code' => 'secondary'];
                    $posts[] = $row;
                }
            }
            ?>

            <?php if (empty($posts)): ?>
                <div class="text-center py-5 my-5" data-aos="fade-up">
                    <i class="fas fa-folder-open text-muted" style="font-size: 5rem; opacity: 0.5;"></i>
                    <h3 class="mt-4 fw-bold">ไม่พบบทความ</h3>
                    <p class="text-muted">ลองค้นหาด้วยคำอื่นๆ หรือเลือกหมวดหมู่อื่นดูสิ</p>
                    <a href="blog.php" class="btn btn-primary mt-2 rounded-pill px-4">ดูบทความทั้งหมด</a>
                </div>
            <?php else: ?>
                <div class="row g-4 mt-2">
                    <?php foreach ($posts as $index => $post): ?>
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <article class="blog-card">
                                <div class="blog-img-wrapper">
                                    <span class="blog-badge text-<?= $post['primary_tag']['color_code'] ?>">
                                        <i class="fas fa-circle me-1" style="font-size:8px; vertical-align:middle;"></i>
                                        <?= htmlspecialchars($post['primary_tag']['name']) ?>
                                    </span>
                                    <?php if (!empty($post['cover_image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/blog/<?= htmlspecialchars($post['cover_image']) ?>"
                                            class="blog-img" alt="<?= htmlspecialchars($post['title']) ?>">
                                    <?php else: ?>
                                        <div class="blog-img d-flex align-items-center justify-content-center text-muted"
                                            style="background:#e9ecef;">
                                            <i class="fas fa-image" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="blog-content">
                                    <div class="blog-meta">
                                        <span><i class="far fa-calendar-alt me-1"></i>
                                            <?= date('d M Y', strtotime($post['published_at'])) ?>
                                        </span>
                                        <span><i class="far fa-clock me-1"></i>
                                            <?= $post['read_time'] ?> นาที
                                        </span>
                                    </div>
                                    <a href="blog_detail.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="blog-title">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                    <p class="blog-excerpt">
                                        <?= htmlspecialchars($post['excerpt'] ?: strip_tags(mb_substr($post['content'], 0, 150))) ?>...
                                    </p>
                                    <div class="blog-footer">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                                                style="width:30px; height:30px; font-size:12px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <small class="fw-bold text-dark">
                                                <?= htmlspecialchars($post['author_name']) ?>
                                            </small>
                                        </div>
                                        <a href="blog_detail.php?slug=<?= htmlspecialchars($post['slug']) ?>"
                                            class="read-more">อ่านต่อ <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5 d-flex justify-content-center" data-aos="fade-up">
                        <ul class="pagination pagination-custom">
                            <?php
                            $qs = $_GET;
                            $prevPage = max(1, $page - 1);
                            $nextPage = min($totalPages, $page + 1);

                            // Previous button
                            $qs['page'] = $prevPage;
                            echo '<li class="page-item ' . ($page == 1 ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($qs) . '"><i class="fas fa-chevron-left"></i></a></li>';

                            // Page numbers
                            for ($i = 1; $i <= $totalPages; $i++) {
                                // Logic to show limited page numbers (e.g. 1 2 ... 5 6 7 ... 10) can be added here
                                // For simplicity, showing all pages up to a reasonable amount
                                if ($totalPages > 7) {
                                    if ($i == 1 || $i == $totalPages || ($i >= $page - 1 && $i <= $page + 1)) {
                                        $qs['page'] = $i;
                                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?' . http_build_query($qs) . '">' . $i . '</a></li>';
                                    } elseif ($i == 2 || $i == $totalPages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                } else {
                                    $qs['page'] = $i;
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?' . http_build_query($qs) . '">' . $i . '</a></li>';
                                }
                            }

                            // Next button
                            $qs['page'] = $nextPage;
                            echo '<li class="page-item ' . ($page == $totalPages ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($qs) . '"><i class="fas fa-chevron-right"></i></a></li>';
                            ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container text-center">
            <h5 class="fw-bold mb-3"><i class="fas fa-layer-group text-primary me-2"></i>CMMS</h5>
            <p class="text-white-50 mb-0">&copy;
                <?= date('Y') ?> Computerized Maintenance Management System. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    </script>
</body>

</html>