<?php
/**
 * Profile - โปรไฟล์ & เปลี่ยนรหัสผ่าน
 */
$pageTitle = 'โปรไฟล์ของฉัน';
require_once __DIR__ . '/includes/header.php';

// Load current user data
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, fullname, email, department, role, last_login, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Count user's requests
$assetCount = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests WHERE user_id = $userId")->fetch_assoc()['cnt'];
$supplyCount = $conn->query("SELECT COUNT(*) as cnt FROM supply_requests WHERE user_id = $userId")->fetch_assoc()['cnt'];
?>

<div class="row g-4 animate-fadeInUp">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card-glass p-4 text-center">
            <!-- Avatar -->
            <div class="mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%;
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                display: flex; align-items: center; justify-content: center;
                font-size: 2.5rem; font-weight: 700; color: white;
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                <?= strtoupper(mb_substr($user['fullname'], 0, 1)) ?>
            </div>

            <h5 class="fw-bold mb-1">
                <?= sanitize($user['fullname']) ?>
            </h5>
            <p class="text-muted mb-2">@
                <?= sanitize($user['username']) ?>
            </p>
            <?= roleBadge($user['role']) ?>

            <hr class="my-3">

            <!-- Stats -->
            <div class="row g-2 text-start">
                <div class="col-6">
                    <div class="p-2 rounded-3 text-center" style="background: rgba(102, 126, 234, 0.1);">
                        <div class="fw-bold text-primary" style="font-size: 1.5rem;">
                            <?= $assetCount ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">คำขอสินทรัพย์</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3 text-center" style="background: rgba(240, 147, 251, 0.1);">
                        <div class="fw-bold" style="font-size: 1.5rem; color: #f093fb;">
                            <?= $supplyCount ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">คำขอพัสดุ</div>
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <div class="text-start">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small"><i class="fas fa-envelope me-1"></i> อีเมล</span>
                    <span class="small fw-semibold">
                        <?= $user['email'] ? sanitize($user['email']) : '-' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small"><i class="fas fa-building me-1"></i> หน่วยงาน</span>
                    <span class="small fw-semibold">
                        <?= $user['department'] ? sanitize($user['department']) : '-' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small"><i class="fas fa-sign-in-alt me-1"></i> เข้าสู่ระบบล่าสุด</span>
                    <span class="small fw-semibold">
                        <?= $user['last_login'] ? thaiDate($user['last_login'], 'compact') : '-' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small"><i class="fas fa-calendar-plus me-1"></i> สมัครเมื่อ</span>
                    <span class="small fw-semibold">
                        <?= thaiDate($user['created_at'], 'compact') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="col-lg-8">
        <!-- Profile Form -->
        <div class="card-glass p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="stat-icon"
                    style="background: linear-gradient(135deg, #667eea, #764ba2); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-edit text-white" style="font-size: 1.1rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">แก้ไขข้อมูลส่วนตัว</h5>
                    <small class="text-muted">อัปเดตชื่อ อีเมล และหน่วยงาน</small>
                </div>
            </div>

            <form id="profileForm">
                <input type="hidden" name="action" value="update_profile">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-primary"></i> ชื่อผู้ใช้ (Username)
                        </label>
                        <input type="text" class="form-control" value="<?= sanitize($user['username']) ?>" disabled
                            style="background: #f0f0f0;">
                        <small class="text-muted">ไม่สามารถเปลี่ยนได้</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card me-1 text-primary"></i> ชื่อ-นามสกุล <span
                                class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="fullname" id="fullname"
                            value="<?= sanitize($user['fullname']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-info"></i> อีเมล
                        </label>
                        <input type="email" class="form-control" name="email" id="email"
                            value="<?= sanitize($user['email'] ?? '') ?>" placeholder="example@mail.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-building me-1 text-warning"></i> หน่วยงาน
                        </label>
                        <input type="text" class="form-control" name="department" id="department"
                            value="<?= sanitize($user['department'] ?? '') ?>" placeholder="ชื่อหน่วยงาน/แผนก">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="card-glass p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="stat-icon"
                    style="background: linear-gradient(135deg, #f093fb, #f5576c); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-key text-white" style="font-size: 1.1rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">เปลี่ยนรหัสผ่าน</h5>
                    <small class="text-muted">แนะนำให้ใช้รหัสผ่านที่ปลอดภัย</small>
                </div>
            </div>

            <form id="passwordForm">
                <input type="hidden" name="action" value="change_password">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-danger"></i> รหัสผ่านปัจจุบัน <span
                                class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" id="currentPassword"
                                required placeholder="กรอกรหัสผ่านปัจจุบัน">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePw('currentPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-success"></i> รหัสผ่านใหม่ <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="newPassword" required
                                minlength="4" placeholder="อย่างน้อย 4 ตัวอักษร">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePw('newPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-check-circle me-1 text-success"></i> ยืนยันรหัสผ่านใหม่ <span
                                class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword"
                                required placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePw('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn px-4 text-white"
                        style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-key me-2"></i>เปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
function togglePw(id) {
    var input = document.getElementById(id);
    var icon = input.parentElement.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Profile form submit
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    fetch('<?= BASE_URL ?>/actions/save_profile.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'สำเร็จ!' : 'ผิดพลาด',
            text: data.message,
            customClass: { popup: 'font-prompt' },
            confirmButtonColor: '#667eea'
        }).then(function() { if (data.success) location.reload(); });
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', customClass: { popup: 'font-prompt' } });
    });
});

// Password form submit
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var newPw = document.getElementById('newPassword').value;
    var confirmPw = document.getElementById('confirmPassword').value;

    if (newPw !== confirmPw) {
        Swal.fire({ icon: 'warning', title: 'รหัสผ่านไม่ตรงกัน', text: 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน', customClass: { popup: 'font-prompt' }, confirmButtonColor: '#667eea' });
        return;
    }

    if (newPw.length < 4) {
        Swal.fire({ icon: 'warning', title: 'รหัสผ่านสั้นเกินไป', text: 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 ตัวอักษร', customClass: { popup: 'font-prompt' }, confirmButtonColor: '#667eea' });
        return;
    }

    var formData = new FormData(this);

    Swal.fire({
        title: 'ยืนยันเปลี่ยนรหัสผ่าน?',
        text: 'คุณต้องการเปลี่ยนรหัสผ่านใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'font-prompt' }
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch('<?= BASE_URL ?>/actions/save_profile.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'สำเร็จ!' : 'ผิดพลาด',
                    text: data.message,
                    customClass: { popup: 'font-prompt' },
                    confirmButtonColor: '#667eea'
                });
                if (data.success) {
                    document.getElementById('passwordForm').reset();
                }
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', customClass: { popup: 'font-prompt' } });
            });
        }
    });
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>