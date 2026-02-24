<?php
/**
 * Landing Page - ระบบบริหารงานช่างกล รฟท.
 * SRT Mechanical Engineering CMMS Platform
 */
require_once __DIR__ . '/config.php';

$config = [
    'site_name' => 'ระบบบริหารงานช่างกล รฟท.',
    'division' => 'ฝ่ายการช่างกล',
    'org' => 'การรถไฟแห่งประเทศไทย',
    'org_en' => 'State Railway of Thailand',
    'version' => '1.0.0',
    'login_url' => 'login.php',
    'dashboard' => 'dashboard.php',
];

// Fetch news from database
$news = [];
$newsRes = $conn->query("SELECT * FROM landing_news WHERE status = 'active' ORDER BY publish_date DESC LIMIT 6");
if ($newsRes) {
    while ($row = $newsRes->fetch_assoc()) {
        // Convert date for Thai display
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $d = new DateTime($row['publish_date']);
        $row['date'] = $d->format('d') . ' ' . $months[(int) $d->format('m')] . ' ' . ($d->format('Y') + 543);
        $news[] = $row;
    }
}

$features = [
    ['icon' => 'fa-clipboard-list', 'title' => 'Work Order Management', 'desc' => 'สร้างและติดตามใบสั่งซ่อมแบบ Real-time'],
    ['icon' => 'fa-calendar-check', 'title' => 'Preventive Maintenance', 'desc' => 'วางแผนซ่อมบำรุงเชิงป้องกันตามรอบ'],
    ['icon' => 'fa-boxes-stacked', 'title' => 'Spare Parts Inventory', 'desc' => 'บริหารคลังอะไหล่และอุปกรณ์'],
    ['icon' => 'fa-chart-line', 'title' => 'KPI Dashboard', 'desc' => 'ติดตาม KPI และสถิติแบบ Real-time'],
    ['icon' => 'fa-bell', 'title' => 'Notification & Alerts', 'desc' => 'แจ้งเตือนงานซ่อมเร่งด่วนอัตโนมัติ'],
    ['icon' => 'fa-file-lines', 'title' => 'Report Generation', 'desc' => 'ออกรายงานอัตโนมัติ พร้อมส่งออก'],
];

// Fetch divisions from database
$divisions = [];
$divRes = $conn->query("SELECT * FROM landing_divisions WHERE status = 'active' ORDER BY sort_order ASC");
if ($divRes) {
    while ($row = $divRes->fetch_assoc()) {
        $divisions[] = [
            'icon' => $row['icon'],
            'name' => $row['name'],
            'loc' => $row['location'],
            'desc' => $row['description'],
        ];
    }
}

// Fetch latest blog posts
$blogPosts = [];
$blogRes = $conn->query("SELECT p.*, u.username as author_name 
    FROM blog_posts p 
    LEFT JOIN users u ON p.author_id = u.id 
    WHERE p.status = 'published' 
    ORDER BY p.published_at DESC LIMIT 6");
if ($blogRes) {
    while ($row = $blogRes->fetch_assoc()) {
        // Fetch first tag
        $pid = $row['id'];
        $tagRes2 = $conn->query("SELECT t.name, t.color_code FROM blog_post_tags pt JOIN blog_tags t ON pt.tag_id = t.id WHERE pt.post_id = $pid LIMIT 1");
        $firstTag = $tagRes2 ? $tagRes2->fetch_assoc() : null;
        $row['primary_tag'] = $firstTag ? $firstTag : ['name' => 'ทั่วไป', 'color_code' => 'secondary'];
        $blogPosts[] = $row;
    }
}

$thaiYear = date('Y') + 543;
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['site_name'] ?></title>
    <meta name="description"
        content="ระบบบริหารจัดการงานซ่อมบำรุงรถจักรและล้อเลื่อน ฝ่ายการช่างกล การรถไฟแห่งประเทศไทย">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --primary-light: #283593;
            --secondary: #e53935;
            --accent: #ffd600;
            --bg: #f5f5f5;
            --dark: #0d1137;
            --text: #333;
            --text-light: #666
        }

        * {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            background: var(--bg);
            color: var(--text)
        }

        /* Navbar */
        .navbar-srt {
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
            padding: .8rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .3)
        }

        .navbar-srt .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #fff !important;
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .navbar-srt .navbar-brand .logo-box {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--secondary), #ff7043);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            font-weight: 700
        }

        .navbar-srt .nav-link {
            color: rgba(255, 255, 255, .8) !important;
            font-weight: 500;
            padding: .5rem 1rem !important;
            border-radius: 8px;
            transition: all .3s
        }

        .navbar-srt .nav-link:hover,
        .navbar-srt .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, .12)
        }

        .btn-login {
            background: var(--secondary);
            color: #fff !important;
            border: none;
            padding: .5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all .3s
        }

        .btn-login:hover {
            background: #c62828;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(229, 57, 53, .4)
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, rgba(13, 17, 55, 0.82) 0%, rgba(26, 35, 126, 0.78) 50%, rgba(21, 101, 192, 0.75) 100%),
                url('assets/images/hero-bg-train.png') center/cover no-repeat;
            min-height: 85vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 214, 0, .08) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(229, 57, 53, .06) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            border-radius: 50%
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .15);
            padding: .4rem 1rem;
            border-radius: 50px;
            font-size: .85rem;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 1.5rem
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 1rem
        }

        .hero h1 span {
            color: var(--accent)
        }

        .hero .lead {
            color: rgba(255, 255, 255, .85);
            font-size: 1.15rem;
            line-height: 1.8;
            max-width: 600px
        }

        .btn-cta {
            padding: .75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all .3s
        }

        .btn-cta-primary {
            background: var(--secondary);
            color: #fff;
            border: none
        }

        .btn-cta-primary:hover {
            background: #c62828;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(229, 57, 53, .4)
        }

        .btn-cta-outline {
            border: 2px solid rgba(255, 255, 255, .5);
            color: #fff;
            background: transparent
        }

        .btn-cta-outline:hover {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border-color: #fff
        }

        .hero-visual {
            position: relative;
            z-index: 1
        }

        .hero-visual .floating-card {
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 16px;
            padding: 1.2rem;
            color: #fff;
            position: absolute;
            animation: float 6s ease-in-out infinite
        }

        .hero-visual .floating-card:nth-child(1) {
            top: 10%;
            right: 0;
            animation-delay: 0s
        }

        .hero-visual .floating-card:nth-child(2) {
            top: 45%;
            left: 5%;
            animation-delay: 2s
        }

        .hero-visual .floating-card:nth-child(3) {
            bottom: 5%;
            right: 10%;
            animation-delay: 4s
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-15px)
            }
        }

        /* Stats */
        .stats-section {
            margin-top: -60px;
            position: relative;
            z-index: 10
        }

        .stat-card-landing {
            background: #fff;
            border-radius: 16px;
            padding: 1.8rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .08);
            transition: all .3s;
            border: 1px solid rgba(0, 0, 0, .04)
        }

        .stat-card-landing:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .12)
        }

        .stat-card-landing .icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #fff
        }

        .stat-card-landing .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary)
        }

        .stat-card-landing .stat-label {
            color: var(--text-light);
            font-size: .9rem;
            margin-top: .3rem
        }

        /* Section Styles */
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: .5rem
        }

        .section-subtitle {
            color: var(--text-light);
            font-size: 1.05rem;
            max-width: 600px
        }

        .section-divider {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
            border-radius: 2px;
            margin: 1rem 0 2rem
        }

        /* About */
        .about-section {
            padding: 5rem 0;
            background: #fff
        }

        .responsibility-card {
            background: var(--bg);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all .3s;
            border: 1px solid transparent
        }

        .responsibility-card:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 30px rgba(26, 35, 126, .1);
            transform: translateY(-4px)
        }

        .responsibility-card .rc-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: 1rem
        }

        /* Features */
        .features-section {
            padding: 5rem 0;
            background: linear-gradient(180deg, var(--bg) 0%, #e8eaf6 100%)
        }

        .feature-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all .4s;
            border: 1px solid rgba(0, 0, 0, .04);
            height: 100%
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(26, 35, 126, .12);
            border-color: var(--primary)
        }

        .feature-card .fc-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
            font-size: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-light))
        }

        .feature-card:hover .fc-icon {
            background: linear-gradient(135deg, var(--secondary), #ff7043);
            transform: scale(1.1)
        }

        .feature-card h5 {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: .5rem
        }

        /* Divisions */
        .divisions-section {
            padding: 5rem 0;
            background: #fff
        }

        .division-card {
            background: var(--bg);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all .3s;
            border: 2px solid transparent;
            height: 100%
        }

        .division-card:hover {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 8px 30px rgba(26, 35, 126, .08)
        }

        .division-card .dc-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #3949ab);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 1rem
        }

        /* News */
        .news-section {
            padding: 5rem 0;
            background: linear-gradient(180deg, #e8eaf6 0%, var(--bg) 100%)
        }

        .news-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            transition: all .3s;
            border: 1px solid rgba(0, 0, 0, .04);
            height: 100%
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, .1)
        }

        .news-card .news-img {
            height: 180px;
            background: linear-gradient(135deg, var(--primary), #1565c0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .3);
            font-size: 3rem
        }

        .news-card .news-body {
            padding: 1.5rem
        }

        .news-card .news-category {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: .2rem .7rem;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .8rem
        }

        .news-card .news-date {
            color: var(--text-light);
            font-size: .85rem
        }

        /* Blog */
        .blog-section {
            padding: 5rem 0;
            background: #fff
        }

        .blog-card-landing {
            background: var(--bg);
            border-radius: 16px;
            overflow: hidden;
            transition: all .3s;
            border: 1px solid rgba(0, 0, 0, .04);
            height: 100%
        }

        .blog-card-landing:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, .1);
            border-color: var(--primary)
        }

        .blog-card-landing .blog-img-wrap {
            height: 180px;
            background: linear-gradient(135deg, #e8eaf6, #c5cae9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(26, 35, 126, .3);
            font-size: 3rem;
            overflow: hidden
        }

        .blog-card-landing .blog-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .blog-card-landing .blog-body {
            padding: 1.5rem
        }

        .blog-card-landing .blog-tag-badge {
            display: inline-block;
            padding: .2rem .7rem;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: .8rem
        }

        .blog-card-landing .blog-title-link {
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: .5rem;
            font-size: 1.05rem;
            line-height: 1.5
        }

        .blog-card-landing .blog-title-link:hover {
            color: var(--primary)
        }

        .blog-card-landing .blog-meta-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-light);
            font-size: .85rem;
            margin-top: auto
        }

        /* Contact */
        .contact-section {
            padding: 5rem 0;
            background: linear-gradient(180deg, var(--bg) 0%, #fff 100%)
        }

        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem
        }

        .contact-info-item .ci-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #3949ab);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem
        }

        .contact-form .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: .7rem 1rem;
            transition: all .3s
        }

        .contact-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, .1)
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #3949ab);
            color: #fff;
            border: none;
            padding: .75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all .3s
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, .3);
            color: #fff
        }

        /* Footer */
        .footer-srt {
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
            color: rgba(255, 255, 255, .8);
            padding: 3rem 0 1.5rem
        }

        .footer-srt h6 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 1rem
        }

        .footer-srt a {
            color: rgba(255, 255, 255, .7);
            text-decoration: none;
            transition: color .3s;
            display: block;
            margin-bottom: .4rem;
            font-size: .9rem
        }

        .footer-srt a:hover {
            color: var(--accent)
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .1);
            padding-top: 1.5rem;
            margin-top: 2rem
        }

        /* Utility */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary), #1565c0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent
        }

        @media(max-width:768px) {
            .hero h1 {
                font-size: 2rem
            }

            .hero {
                min-height: 70vh;
                padding-top: 4rem
            }

            .stats-section {
                margin-top: -30px
            }
        }
    </style>
</head>

<body>

    <!-- =============== NAVBAR =============== -->
    <nav class="navbar navbar-expand-lg navbar-srt fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <div class="logo-box"><i class="fas fa-train"></i></div>
                <div>
                    <div style="line-height:1.2"><?= $config['site_name'] ?></div><small
                        style="font-size:.7rem;opacity:.7"><?= $config['org'] ?></small>
                </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><i
                    class="fas fa-bars text-white"></i></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item"><a class="nav-link active" href="#home">หน้าหลัก</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">เกี่ยวกับ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">ฟีเจอร์</a></li>
                    <li class="nav-item"><a class="nav-link" href="#news">ข่าวสาร</a></li>
                    <li class="nav-item"><a class="nav-link" href="#blog">บทความ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">ติดต่อ</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-login" href="<?= $config['login_url'] ?>"><i
                                class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- =============== HERO =============== -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="hero-badge"><i class="fas fa-shield-halved"></i> <?= $config['org_en'] ?> |
                        <?= $config['division'] ?>
                    </div>
                    <h1>ระบบบริหารจัดการ<br><span>งานซ่อมบำรุง</span></h1>
                    <p class="lead mb-4">แพลตฟอร์มกลางสำหรับวางแผน ติดตาม และรายงานผลการซ่อมบำรุงรถจักรและล้อเลื่อน
                        เพื่อความปลอดภัยและความพร้อมใช้งานของขบวนรถทั่วประเทศ</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= $config['login_url'] ?>" class="btn btn-cta btn-cta-primary"><i
                                class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</a>
                        <a href="#features" class="btn btn-cta btn-cta-outline"><i
                                class="fas fa-eye me-2"></i>ดูภาพรวมระบบ</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block" data-aos="fade-left">
                    <div class="hero-visual" style="height:400px;position:relative">
                        <div class="floating-card"><i class="fas fa-wrench me-2"></i>Preventive Maintenance<br><small
                                style="opacity:.7">ซ่อมบำรุงเชิงป้องกัน</small></div>
                        <div class="floating-card"><i class="fas fa-chart-pie me-2"></i>KPI: 98.5%<br><small
                                style="opacity:.7">ความพร้อมใช้งาน</small></div>
                        <div class="floating-card"><i class="fas fa-bell me-2"></i>12 แจ้งเตือนใหม่<br><small
                                style="opacity:.7">งานซ่อมเร่งด่วน</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =============== STATS BAR =============== -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <?php
                $stats = [
                    ['icon' => 'fa-train', 'num' => '500+', 'label' => 'รถจักรในความดูแล', 'color' => 'linear-gradient(135deg,#1a237e,#3949ab)'],
                    ['icon' => 'fa-wrench', 'num' => '10,000+', 'label' => 'งานซ่อมบำรุงต่อปี', 'color' => 'linear-gradient(135deg,#e53935,#ff7043)'],
                    ['icon' => 'fa-industry', 'num' => '20+', 'label' => 'ศูนย์ซ่อมบำรุงทั่วประเทศ', 'color' => 'linear-gradient(135deg,#ff8f00,#ffd600)'],
                    ['icon' => 'fa-hard-hat', 'num' => '1,000+', 'label' => 'ช่างเทคนิค', 'color' => 'linear-gradient(135deg,#2e7d32,#66bb6a)'],
                ];
                foreach ($stats as $i => $s): ?>
                    <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <div class="stat-card-landing">
                            <div class="icon-wrap" style="background:<?= $s['color'] ?>"><i
                                    class="fas <?= $s['icon'] ?>"></i></div>
                            <div class="stat-number"><?= $s['num'] ?></div>
                            <div class="stat-label"><?= $s['label'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- =============== ABOUT =============== -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title">เกี่ยวกับฝ่ายการช่างกล</h2>
                    <div class="section-divider"></div>
                    <p style="line-height:1.9;color:var(--text-light)">ฝ่ายการช่างกล การรถไฟแห่งประเทศไทย
                        มีภารกิจหลักในการบริหารจัดการ ซ่อมบำรุง และควบคุมคุณภาพรถจักรและล้อเลื่อนทั่วประเทศ
                        ครอบคลุมทั้ง:</p>
                    <ul style="list-style:none;padding:0;color:var(--text-light);line-height:2.2">
                        <li><i class="fas fa-check-circle text-success me-2"></i>การซ่อมบำรุงรถจักรดีเซลและรถดีเซลราง
                        </li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>การดูแลตู้โดยสาร ตู้สินค้า และรถพ่วง
                        </li>
                        <li><i
                                class="fas fa-check-circle text-success me-2"></i>การบริหารบุคลากรพนักงานขับรถและช่างเทคนิค
                        </li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>การพัฒนาระบบดิจิทัลในงานซ่อมบำรุง</li>
                    </ul>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-3">
                        <?php $rCards = [
                            ['icon' => 'fa-cogs', 'title' => 'ซ่อมบำรุงรถจักร', 'desc' => 'Locomotive Maintenance — ดูแลรถจักรให้พร้อมใช้งานตลอดเวลา', 'bg' => 'linear-gradient(135deg,#1a237e,#3949ab)'],
                            ['icon' => 'fa-train-subway', 'title' => 'ซ่อมบำรุงล้อเลื่อน', 'desc' => 'Rolling Stock Maintenance — ซ่อมบำรุงตู้โดยสาร ตู้สินค้า', 'bg' => 'linear-gradient(135deg,#e53935,#ff7043)'],
                            ['icon' => 'fa-tasks', 'title' => 'บริหารจัดการงาน', 'desc' => 'Work Order Management — ระบบจัดการใบสั่งซ่อม', 'bg' => 'linear-gradient(135deg,#ff8f00,#ffd600)'],
                        ];
                        foreach ($rCards as $rc): ?>
                            <div class="col-12">
                                <div class="responsibility-card">
                                    <div class="rc-icon" style="background:<?= $rc['bg'] ?>"><i
                                            class="fas <?= $rc['icon'] ?>"></i></div>
                                    <h6 style="font-weight:600"><?= $rc['title'] ?></h6>
                                    <p class="mb-0" style="font-size:.9rem;color:var(--text-light)"><?= $rc['desc'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =============== FEATURES =============== -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">ฟีเจอร์หลักของระบบ</h2>
                <p class="section-subtitle mx-auto">เครื่องมือครบวงจรสำหรับบริหารงานซ่อมบำรุงรถจักรและล้อเลื่อน</p>
                <div class="section-divider mx-auto"></div>
            </div>
            <div class="row g-4">
                <?php foreach ($features as $i => $f): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <div class="feature-card">
                            <div class="fc-icon"><i class="fas <?= $f['icon'] ?>"></i></div>
                            <h5><?= $f['title'] ?></h5>
                            <p class="text-muted mb-0"><?= $f['desc'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- =============== SUB-DIVISIONS =============== -->
    <section class="divisions-section" id="divisions">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">หน่วยงานในสังกัด</h2>
                <p class="section-subtitle mx-auto">ศูนย์และกองภายใต้ฝ่ายการช่างกล การรถไฟแห่งประเทศไทย</p>
                <div class="section-divider mx-auto"></div>
            </div>
            <div class="row g-4">
                <?php foreach ($divisions as $i => $d): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                        <div class="division-card">
                            <div class="dc-icon"><i class="fas <?= $d['icon'] ?>"></i></div>
                            <h6 style="font-weight:600"><?= $d['name'] ?></h6>
                            <small class="text-muted d-block mb-2"><i
                                    class="fas fa-map-marker-alt me-1"></i><?= $d['loc'] ?></small>
                            <p class="mb-0" style="font-size:.88rem;color:var(--text-light)"><?= $d['desc'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- =============== NEWS =============== -->
    <section class="news-section" id="news">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">ข่าวและประกาศ</h2>
                <p class="section-subtitle mx-auto">ข่าวสารล่าสุดจากฝ่ายการช่างกล การรถไฟแห่งประเทศไทย</p>
                <div class="section-divider mx-auto"></div>
            </div>
            <div class="row g-4">
                <?php foreach ($news as $i => $n):
                    $newsSlug = !empty($n['slug']) ? $n['slug'] : 'news-' . $n['id'];
                    ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 150 ?>">
                        <a href="news_detail.php?slug=<?= htmlspecialchars($newsSlug) ?>" class="text-decoration-none">
                            <div class="news-card">
                                <div class="news-img">
                                    <?php if (!empty($n['cover_image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/news/<?= htmlspecialchars($n['cover_image']) ?>"
                                            style="width:100%;height:100%;object-fit:cover">
                                    <?php else: ?>
                                        <i class="fas fa-newspaper"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="news-body">
                                    <span class="news-category"><?= $n['category'] ?></span>
                                    <h6 style="font-weight:600;margin-bottom:.5rem;color:var(--text)"><?= $n['title'] ?>
                                    </h6>
                                    <p class="text-muted mb-3" style="font-size:.9rem"><?= $n['excerpt'] ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="news-date"><i
                                                class="far fa-calendar-alt me-1"></i><?= $n['date'] ?></span>
                                        <span style="color:var(--secondary);font-weight:600;font-size:.9rem">อ่านต่อ <i
                                                class="fas fa-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- =============== BLOG/ARTICLES =============== -->
    <?php if (!empty($blogPosts)): ?>
        <section class="blog-section" id="blog">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">บทความและองค์ความรู้</h2>
                    <p class="section-subtitle mx-auto">บทความ เคล็ดลับ และองค์ความรู้จากทีมช่างกล รฟท.</p>
                    <div class="section-divider mx-auto"></div>
                </div>
                <div class="row g-4">
                    <?php foreach ($blogPosts as $i => $bp): ?>
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                            <a href="blog_detail.php?slug=<?= htmlspecialchars($bp['slug']) ?>" class="text-decoration-none">
                                <div class="blog-card-landing">
                                    <div class="blog-img-wrap">
                                        <?php if (!empty($bp['cover_image'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/blog/<?= htmlspecialchars($bp['cover_image']) ?>"
                                                alt="<?= htmlspecialchars($bp['title']) ?>">
                                        <?php else: ?>
                                            <i class="fas fa-file-alt"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="blog-body">
                                        <span class="blog-tag-badge bg-<?= $bp['primary_tag']['color_code'] ?>">
                                            <?= htmlspecialchars($bp['primary_tag']['name']) ?>
                                        </span>
                                        <h6 class="blog-title-link"><?= htmlspecialchars($bp['title']) ?></h6>
                                        <p class="text-muted mb-3"
                                            style="font-size:.9rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                            <?= htmlspecialchars($bp['excerpt'] ?? '') ?>
                                        </p>
                                        <div class="blog-meta-info">
                                            <span><i
                                                    class="far fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($bp['published_at'])) ?></span>
                                            <span><i class="far fa-clock me-1"></i><?= $bp['read_time'] ?> นาที</span>
                                            <span><i class="far fa-eye me-1"></i><?= number_format($bp['views']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="blog.php" class="btn btn-cta btn-cta-primary">
                        <i class="fas fa-newspaper me-2"></i>ดูบทความทั้งหมด
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- =============== CONTACT =============== -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">ติดต่อเรา</h2>
                <p class="section-subtitle mx-auto">ช่องทางติดต่อฝ่ายการช่างกล การรถไฟแห่งประเทศไทย</p>
                <div class="section-divider mx-auto"></div>
            </div>
            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="contact-info-item">
                        <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div><strong>ที่อยู่</strong>
                            <p class="mb-0 text-muted" style="font-size:.9rem">เลขที่ 1 ถนนรองเมือง แขวงรองเมือง
                                เขตปทุมวัน กรุงเทพมหานคร 10330</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon"><i class="fas fa-phone-alt"></i></div>
                        <div><strong>Call Center</strong>
                            <p class="mb-0 text-muted">1690</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon"><i class="fas fa-envelope"></i></div>
                        <div><strong>อีเมล</strong>
                            <p class="mb-0 text-muted">datawarehouse.srt@railway.co.th</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon"><i class="fas fa-globe"></i></div>
                        <div><strong>เว็บไซต์</strong>
                            <p class="mb-0"><a href="https://www.railway.co.th" target="_blank"
                                    style="color:var(--primary)">www.railway.co.th</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <form class="contact-form" action="#contact" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="ชื่อ-นามสกุล"
                                    required></div>
                            <div class="col-md-6"><input type="email" class="form-control" placeholder="อีเมล" required>
                            </div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="หัวข้อ" required>
                            </div>
                            <div class="col-12"><textarea class="form-control" rows="5" placeholder="ข้อความ"
                                    required></textarea></div>
                            <div class="col-12"><button type="submit" class="btn btn-submit w-100"><i
                                        class="fas fa-paper-plane me-2"></i>ส่งข้อความ</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- =============== FOOTER =============== -->
    <footer class="footer-srt">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div
                            style="width:40px;height:40px;background:linear-gradient(135deg,var(--secondary),#ff7043);border-radius:10px;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-train text-white"></i>
                        </div>
                        <div><strong style="color:#fff"><?= $config['division'] ?></strong><br><small
                                style="opacity:.7"><?= $config['org'] ?></small></div>
                    </div>
                    <p style="font-size:.85rem;opacity:.7;line-height:1.8">
                        ระบบบริหารจัดการงานซ่อมบำรุงรถจักรและล้อเลื่อน เพื่อเพิ่มประสิทธิภาพการวางแผน ติดตาม
                        และรายงานผลงานซ่อมบำรุง</p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h6>เมนูหลัก</h6>
                    <a href="#home">หน้าหลัก</a><a href="#about">เกี่ยวกับ</a><a href="#features">ฟีเจอร์</a><a
                        href="#news">ข่าวสาร</a><a href="#blog">บทความ</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>ระบบงาน</h6>
                    <a href="<?= $config['login_url'] ?>">เข้าสู่ระบบ</a><a href="#">แดชบอร์ด</a><a
                        href="#">งานซ่อมบำรุง</a><a href="#">รายงาน</a>
                </div>
                <div class="col-lg-2">
                    <h6>ติดต่อ</h6>
                    <a href="tel:1690"><i class="fas fa-phone-alt me-1"></i>1690</a>
                    <a href="https://www.railway.co.th" target="_blank"><i
                            class="fas fa-globe me-1"></i>www.railway.co.th</a>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <small>&copy; <?= $thaiYear ?> <?= $config['division'] ?> <?= $config['org'] ?> |
                    สงวนลิขสิทธิ์</small><br>
                <small style="opacity:.5">พัฒนาโดยทีมไอที <?= $config['division'] ?> |
                    v<?= $config['version'] ?></small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 100 });
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const nav = document.querySelector('.navbar-srt');
            nav.style.background = window.scrollY > 50 ? 'rgba(13,17,55,.98)' : '';
        });
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const t = document.querySelector(this.getAttribute('href'));
                if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>

</html>