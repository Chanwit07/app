<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'auth.php';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        $user = loginUser($conn, $username, $password);
        if ($user) {
            logAudit($conn, 'เข้าสู่ระบบ', 'users', $user['id'], json_encode(['username' => $username]));
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Maintenance Insight Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Prompt', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .brand-icon i {
            font-size: 2.2rem;
            color: white;
        }

        .login-title {
            color: #fff;
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 0.3rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            font-size: 0.85rem;
            margin-bottom: 2rem;
        }

        .form-floating>.form-control {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #fff;
            height: 56px;
            padding-left: 1rem;
        }

        .form-floating>.form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            color: #fff;
        }

        .form-floating>label {
            color: rgba(255, 255, 255, 0.5);
            padding-left: 1rem;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #667eea;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            height: 52px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            color: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.45);
            color: #fff;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .floating-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: #667eea;
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            background: #764ba2;
            bottom: -50px;
            right: -50px;
            animation-delay: 4s;
        }

        .orb-3 {
            width: 200px;
            height: 200px;
            background: #f093fb;
            top: 50%;
            left: 50%;
            animation-delay: 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(5deg);
            }
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            z-index: 5;
            cursor: pointer;
        }

        .password-wrapper {
            position: relative;
        }
    </style>
</head>

<body>
    <div class="floating-orb orb-1"></div>
    <div class="floating-orb orb-2"></div>
    <div class="floating-orb orb-3"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-icon">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <h1 class="login-title">Maintenance Insight</h1>
            <p class="login-subtitle">ระบบบริหารจัดการพัสดุและสินทรัพย์</p>

            <?php if ($error): ?>
                <div class="alert alert-custom d-flex align-items-center mb-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้"
                        required autofocus value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
                    <label for="username"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                </div>
                <div class="form-floating mb-4 password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน"
                        required>
                    <label for="password"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                    <span class="input-icon" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                </button>
            </form>

            <div class="text-center mt-4">
                <small style="color: rgba(255,255,255,0.3); font-size: 0.75rem;">
                    Maintenance Insight Platform v1.0 &copy;
                    <?= date('Y') + 543 ?>
                </small>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>