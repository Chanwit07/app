<?php
/**
 * Inventory Public Hub (User-facing)
 * Displays assets with filters
 */
$pageTitle = 'ทะเบียนทรัพย์สิน (ส่วนกลาง)';
require_once __DIR__ . '/includes/header.php';

// Safe variables for filter
$search = sanitize($_GET['search'] ?? '');
$type = sanitize($_GET['type'] ?? '');
$dept = sanitize($_GET['dept'] ?? '');

// Build Query
$sql = "SELECT * FROM inventory_assets WHERE status != 'written_off' ";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (asset_code LIKE ? OR asset_name LIKE ?) ";
    $term = "%{$search}%";
    $params[] = $term; $params[] = $term;
    $types .= "ss";
}
if ($type && in_array($type, ['fixed_asset', 'container', 'computer'])) {
    $sql .= " AND asset_type = ? ";
    $params[] = $type;
    $types .= "s";
}
if ($dept) {
    $sql .= " AND (install_department = ? OR install_section = ?) ";
    $params[] = $dept; $params[] = $dept;
    $types .= "ss";
}

$sql .= " ORDER BY created_at DESC LIMIT 100"; // Limit for performance

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$all_depts = [];

while ($row = $res->fetch_assoc()) {
    $items[] = $row;
    // Collect unique departments for dropdown
    if ($row['install_department'] && !in_array($row['install_department'], $all_depts)) {
        $all_depts[] = $row['install_department'];
    }
    if ($row['install_section'] && !in_array($row['install_section'], $all_depts)) {
        $all_depts[] = $row['install_section'];
    }
}
$stmt->close();
sort($all_depts);

function renderAssetBadge($type) {
    $map = [
        'fixed_asset' => ['color' => 'primary', 'icon' => 'building', 'label' => 'สินทรัพย์'],
        'container'   => ['color' => 'warning', 'icon' => 'box-open', 'label' => 'ภาชนะ'],
        'computer'    => ['color' => 'info', 'icon' => 'laptop', 'label' => 'คอมพิวเตอร์']
    ];
    $m = $map[$type] ?? $map['fixed_asset'];
    return "<span class=\"badge bg-{$m['color']} bg-opacity-10 text-{$m['color']} border border-{$m['color']} border-opacity-25\">
            <i class=\"fas fa-{$m['icon']} me-1\"></i>{$m['label']}</span>";
}
?>

<style>
    .inventory-hero {
        background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .inventory-hero * { position: relative; z-index: 1; }
    .inventory-hero::after {
        content: '\f466'; /* boxes-stacked */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        bottom: -20%;
        right: 5%;
        font-size: 10rem;
        opacity: 0.1;
        z-index: 0;
        transform: rotate(-15deg);
    }
    
    .inv-card {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }
    .inv-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(31, 64, 55, 0.1);
        border-color: rgba(31, 64, 55, 0.2);
    }
    .inv-img-wrap {
        height: 180px;
        background: #f8f9fa;
        border-radius: 16px 16px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .inv-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
    }
    .inv-img-wrap i {
        font-size: 3rem;
        color: #dee2e6;
    }
    
    .filter-panel {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 2rem;
    }
</style>

<div class="animate-fadeInUp">

    <!-- Hero -->
    <div class="inventory-hero">
        <h2 class="fw-bold"><i class="fas fa-boxes-stacked me-2"></i>ระบบสืบค้นทะเบียนทรัพย์สิน</h2>
        <p class="mb-0 text-white-50">ค้นหาและดูข้อมูลสินทรัพย์ถาวร ภาชนะถาวร และอุปกรณ์คอมพิวเตอร์ของหน่วยงาน</p>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <form method="GET" action="inventory.php" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold text-muted small text-uppercase">ค้นหาจากรหัส หรือ ชื่อ</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="รหัสสินทรัพย์ / ชื่อรายการ...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small text-uppercase">ประเภท</label>
                <select class="form-select" name="type">
                    <option value="">-- หาทุกประเภท --</option>
                    <option value="fixed_asset" <?= $type === 'fixed_asset' ? 'selected' : '' ?>>สินทรัพย์ถาวร</option>
                    <option value="container" <?= $type === 'container' ? 'selected' : '' ?>>ภาชนะถาวร</option>
                    <option value="computer" <?= $type === 'computer' ? 'selected' : '' ?>>อุปกรณ์คอมพิวเตอร์</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small text-uppercase">จุดติดตั้ง / หน่วยงาน</label>
                <input type="text" class="form-control" name="dept" value="<?= htmlspecialchars($dept) ?>" placeholder="ระบุหน่วยงาน/แผนก..." list="deptList">
                <datalist id="deptList">
                    <?php foreach($all_depts as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn w-100 text-white" style="background-color: #1f4037;">
                    ค้นหา
                </button>
            </div>
        </form>
        <?php if($search || $type || $dept): ?>
            <div class="mt-3 fs-7">
                <a href="inventory.php" class="text-decoration-none text-danger"><i class="fas fa-times-circle me-1"></i>ล้างการค้นหา</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Results Grid -->
    <div class="row g-4">
        <?php if (empty($items)): ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3"><i class="fas fa-search fa-4x"></i></div>
                <h5 class="text-muted">ไม่พบรายการสินทรัพย์ที่ค้นหา</h5>
            </div>
        <?php else: ?>
            <div class="col-12 mb-2">
                <p class="text-muted"><i class="fas fa-list-ul me-2"></i>พบข้อมูล <strong><?= count($items) ?></strong> รายการ</p>
            </div>
            
            <?php foreach ($items as $item): ?>
                <div class="col-sm-6 col-md-4 col-xl-3">
                    <div class="inv-card" onclick='showAssetDetail(<?= json_encode($item) ?>)'>
                        <div class="inv-img-wrap border-bottom">
                            <?php if ($item['image_path']): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($item['image_path']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-box-open"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex-grow-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fw-bold font-monospace text-secondary"><?= sanitize($item['asset_code']) ?></span>
                                <?= renderAssetBadge($item['asset_type']) ?>
                            </div>
                            
                            <h6 class="fw-bold text-dark mb-1" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= sanitize($item['asset_name']) ?></h6>
                            
                            <!-- Location -->
                            <div class="mt-auto pt-3">
                                <?php if($item['install_department'] || $item['install_section']): ?>
                                    <div class="small text-muted mb-1"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= sanitize($item['install_department'].' '.$item['install_section']) ?></div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                                    <div class="small text-muted">จำนวน: <strong class="text-dark"><?= $item['quantity'] ?></strong> <?= sanitize($item['unit']) ?></div>
                                    <?php if($item['status'] === 'broken'): ?>
                                        <span class="badge bg-danger">ชำรุด</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 pb-0 position-absolute w-100" style="z-index: 10;">
                <button type="button" class="btn-close bg-white shadow-sm p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-4 border-end" style="min-height:300px;">
                        <img id="detailImg" src="" class="img-fluid rounded shadow-sm d-none" style="object-fit:contain; max-height:300px;">
                        <i id="detailNoImg" class="fas fa-image fa-5x text-muted opacity-25"></i>
                    </div>
                    <div class="col-md-7 p-4">
                        <div id="detailBadge" class="mb-2"></div>
                        <h4 class="fw-bold mb-1" id="detailName">...</h4>
                        <div class="text-muted font-monospace mb-4" id="detailCode">...</div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <small class="text-muted d-block text-uppercase">จำนวนทั้งหมด</small>
                                <span class="fw-semibold" id="detailQty">...</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block text-uppercase">หน่วยงานที่รับผิดชอบ</small>
                                <span class="fw-semibold" id="detailResp">...</span>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block text-uppercase">จุดติดตั้ง / ใช้งาน</small>
                                <span class="fw-semibold"><i class="fas fa-map-marker-alt text-danger me-1"></i><span id="detailLoc">...</span></span>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block text-uppercase">วันที่ได้มา</small>
                                <span class="fw-semibold" id="detailDate">...</span>
                            </div>
                        </div>
                        
                        <!-- Computer Extra -->
                        <div id="compExtraDiv" class="p-3 bg-info bg-opacity-10 rounded border border-info border-opacity-25 d-none">
                            <h6 class="text-info fw-bold mb-2"><i class="fas fa-laptop me-2"></i>ข้อมูลระบบคอมพิวเตอร์</h6>
                            <div class="row g-2 text-sm">
                                <div class="col-6"><span class="text-muted">ประเภท:</span> <span id="cType" class="fw-semibold"></span></div>
                                <div class="col-6"><span class="text-muted">การรับประกัน:</span> <span id="cWar" class="fw-semibold"></span></div>
                                <div class="col-6"><span class="text-muted">ยี่ห้อ:</span> <span id="cBrand" class="fw-semibold"></span></div>
                                <div class="col-6"><span class="text-muted">รุ่น:</span> <span id="cModel" class="fw-semibold"></span></div>
                                <div class="col-12"><span class="text-muted">S/N:</span> <span id="cSn" class="fw-semibold font-monospace"></span></div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showAssetDetail(data) {
    // Basic Details
    document.getElementById('detailName').textContent = data.asset_name;
    document.getElementById('detailCode').textContent = data.asset_code;
    document.getElementById('detailQty').textContent = `${data.quantity} ${data.unit}`;
    document.getElementById('detailResp').textContent = data.responsible_dept || '-';
    
    let loc = [];
    if(data.install_department) loc.push(data.install_department);
    if(data.install_section) loc.push(data.install_section);
    document.getElementById('detailLoc').textContent = loc.join(' - ') || '-';
    
    // Dates
    if(data.acquisition_date) {
        // basic JS format approximation, ideally passed via PHP pre-formatted
        const arr = data.acquisition_date.split('-');
        document.getElementById('detailDate').textContent = `${arr[2]}/${arr[1]}/${parseInt(arr[0])+543}`;
    } else {
        document.getElementById('detailDate').textContent = '-';
    }
    
    // Image
    const imgEl = document.getElementById('detailImg');
    const noImgEl = document.getElementById('detailNoImg');
    if(data.image_path) {
        imgEl.src = '<?= BASE_URL ?>/uploads/' + data.image_path;
        imgEl.classList.remove('d-none');
        noImgEl.classList.add('d-none');
    } else {
        imgEl.addClass('d-none');
        noImgEl.classList.remove('d-none');
    }
    
    // Badges
    const badgeMap = {
        'fixed_asset': '<span class="badge bg-primary rounded-pill px-3 py-2"><i class="fas fa-building me-1"></i>สินทรัพย์ถาวร</span>',
        'container': '<span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="fas fa-box-open me-1"></i>ภาชนะถาวร</span>',
        'computer': '<span class="badge bg-info text-white rounded-pill px-3 py-2"><i class="fas fa-laptop me-1"></i>อุปกรณ์คอมพิวเตอร์</span>'
    };
    document.getElementById('detailBadge').innerHTML = badgeMap[data.asset_type] || '';
    
    // Computer details
    const compDiv = document.getElementById('compExtraDiv');
    if(data.asset_type === 'computer') {
        compDiv.classList.remove('d-none');
        document.getElementById('cType').textContent = data.computer_type === 'rent' ? 'เครื่องเช่า' : 'เครื่องของหน่วยงาน (งทป.)';
        document.getElementById('cBrand').textContent = data.brand || '-';
        document.getElementById('cModel').textContent = data.model || '-';
        document.getElementById('cSn').textContent = data.serial_number || '-';
        
        let warText = '-';
        if(data.warranty_years && data.acquisition_date) {
            const startDate = new Date(data.acquisition_date);
            const expDate = new Date(startDate);
            expDate.setFullYear(startDate.getFullYear() + parseInt(data.warranty_years));
            const today = new Date();
            if(today > expDate) {
                warText = 'หมดประกันแล้ว';
            } else {
                let diffTime = Math.abs(expDate - today);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                warText = `เหลืออีกประมาณ ${diffDays} วัน (${data.warranty_years} ปี)`;
            }
        }
        document.getElementById('cWar').textContent = warText;
        if(warText === 'หมดประกันแล้ว') document.getElementById('cWar').className = 'text-danger fw-bold';
        else document.getElementById('cWar').className = 'text-success fw-bold';
        
    } else {
        compDiv.classList.add('d-none');
    }
    
    new bootstrap.Modal(document.getElementById('detailModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
