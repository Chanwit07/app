<?php
/**
 * Shared Header - Navigation & Head
 */
require_once __DIR__ . '/../auth.php';
checkAuth();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?? 'Maintenance Insight Platform' ?>
    </title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            important: true,
            corePlugins: { preflight: false }
        }
    </script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Font - Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <?php if (isset($extraCss))
        echo $extraCss; ?>
</head>

<body class="app-body">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-logo">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">MIP</span>
                    <span class="brand-sub">Maintenance Insight</span>
                </div>
            </div>
            <button class="sidebar-close d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">เมนูหลัก</span>

                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/dashboard.php"
                        class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>แดชบอร์ด</span>
                    </a>
                    <a href="<?= BASE_URL ?>/kanban.php" class="nav-link <?= $currentPage === 'kanban' ? 'active' : '' ?>">
                        <i class="fas fa-columns"></i>
                        <span>Kanban Board</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>หน้าหลัก</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">แบบฟอร์ม</span>
                <a href="<?= BASE_URL ?>/form-asset.php"
                    class="nav-link <?= $currentPage === 'form-asset' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i>
                    <span>ขอรหัสสินทรัพย์</span>
                </a>
                <a href="<?= BASE_URL ?>/form-supply.php"
                    class="nav-link <?= $currentPage === 'form-supply' ? 'active' : '' ?>">
                    <i class="fas fa-box-open"></i>
                    <span>ขอรหัสพัสดุ</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">ติดตาม & ประวัติ</span>
                <a href="<?= BASE_URL ?>/tracking.php"
                    class="nav-link <?= $currentPage === 'tracking' ? 'active' : '' ?>">
                    <i class="fas fa-search-location"></i>
                    <span>ติดตามสถานะ</span>
                </a>
                <a href="<?= BASE_URL ?>/history.php"
                    class="nav-link <?= $currentPage === 'history' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i>
                    <span>ประวัติการดำเนินการ</span>
                </a>
            </div>

            <?php if (isSuperAdmin()): ?>
                <div class="nav-section">
                    <span class="nav-section-title">จัดการระบบ</span>
                    <a href="<?= BASE_URL ?>/admin/manage_users.php"
                        class="nav-link <?= $currentPage === 'manage_users' ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>จัดการผู้ใช้</span>
                    </a>
                </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['fullname'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name">
                        <?= sanitize($_SESSION['fullname'] ?? '') ?>
                    </span>
                    <span class="user-role">
                        <?= roleLabel($_SESSION['role'] ?? 'user') ?>
                    </span>
                </div>
                <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" title="ออกจากระบบ">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn-menu d-lg-none me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <h4 class="mb-0">
                        <?= $pageTitle ?? 'หน้าหลัก' ?>
                    </h4>
                    <small class="text-muted">
                        <?= thaiDate(null, 'full') ?>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?= roleBadge($_SESSION['role'] ?? 'user') ?>
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle p-0" type="button"
                        data-bs-toggle="dropdown">
                        <span class="d-none d-md-inline text-dark">
                            <?= sanitize($_SESSION['fullname'] ?? '') ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">
                                <?= sanitize($_SESSION['username'] ?? '') ?>
                            </span></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i
                                    class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content Container -->
        <div class="content-wrapper">