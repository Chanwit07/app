<?php
/**
 * Form Asset - ขอรหัส/ยูนิตสินทรัพย์
 */
$pageTitle = 'ขอรหัส/ยูนิตสินทรัพย์';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center animate-fadeInUp">
    <div class="col-lg-8">
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-building"></i>
                แบบฟอร์มขอรหัส/ยูนิตสินทรัพย์
            </div>

            <form id="assetForm" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- หน่วยงาน -->
                    <div class="col-md-6">
                        <label class="form-label">หน่วยงาน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="department" required placeholder="เช่น แผนกบัญชี">
                    </div>

                    <!-- เลขที่สินทรัพย์ -->
                    <div class="col-md-6">
                        <label class="form-label">เลขที่สินทรัพย์ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="asset_id" required placeholder="เช่น A-2025-001">
                    </div>

                    <!-- กลุ่มสินทรัพย์ -->
                    <div class="col-md-6">
                        <label class="form-label">กลุ่มสินทรัพย์/ยูนิต <span class="text-danger">*</span></label>
                        <select class="form-select" name="asset_group" required>
                            <option value="">-- เลือกกลุ่มสินทรัพย์ --</option>
                            <option value="คอมพิวเตอร์และอุปกรณ์">คอมพิวเตอร์และอุปกรณ์</option>
                            <option value="เฟอร์นิเจอร์และสิ่งติดตั้ง">เฟอร์นิเจอร์และสิ่งติดตั้ง</option>
                            <option value="เครื่องใช้สำนักงาน">เครื่องใช้สำนักงาน</option>
                            <option value="ยานพาหนะ">ยานพาหนะ</option>
                            <option value="เครื่องจักรและอุปกรณ์">เครื่องจักรและอุปกรณ์</option>
                            <option value="อาคารและสิ่งก่อสร้าง">อาคารและสิ่งก่อสร้าง</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>

                    <!-- Serial Number -->
                    <div class="col-md-6">
                        <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="serial_number" required
                            placeholder="เช่น SN-ABC123456">
                    </div>

                    <!-- ประเภทบัญชี -->
                    <div class="col-md-6">
                        <label class="form-label">ประเภทบัญชี <span class="text-danger">*</span></label>
                        <select class="form-select" name="account_type" required>
                            <option value="">-- เลือกประเภทบัญชี --</option>
                            <option value="ครุภัณฑ์">ครุภัณฑ์</option>
                            <option value="สินทรัพย์ถาวร">สินทรัพย์ถาวร</option>
                            <option value="วัสดุคงทน">วัสดุคงทน</option>
                            <option value="วัสดุสิ้นเปลือง">วัสดุสิ้นเปลือง</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="col-12">
                        <label class="form-label">แนบรูปภาพ (ถ้ามี)</label>
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt d-block"></i>
                            <p>คลิกหรือลากไฟล์มาวางที่นี่</p>
                            <small class="text-muted">รองรับ JPG, PNG, GIF, PDF (ไม่เกิน 5MB)</small>
                            <input type="file" id="fileInput" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                                style="display:none">
                            <img id="imagePreview" class="upload-preview d-none" src="" alt="Preview">
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary-gradient btn-lg w-100" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>ส่งคำขอ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
// Image preview
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('imagePreview');
const uploadZone = document.getElementById('uploadZone');

fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

// Drag & Drop
uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => { uploadZone.classList.remove('dragover'); });
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change'));
    }
});

// Form submit
document.getElementById('assetForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังส่ง...';

    const formData = new FormData(this);
    
    fetch('actions/save_asset.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                confirmButtonColor: '#667eea',
                customClass: { popup: 'font-prompt' }
            }).then(() => {
                document.getElementById('assetForm').reset();
                preview.classList.add('d-none');
            });
        } else {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, customClass: { popup: 'font-prompt' } });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', customClass: { popup: 'font-prompt' } });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>ส่งคำขอ';
    });
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>