<?php
/**
 * Manage Users - Super Admin Only
 */
$pageTitle = 'จัดการผู้ใช้งาน';
require_once __DIR__ . '/../includes/header.php';
checkRole(['super_admin']);

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = sanitize($_POST['username'] ?? '');
        $fullname = sanitize($_POST['fullname'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $department = sanitize($_POST['department'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($fullname) || empty($password)) {
            $message = 'กรุณากรอกข้อมูลให้ครบ';
            $messageType = 'danger';
        } else {
            // Check duplicate
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
                $messageType = 'danger';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, fullname, email, department, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $hash, $fullname, $email, $department, $role);
                if ($stmt->execute()) {
                    logAudit($conn, 'สร้างผู้ใช้ใหม่: ' . $username, 'users', $stmt->insert_id);
                    $message = 'สร้างผู้ใช้ใหม่สำเร็จ';
                    $messageType = 'success';
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    if ($action === 'toggle_status') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? 'active';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND id != ?");
        $myId = $_SESSION['user_id'];
        $stmt->bind_param("sii", $newStatus, $userId, $myId);
        $stmt->execute();
        $stmt->close();
        logAudit($conn, "เปลี่ยนสถานะผู้ใช้ #{$userId} เป็น {$newStatus}", 'users', $userId);
        $message = 'อัปเดตสถานะสำเร็จ';
        $messageType = 'success';
    }

    if ($action === 'reset_password') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';
        if (!empty($newPass)) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $userId);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, "รีเซ็ตรหัสผ่านผู้ใช้ #{$userId}", 'users', $userId);
            $message = 'รีเซ็ตรหัสผ่านสำเร็จ';
            $messageType = 'success';
        }
    }

    if ($action === 'update_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? 'user';
        if (in_array($newRole, ['user', 'admin', 'super_admin'])) {
            $myId = $_SESSION['user_id'];
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            $stmt->bind_param("sii", $newRole, $userId, $myId);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, "เปลี่ยน Role ผู้ใช้ #{$userId} เป็น {$newRole}", 'users', $userId);
            $message = 'เปลี่ยน Role สำเร็จ';
            $messageType = 'success';
        }
    }
}

// Fetch all users
$users = [];
$res = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
?>

<div class="animate-fadeInUp">
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="form-section mb-4">
        <div class="section-title">
            <i class="fas fa-user-plus"></i>
            เพิ่มผู้ใช้ใหม่
        </div>
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="create">
            <div class="col-md-3">
                <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" required placeholder="username">
            </div>
            <div class="col-md-3">
                <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="fullname" required placeholder="ชื่อ นามสกุล">
            </div>
            <div class="col-md-2">
                <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" required placeholder="********">
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                    <option value="user">ผู้ใช้งาน</option>
                    <option value="admin">เจ้าหน้าที่</option>
                    <option value="super_admin">ผู้ดูแลระบบ</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-gradient w-100">
                    <i class="fas fa-plus me-1"></i>เพิ่ม
                </button>
            </div>
            <div class="col-md-3">
                <label class="form-label">อีเมล</label>
                <input type="email" class="form-control" name="email" placeholder="email@example.com">
            </div>
            <div class="col-md-3">
                <label class="form-label">หน่วยงาน</label>
                <input type="text" class="form-control" name="department" placeholder="แผนก/ฝ่าย">
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="form-section">
        <div class="section-title">
            <i class="fas fa-users"></i>
            รายชื่อผู้ใช้ทั้งหมด (
            <?= count($users) ?> คน)
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>หน่วยงาน</th>
                        <th>Role</th>
                        <th>สถานะ</th>
                        <th>เข้าสู่ระบบล่าสุด</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <?= $u['id'] ?>
                            </td>
                            <td><code><?= sanitize($u['username']) ?></code></td>
                            <td>
                                <?= sanitize($u['fullname']) ?>
                            </td>
                            <td>
                                <?= sanitize($u['department'] ?? '-') ?>
                            </td>
                            <td>
                                <?= roleBadge($u['role']) ?>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td><small>
                                    <?= $u['last_login'] ? thaiDate($u['last_login'], 'compact') : 'ยังไม่เคย' ?>
                                </small></td>
                            <td>
                                <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                                    <div class="btn-group btn-group-sm">
                                        <!-- Toggle Status -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="new_status"
                                                value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                                            <button type="submit"
                                                class="btn btn-<?= $u['status'] === 'active' ? 'outline-danger' : 'outline-success' ?> btn-sm"
                                                title="<?= $u['status'] === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                                                <i class="fas fa-<?= $u['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Change Role -->
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="changeRole(<?= $u['id'] ?>, '<?= $u['role'] ?>')" title="เปลี่ยน Role">
                                            <i class="fas fa-user-tag"></i>
                                        </button>

                                        <!-- Reset Password -->
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            onclick="resetPassword(<?= $u['id'] ?>)" title="รีเซ็ตรหัสผ่าน">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted">ตัวเอง</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
function changeRole(userId, currentRole) {
    const roles = { user: 'ผู้ใช้งาน', admin: 'เจ้าหน้าที่', super_admin: 'ผู้ดูแลระบบ' };
    let options = {};
    Object.entries(roles).forEach(([key, label]) => {
        options[key] = label + (key === currentRole ? ' (ปัจจุบัน)' : '');
    });

    Swal.fire({
        title: 'เปลี่ยน Role',
        input: 'select',
        inputOptions: options,
        inputValue: currentRole,
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'font-prompt' }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="update_role"><input type="hidden" name="user_id" value="${userId}"><input type="hidden" name="new_role" value="${result.value}">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function resetPassword(userId) {
    Swal.fire({
        title: 'รีเซ็ตรหัสผ่าน',
        input: 'password',
        inputLabel: 'รหัสผ่านใหม่',
        inputPlaceholder: 'ระบุรหัสผ่านใหม่',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'รีเซ็ต',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'font-prompt' },
        inputValidator: (value) => { if (!value) return 'กรุณาระบุรหัสผ่าน'; }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="reset_password"><input type="hidden" name="user_id" value="${userId}"><input type="hidden" name="new_password" value="${result.value}">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
JS;

require_once __DIR__ . '/../includes/footer.php';
?>