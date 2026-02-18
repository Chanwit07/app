<?php
/**
 * Form Supply - ขอรหัสพัสดุใหม่ / แก้ไขรายละเอียดพัสดุ
 */
$pageTitle = 'ขอรหัสพัสดุ / แก้ไขรายละเอียด';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center animate-fadeInUp">
    <div class="col-lg-8">
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-box-open"></i>
                แบบฟอร์มขอรหัสพัสดุ / แก้ไขรายละเอียด
            </div>

            <!-- Tabs -->
            <ul class="nav nav-pills mb-4" role="tablist" style="gap: 0.5rem;">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="pill" href="#tabNewCode"
                        style="border-radius: 10px; font-size: 0.88rem;">
                        <i class="fas fa-plus-circle me-1"></i>ขอรหัสพัสดุใหม่
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#tabEditDetail"
                        style="border-radius: 10px; font-size: 0.88rem;">
                        <i class="fas fa-edit me-1"></i>แก้ไขรายละเอียดพัสดุ
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: New Code -->
                <div class="tab-pane fade show active" id="tabNewCode">
                    <form id="supplyNewForm" enctype="multipart/form-data">
                        <input type="hidden" name="request_type" value="new_code">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">ชื่อรายการพัสดุ <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="item_name" rows="3" required
                                    placeholder="ระบุชื่อรายการพัสดุที่ต้องการขอรหัส"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">หน่วยนับ <span class="text-danger">*</span></label>
                                <select class="form-select" name="unit" required>
                                    <option value="">-- เลือก --</option>
                                    <option value="ชิ้น">ชิ้น</option>
                                    <option value="อัน">อัน</option>
                                    <option value="เครื่อง">เครื่อง</option>
                                    <option value="ตัว">ตัว</option>
                                    <option value="ชุด">ชุด</option>
                                    <option value="กล่อง">กล่อง</option>
                                    <option value="ม้วน">ม้วน</option>
                                    <option value="แกลลอน">แกลลอน</option>
                                    <option value="ลิตร">ลิตร</option>
                                    <option value="กิโลกรัม">กิโลกรัม</option>
                                    <option value="เมตร">เมตร</option>
                                    <option value="แผ่น">แผ่น</option>
                                    <option value="ถุง">ถุง</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ปริมาณใช้ต่อปี</label>
                                <input type="number" class="form-control" name="annual_usage" min="0"
                                    placeholder="ไม่บังคับ">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max-Min</label>
                                <input type="text" class="form-control" name="max_min" placeholder="เช่น 100-50">
                            </div>

                            <!-- Image Upload -->
                            <div class="col-12">
                                <label class="form-label">แนบรูปภาพ (ถ้ามี)</label>
                                <div class="upload-zone" id="uploadZoneNew"
                                    onclick="document.getElementById('fileInputNew').click()">
                                    <i class="fas fa-cloud-upload-alt d-block"></i>
                                    <p>คลิกหรือลากไฟล์มาวางที่นี่</p>
                                    <small class="text-muted">รองรับ JPG, PNG, GIF, PDF (ไม่เกิน 5MB)</small>
                                    <input type="file" id="fileInputNew" name="image"
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" style="display:none">
                                    <img id="previewNew" class="upload-preview d-none" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-gradient btn-lg w-100" id="submitNewBtn">
                                    <i class="fas fa-paper-plane me-2"></i>ส่งคำขอรหัสพัสดุใหม่
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Edit Detail -->
                <div class="tab-pane fade" id="tabEditDetail">
                    <form id="supplyEditForm" enctype="multipart/form-data">
                        <input type="hidden" name="request_type" value="edit_detail">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">เลขที่สิ่งของ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="item_number" required
                                    placeholder="เช่น S-2025-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">หน่วยนับ <span class="text-danger">*</span></label>
                                <select class="form-select" name="unit" required>
                                    <option value="">-- เลือก --</option>
                                    <option value="ชิ้น">ชิ้น</option>
                                    <option value="อัน">อัน</option>
                                    <option value="เครื่อง">เครื่อง</option>
                                    <option value="ตัว">ตัว</option>
                                    <option value="ชุด">ชุด</option>
                                    <option value="กล่อง">กล่อง</option>
                                    <option value="ม้วน">ม้วน</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ชื่อเดิม <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="item_name" rows="2" required
                                    placeholder="ระบุชื่อเดิมของรายการ"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ชื่อใหม่ <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="new_item_name" rows="2" required
                                    placeholder="ระบุชื่อใหม่ที่ต้องการแก้ไข"></textarea>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-12">
                                <label class="form-label">แนบรูปภาพ (ถ้ามี)</label>
                                <div class="upload-zone" id="uploadZoneEdit"
                                    onclick="document.getElementById('fileInputEdit').click()">
                                    <i class="fas fa-cloud-upload-alt d-block"></i>
                                    <p>คลิกหรือลากไฟล์มาวางที่นี่</p>
                                    <small class="text-muted">รองรับ JPG, PNG, GIF, PDF (ไม่เกิน 5MB)</small>
                                    <input type="file" id="fileInputEdit" name="image"
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" style="display:none">
                                    <img id="previewEdit" class="upload-preview d-none" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-gradient btn-lg w-100" id="submitEditBtn">
                                    <i class="fas fa-paper-plane me-2"></i>ส่งคำขอแก้ไขรายละเอียด
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div><!-- /.tab-content -->
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
// Setup upload preview for both tabs
function setupUpload(inputId, previewId, zoneId) {
    const input = document.getElementById(inputId);
    const prev = document.getElementById(previewId);
    const zone = document.getElementById(zoneId);

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => { prev.src = e.target.result; prev.classList.remove('d-none'); };
            reader.readAsDataURL(file);
        }
    });

    zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault(); zone.classList.remove('dragover');
        if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
    });
}

setupUpload('fileInputNew', 'previewNew', 'uploadZoneNew');
setupUpload('fileInputEdit', 'previewEdit', 'uploadZoneEdit');

// Submit handler factory
function setupSubmit(formId, btnId) {
    document.getElementById(formId).addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById(btnId);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังส่ง...';

        fetch('actions/save_supply.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, confirmButtonColor: '#667eea', customClass: { popup: 'font-prompt' } })
                .then(() => { document.getElementById(formId).reset(); document.querySelectorAll('.upload-preview').forEach(p => p.classList.add('d-none')); });
            } else {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, customClass: { popup: 'font-prompt' } });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'ข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อได้', customClass: { popup: 'font-prompt' } }))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>' + (formId === 'supplyNewForm' ? 'ส่งคำขอรหัสพัสดุใหม่' : 'ส่งคำขอแก้ไขรายละเอียด');
        });
    });
}

setupSubmit('supplyNewForm', 'submitNewBtn');
setupSubmit('supplyEditForm', 'submitEditBtn');
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>