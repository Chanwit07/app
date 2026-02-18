<?php
/**
 * Logout - Destroy session and redirect
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user_id'])) {
    logAudit($conn, 'ออกจากระบบ', 'users', $_SESSION['user_id']);
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

header('Location: login.php');
exit;
