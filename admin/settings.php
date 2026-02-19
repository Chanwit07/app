<?php
/**
 * Admin Settings - Telegram Configuration
 */
$pageTitle = 'ตั้งค่าระบบ';
require_once __DIR__ . '/../includes/header.php';
checkRole(['admin', 'super_admin']);

// Load current settings
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM app_settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$botToken = $settings['telegram_bot_token'] ?? '';
$chatId = $settings['telegram_chat_id'] ?? '';
$enabled = ($settings['telegram_enabled'] ?? '0') === '1';
?>

<div class="row g-4 animate-fadeInUp">
    <div class="col-12">
        <div class="card-glass p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="stat-icon"
                    style="background: linear-gradient(135deg, #667eea, #764ba2); width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-cog text-white" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">ตั้งค่า Telegram Bot</h5>
                    <small class="text-muted">เชื่อมต่อ Telegram เพื่อรับแจ้งเตือนอัตโนมัติ</small>
                </div>
            </div>

            <!-- Telegram Setup Guide -->
            <div class="alert alert-info border-0 mb-4" style="border-radius: 12px;">
                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>วิธีสร้าง Telegram Bot</h6>
                <ol class="mb-0 small">
                    <li>ค้นหา <strong>@BotFather</strong> บน Telegram แล้วส่ง <code>/newbot</code></li>
                    <li>ตั้งชื่อ Bot ตามที่ต้องการ → จะได้รับ <strong>Bot Token</strong></li>
                    <li>เพิ่ม Bot เข้ากลุ่ม Telegram ที่ต้องการรับแจ้งเตือน</li>
                    <li>ส่งข้อความอะไรก็ได้ในกลุ่ม แล้วเปิดลิงก์:
                        <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>
                        เพื่อดู <strong>Chat ID</strong> (ตัวเลขที่เป็นลบ เช่น -1001234567890)
                    </li>
                </ol>
            </div>

            <form id="telegramForm">
                <!-- Enable/Disable Toggle -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="telegramEnabled" name="telegram_enabled"
                            <?= $enabled ? 'checked' : '' ?>
                        style="width: 3em; height: 1.5em; cursor: pointer;">
                        <label class="form-check-label fw-semibold ms-2" for="telegramEnabled" style="cursor: pointer;">
                            <span id="enableLabel">
                                <?= $enabled ? '✅ เปิดใช้งาน Telegram' : '⏸️ ปิดใช้งาน Telegram' ?>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Bot Token -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-primary"></i> Bot Token
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="botToken" name="telegram_bot_token"
                                value="<?= sanitize($botToken) ?>" placeholder="123456789:ABCdefGhIJKlmNOPqrs-TuvWxyZ">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()"
                                id="toggleTokenBtn">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Token ที่ได้จาก @BotFather</small>
                    </div>

                    <!-- Chat ID -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fab fa-telegram me-1 text-info"></i> Chat ID
                        </label>
                        <input type="text" class="form-control" id="chatId" name="telegram_chat_id"
                            value="<?= sanitize($chatId) ?>" placeholder="-1001234567890">
                        <small class="text-muted">Chat ID ของกลุ่ม (ตัวเลขที่เป็นลบ)</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="testTelegram()">
                        <i class="fas fa-paper-plane me-2"></i>ทดสอบส่งข้อความ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Card -->
    <div class="col-12">
        <div class="card-glass p-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-bell me-2 text-warning"></i>สถานะการแจ้งเตือน</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(102, 126, 234, 0.1);">
                        <div class="small text-muted">สถานะ Telegram</div>
                        <div class="fw-bold mt-1" id="telegramStatus">
                            <?php if ($enabled && !empty($botToken)): ?>
                                <span class="text-success"><i class="fas fa-circle me-1"
                                        style="font-size: 0.5rem;"></i>เชื่อมต่อแล้ว</span>
                            <?php elseif (!empty($botToken)): ?>
                                <span class="text-warning"><i class="fas fa-circle me-1"
                                        style="font-size: 0.5rem;"></i>ปิดใช้งาน</span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fas fa-circle me-1"
                                        style="font-size: 0.5rem;"></i>ยังไม่ได้ตั้งค่า</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(72, 187, 120, 0.1);">
                        <div class="small text-muted">การแจ้งเตือนในแอป (กระดิ่ง)</div>
                        <div class="fw-bold mt-1 text-success">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>เปิดใช้งานเสมอ
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(236, 201, 75, 0.1);">
                        <div class="small text-muted">แจ้งเตือนเมื่อ</div>
                        <div class="fw-bold mt-1">มีคำขอใหม่ / อัปเดตสถานะ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
function toggleTokenVisibility() {
    const input = document.getElementById('botToken');
    const btn = document.getElementById('toggleTokenBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

document.getElementById('telegramEnabled').addEventListener('change', function() {
    document.getElementById('enableLabel').textContent = 
        this.checked ? '✅ เปิดใช้งาน Telegram' : '⏸️ ปิดใช้งาน Telegram';
});

document.getElementById('telegramForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'save');
    // Ensure checkbox value is sent
    if (!document.getElementById('telegramEnabled').checked) {
        formData.delete('telegram_enabled');
    }

    fetch(BASE_URL + '/actions/save_settings.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'สำเร็จ!' : 'ผิดพลาด',
            text: data.message,
            customClass: { popup: 'font-prompt' },
            confirmButtonColor: '#667eea'
        }).then(() => { if (data.success) location.reload(); });
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อได้', customClass: { popup: 'font-prompt' } });
    });
});

function testTelegram() {
    const token = document.getElementById('botToken').value.trim();
    const chatId = document.getElementById('chatId').value.trim();

    if (!token || !chatId) {
        Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูล', text: 'ต้องกรอก Bot Token และ Chat ID ก่อนทดสอบ', customClass: { popup: 'font-prompt' }, confirmButtonColor: '#667eea' });
        return;
    }

    Swal.fire({ title: 'กำลังส่งข้อความทดสอบ...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'font-prompt' } });

    const formData = new FormData();
    formData.append('action', 'test');
    formData.append('telegram_bot_token', token);
    formData.append('telegram_chat_id', chatId);

    fetch(BASE_URL + '/actions/save_settings.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'สำเร็จ!' : 'ผิดพลาด',
            text: data.message,
            customClass: { popup: 'font-prompt' },
            confirmButtonColor: '#667eea'
        });
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', customClass: { popup: 'font-prompt' } });
    });
}

var BASE_URL = '<?= BASE_URL ?>';
</script>
JS;

require_once __DIR__ . '/../includes/footer.php';
?>