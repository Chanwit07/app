<?php
/**
 * Auth System - Authentication & Authorization
 */
require_once __DIR__ . '/config.php';

/**
 * Check if user is logged in, redirect to login if not
 */
function checkAuth()
{
    if (!isset($_SESSION['user_id'])) {
        if (!headers_sent()) {
            header('Location: ' . BASE_URL . '/login.php');
        } else {
            echo '<script>window.location.href="' . BASE_URL . '/login.php";</script>';
        }
        exit;
    }
}

/**
 * Check if user has required role
 * @param string|array $requiredRoles - single role or array of roles
 */
function checkRole($requiredRoles)
{
    checkAuth();

    if (is_string($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }

    // super_admin has access to everything
    if ($_SESSION['role'] === 'super_admin') {
        return true;
    }

    if (!in_array($_SESSION['role'], $requiredRoles)) {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
        }
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>403</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head><body class="d-flex align-items-center justify-content-center min-vh-100 bg-dark text-white">
        <div class="text-center"><h1 class="display-1">403</h1><p class="lead">คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>
        <a href="dashboard.php" class="btn btn-outline-light mt-3">กลับหน้าหลัก</a></div></body></html>';
        exit;
    }

    return true;
}

/**
 * Verify login credentials
 * @param string $username
 * @param string $password
 * @return array|false
 */
function loginUser($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT id, username, password_hash, fullname, role, status, department FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();
    $updateStmt->close();

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['department'] = $user['department'] ?? '';

    return $user;
}

/**
 * Check if current user is admin or super_admin
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin']);
}

/**
 * Check if current user is super admin
 * @return bool
 */
function isSuperAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

/**
 * Get current user's display name
 * @return string
 */
function currentUserName()
{
    return $_SESSION['fullname'] ?? 'Guest';
}

/**
 * Get role display name in Thai
 * @param string $role
 * @return string
 */
function roleLabel($role)
{
    $labels = [
        'user' => 'ผู้ใช้งาน',
        'admin' => 'เจ้าหน้าที่',
        'super_admin' => 'ผู้ดูแลระบบ'
    ];
    return $labels[$role] ?? $role;
}

/**
 * Get role badge HTML
 * @param string $role
 * @return string
 */
function roleBadge($role)
{
    $badges = [
        'user' => '<span class="badge bg-secondary"><i class="fas fa-user me-1"></i>ผู้ใช้งาน</span>',
        'admin' => '<span class="badge bg-primary"><i class="fas fa-user-shield me-1"></i>เจ้าหน้าที่</span>',
        'super_admin' => '<span class="badge bg-danger"><i class="fas fa-crown me-1"></i>ผู้ดูแลระบบ</span>'
    ];
    return $badges[$role] ?? '<span class="badge bg-dark">ไม่ทราบ</span>';
}
