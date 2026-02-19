/**
 * Kanban Board - Interactivity & Status Updates
 */

/**
 * Show detail modal for a request
 */
function showDetail(item) {
    const type = item._type;
    const id = item.id;
    const status = item.status;
    
    let detailHtml = '';
    
    if (type === 'asset') {
        detailHtml = `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">หน่วยงาน</label>
                    <div class="fw-semibold">${escHtml(item.department)}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">เลขที่สินทรัพย์</label>
                    <div class="fw-semibold">${escHtml(item.asset_id)}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">กลุ่มสินทรัพย์</label>
                    <div class="fw-semibold">${escHtml(item.asset_group)}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Serial Number</label>
                    <div class="fw-semibold">${escHtml(item.serial_number)}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">ประเภทบัญชี</label>
                    <div class="fw-semibold">${escHtml(item.account_type)}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">ผู้ขอ</label>
                    <div class="fw-semibold">${escHtml(item.requester || 'N/A')}</div>
                </div>
            </div>
        `;
    } else {
        detailHtml = `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">ประเภทคำขอ</label>
                    <div class="fw-semibold">${item.request_type === 'new_code' ? 'ขอรหัสพัสดุใหม่' : 'แก้ไขรายละเอียดพัสดุ'}</div>
                </div>
                ${item.item_number ? `<div class="col-md-6"><label class="form-label text-muted small">เลขที่สิ่งของ</label><div class="fw-semibold">${escHtml(item.item_number)}</div></div>` : ''}
                <div class="col-12">
                    <label class="form-label text-muted small">ชื่อรายการ${item.new_item_name ? ' (เดิม)' : ''}</label>
                    <div class="fw-semibold">${escHtml(item.item_name)}</div>
                </div>
                ${item.new_item_name ? `<div class="col-12"><label class="form-label text-muted small">ชื่อใหม่</label><div class="fw-semibold text-info">${escHtml(item.new_item_name)}</div></div>` : ''}
                <div class="col-md-4">
                    <label class="form-label text-muted small">หน่วยนับ</label>
                    <div class="fw-semibold">${escHtml(item.unit)}</div>
                </div>
                ${item.annual_usage ? `<div class="col-md-4"><label class="form-label text-muted small">ปริมาณใช้/ปี</label><div class="fw-semibold">${item.annual_usage}</div></div>` : ''}
                ${item.max_min ? `<div class="col-md-4"><label class="form-label text-muted small">Max-Min</label><div class="fw-semibold">${escHtml(item.max_min)}</div></div>` : ''}
                <div class="col-md-6">
                    <label class="form-label text-muted small">ผู้ขอ</label>
                    <div class="fw-semibold">${escHtml(item.requester || 'N/A')}</div>
                </div>
            </div>
        `;
    }
    
    // Image
    if (item.image) {
        detailHtml += `<div class="mb-4"><label class="form-label text-muted small">รูปภาพแนบ</label><br><img src="uploads/${escHtml(item.image)}" class="img-fluid rounded" style="max-height: 200px;"></div>`;
    }
    
    // Admin note
    detailHtml += `
        <hr>
        <div class="mb-3">
            <label class="form-label fw-semibold"><i class="fas fa-sticky-note me-1 text-warning"></i>บันทึกหมายเหตุ (Internal Note)</label>
            <textarea class="form-control" id="adminNote" rows="3" placeholder="ระบุหมายเหตุสำหรับเจ้าหน้าที่...">${escHtml(item.admin_note || '')}</textarea>
        </div>
    `;
    
    // Status update buttons
    if (status !== 'Completed') {
        detailHtml += '<div class="d-flex gap-2 flex-wrap">';
        if (status === 'Pending') {
            detailHtml += `<button class="btn btn-info text-white" onclick="updateStatus('${type}', ${id}, 'Processing')"><i class="fas fa-play me-1"></i>เริ่มดำเนินการ</button>`;
        }
        if (status === 'Pending' || status === 'Processing') {
            detailHtml += `<button class="btn btn-success" onclick="updateStatus('${type}', ${id}, 'Completed')"><i class="fas fa-check me-1"></i>เสร็จสิ้น</button>`;
        }
        detailHtml += '</div>';
    } else {
        detailHtml += '<div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>คำขอนี้เสร็จสิ้นแล้ว</div>';
    }
    
    document.getElementById('modalBody').innerHTML = detailHtml;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
}

/**
 * Update request status via AJAX
 */
function updateStatus(type, id, newStatus) {
    const note = document.getElementById('adminNote')?.value || '';
    
    Swal.fire({
        title: 'ยืนยันการเปลี่ยนสถานะ?',
        text: `เปลี่ยนสถานะเป็น "${getStatusLabel(newStatus)}"`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'font-prompt' }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('id', id);
            formData.append('status', newStatus);
            formData.append('admin_note', note);
            
            fetch('actions/update_status.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        customClass: { popup: 'font-prompt' },
                        confirmButtonColor: '#667eea'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message, customClass: { popup: 'font-prompt' } });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อได้', customClass: { popup: 'font-prompt' } });
            });
        }
    });
}

function getStatusLabel(status) {
    const labels = { 'Pending': 'รอดำเนินการ', 'Processing': 'กำลังดำเนินการ', 'Completed': 'เสร็จสิ้น' };
    return labels[status] || status;
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ====================================================
// Completed cards hide/dismiss functionality
// Uses localStorage to persist hidden cards
// ====================================================

const STORAGE_KEY = 'kanban_hidden_completed';

function getHiddenCards() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch { return []; }
}

function saveHiddenCards(arr) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
}

/** Hide a single completed card */
function hideCompletedCard(cardKey, btnEl) {
    const card = btnEl.closest('.kanban-card');
    if (!card) return;

    // Animate out
    card.style.transition = 'all 0.3s ease';
    card.style.opacity = '0';
    card.style.transform = 'translateX(20px)';
    setTimeout(() => {
        card.style.display = 'none';
        updateCompletedCount();
    }, 300);

    // Save to localStorage
    const hidden = getHiddenCards();
    if (!hidden.includes(cardKey)) {
        hidden.push(cardKey);
        saveHiddenCards(hidden);
    }
}

/** Toggle hide/show ALL completed cards */
function toggleHideAllCompleted() {
    const container = document.getElementById('completedCards');
    const cards = container.querySelectorAll('.kanban-card');
    const label = document.getElementById('toggleHideLabel');
    const icon = document.getElementById('toggleHideAll').querySelector('i');

    // Check if any visible cards remain
    const visibleCards = Array.from(cards).filter(c => c.style.display !== 'none');

    if (visibleCards.length > 0) {
        // Hide all
        const hidden = getHiddenCards();
        cards.forEach(card => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => { card.style.display = 'none'; }, 300);
            const key = card.getAttribute('data-card-key');
            if (key && !hidden.includes(key)) hidden.push(key);
        });
        saveHiddenCards(hidden);
        label.textContent = 'แสดงทั้งหมด';
        icon.className = 'fas fa-eye me-1';
        setTimeout(updateCompletedCount, 350);
    } else {
        // Show all
        saveHiddenCards([]);
        cards.forEach(card => {
            card.style.display = '';
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateX(0)';
        });
        label.textContent = 'ซ่อนทั้งหมด';
        icon.className = 'fas fa-eye-slash me-1';
        updateCompletedCount();
    }
}

/** Update the completed column count badge */
function updateCompletedCount() {
    const container = document.getElementById('completedCards');
    if (!container) return;
    const visibleCount = Array.from(container.querySelectorAll('.kanban-card'))
        .filter(c => c.style.display !== 'none').length;
    const countEl = document.getElementById('completedCount');
    if (countEl) countEl.textContent = visibleCount;
}

/** On page load: restore hidden state from localStorage */
document.addEventListener('DOMContentLoaded', function() {
    const hidden = getHiddenCards();
    if (hidden.length === 0) return;

    const container = document.getElementById('completedCards');
    if (!container) return;

    hidden.forEach(key => {
        const card = container.querySelector(`[data-card-key="${key}"]`);
        if (card) card.style.display = 'none';
    });

    updateCompletedCount();

    // Update toggle button label
    const cards = container.querySelectorAll('.kanban-card');
    const allHidden = Array.from(cards).every(c => c.style.display === 'none');
    if (allHidden && cards.length > 0) {
        const label = document.getElementById('toggleHideLabel');
        const icon = document.getElementById('toggleHideAll')?.querySelector('i');
        if (label) label.textContent = 'แสดงทั้งหมด';
        if (icon) icon.className = 'fas fa-eye me-1';
    }
});

