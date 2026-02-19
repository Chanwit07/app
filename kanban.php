<?php
/**
 * Kanban Board - Admin Status Management
 */
$pageTitle = 'Kanban Board';
require_once __DIR__ . '/includes/header.php';
checkRole(['admin', 'super_admin']);

// Fetch all requests
$assetRequests = [];
$supplyRequests = [];

$res = $conn->query("SELECT ar.*, u.fullname as requester FROM asset_requests ar LEFT JOIN users u ON ar.user_id = u.id ORDER BY ar.created_at DESC");
while ($row = $res->fetch_assoc()) {
    $row['_type'] = 'asset';
    $assetRequests[] = $row;
}

$res = $conn->query("SELECT sr.*, u.fullname as requester FROM supply_requests sr LEFT JOIN users u ON sr.user_id = u.id ORDER BY sr.created_at DESC");
while ($row = $res->fetch_assoc()) {
    $row['_type'] = 'supply';
    $supplyRequests[] = $row;
}

$allRequests = array_merge($assetRequests, $supplyRequests);

// Group by status
$grouped = ['Pending' => [], 'Processing' => [], 'Completed' => []];
foreach ($allRequests as $r) {
    $grouped[$r['status']][] = $r;
}
?>

<div class="kanban-board animate-fadeInUp">
    <!-- Pending Column -->
    <div class="kanban-column">
        <div class="kanban-header pending">
            <span><i class="fas fa-clock me-2"></i>รอดำเนินการ</span>
            <span class="kanban-count">
                <?= count($grouped['Pending']) ?>
            </span>
        </div>
        <div class="kanban-cards" id="pendingCards">
            <?php foreach ($grouped['Pending'] as $item): ?>
                <?= renderKanbanCard($item) ?>
            <?php endforeach; ?>
            <?php if (empty($grouped['Pending'])): ?>
                <div class="empty-state"><i class="fas fa-inbox d-block"></i>
                    <p class="small">ไม่มีคำขอรอดำเนินการ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Processing Column -->
    <div class="kanban-column">
        <div class="kanban-header processing">
            <span><i class="fas fa-cog me-2"></i>กำลังดำเนินการ</span>
            <span class="kanban-count">
                <?= count($grouped['Processing']) ?>
            </span>
        </div>
        <div class="kanban-cards" id="processingCards">
            <?php foreach ($grouped['Processing'] as $item): ?>
                <?= renderKanbanCard($item) ?>
            <?php endforeach; ?>
            <?php if (empty($grouped['Processing'])): ?>
                <div class="empty-state"><i class="fas fa-inbox d-block"></i>
                    <p class="small">ไม่มีคำขอกำลังดำเนินการ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Completed Column -->
    <div class="kanban-column">
        <div class="kanban-header completed">
            <span><i class="fas fa-check-circle me-2"></i>เสร็จสิ้น</span>
            <div class="d-flex align-items-center gap-2">
                <span class="kanban-count" id="completedCount">
                    <?= count($grouped['Completed']) ?>
                </span>
            </div>
        </div>
        <!-- Action bar: Hide All / Show All / View Details -->
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <button class="btn btn-sm btn-outline-secondary" id="toggleHideAll" onclick="toggleHideAllCompleted()"
                title="ซ่อน/แสดงทั้งหมด">
                <i class="fas fa-eye-slash me-1"></i><span id="toggleHideLabel">ซ่อนทั้งหมด</span>
            </button>
            <a href="<?= BASE_URL ?>/my-requests.php?status=Completed" class="btn btn-sm btn-outline-success"
                title="ดูรายละเอียดทั้งหมด">
                <i class="fas fa-external-link-alt me-1"></i>ดูรายละเอียด
            </a>
        </div>
        <div class="kanban-cards" id="completedCards">
            <?php foreach ($grouped['Completed'] as $item): ?>
                <?= renderKanbanCard($item, true) ?>
            <?php endforeach; ?>
            <?php if (empty($grouped['Completed'])): ?>
                <div class="empty-state" id="completedEmpty"><i class="fas fa-inbox d-block"></i>
                    <p class="small">ยังไม่มีคำขอที่เสร็จสิ้น</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail / Status Update Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>รายละเอียดคำขอ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Render a Kanban card
 */
function renderKanbanCard($item, $isCompleted = false)
{
    $type = $item['_type'];
    $id = $item['id'];
    $status = $item['status'];
    $cardKey = $type . '_' . $id;

    if ($type === 'asset') {
        $typeLabel = 'สินทรัพย์';
        $typeColor = '#667eea';
        $title = sanitize($item['asset_id']) . ' — ' . sanitize($item['department']);
        $detail = sanitize($item['asset_group']);
    } else {
        $typeLabel = requestTypeLabel($item['request_type']);
        $typeColor = '#f093fb';
        $title = sanitize($item['item_name']);
        $detail = $item['item_number'] ? 'เลขที่: ' . sanitize($item['item_number']) : sanitize($item['unit']);
    }

    $escapedItem = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES);

    $html = '<div class="kanban-card" data-card-key="' . $cardKey . '" onclick=\'showDetail(' . $escapedItem . ')\'>';
    // Title row with dismiss button for completed
    $html .= '<div class="d-flex justify-content-between align-items-start">';
    $html .= '<div class="card-type" style="color: ' . $typeColor . ';">' . $typeLabel . ' #' . $id . '</div>';
    if ($isCompleted) {
        $html .= '<button class="btn btn-sm p-0 text-muted btn-dismiss-card" onclick="event.stopPropagation(); hideCompletedCard(\'' . $cardKey . '\', this)" title="ซ่อนรายการนี้">';
        $html .= '<i class="fas fa-times"></i>';
        $html .= '</button>';
    }
    $html .= '</div>';
    $html .= '<div class="card-title">' . $title . '</div>';
    $html .= '<div class="small text-muted mb-2">' . $detail . '</div>';
    $html .= '<div class="card-meta">';
    $html .= '<i class="fas fa-user"></i>' . sanitize($item['requester'] ?? 'N/A');
    $html .= '<span>•</span>';
    $html .= '<i class="fas fa-calendar"></i>' . thaiDate($item['created_at'], 'compact');
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

$extraJs = <<<JS
<script src="assets/js/kanban.js?v={$_SERVER['REQUEST_TIME']}"></script>
<script>
// ====================================================
// Completed cards hide/dismiss functionality
// ====================================================
var STORAGE_KEY = 'kanban_hidden_completed';

function getHiddenCards() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
    catch(e) { return []; }
}
function saveHiddenCards(arr) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
}

function hideCompletedCard(cardKey, btnEl) {
    var card = btnEl.closest('.kanban-card');
    if (!card) return;
    card.style.transition = 'all 0.3s ease';
    card.style.opacity = '0';
    card.style.transform = 'translateX(20px)';
    setTimeout(function() {
        card.style.display = 'none';
        updateCompletedCount();
    }, 300);
    var hidden = getHiddenCards();
    if (hidden.indexOf(cardKey) === -1) {
        hidden.push(cardKey);
        saveHiddenCards(hidden);
    }
}

function toggleHideAllCompleted() {
    var container = document.getElementById('completedCards');
    if (!container) return;
    var cards = container.querySelectorAll('.kanban-card');
    var label = document.getElementById('toggleHideLabel');
    var iconEl = document.querySelector('#toggleHideAll i');
    var visibleCards = [];
    for (var i = 0; i < cards.length; i++) {
        if (cards[i].style.display !== 'none') visibleCards.push(cards[i]);
    }
    if (visibleCards.length > 0) {
        var hidden = getHiddenCards();
        for (var j = 0; j < cards.length; j++) {
            (function(card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateX(20px)';
                setTimeout(function() { card.style.display = 'none'; }, 300);
                var key = card.getAttribute('data-card-key');
                if (key && hidden.indexOf(key) === -1) hidden.push(key);
            })(cards[j]);
        }
        saveHiddenCards(hidden);
        if (label) label.textContent = 'แสดงทั้งหมด';
        if (iconEl) iconEl.className = 'fas fa-eye me-1';
        setTimeout(updateCompletedCount, 350);
    } else {
        saveHiddenCards([]);
        for (var k = 0; k < cards.length; k++) {
            cards[k].style.display = '';
            cards[k].style.opacity = '1';
            cards[k].style.transform = 'translateX(0)';
        }
        if (label) label.textContent = 'ซ่อนทั้งหมด';
        if (iconEl) iconEl.className = 'fas fa-eye-slash me-1';
        updateCompletedCount();
    }
}

function updateCompletedCount() {
    var container = document.getElementById('completedCards');
    if (!container) return;
    var cards = container.querySelectorAll('.kanban-card');
    var count = 0;
    for (var i = 0; i < cards.length; i++) {
        if (cards[i].style.display !== 'none') count++;
    }
    var countEl = document.getElementById('completedCount');
    if (countEl) countEl.textContent = count;
}

// On page load: restore hidden state
(function() {
    var hidden = getHiddenCards();
    if (hidden.length === 0) return;
    var container = document.getElementById('completedCards');
    if (!container) return;
    for (var i = 0; i < hidden.length; i++) {
        var card = container.querySelector('[data-card-key="' + hidden[i] + '"]');
        if (card) card.style.display = 'none';
    }
    updateCompletedCount();
    var cards = container.querySelectorAll('.kanban-card');
    var allHidden = true;
    for (var j = 0; j < cards.length; j++) {
        if (cards[j].style.display !== 'none') { allHidden = false; break; }
    }
    if (allHidden && cards.length > 0) {
        var label = document.getElementById('toggleHideLabel');
        var iconEl = document.querySelector('#toggleHideAll i');
        if (label) label.textContent = 'แสดงทั้งหมด';
        if (iconEl) iconEl.className = 'fas fa-eye me-1';
    }
})();
</script>
<style>
.btn-dismiss-card {
    opacity: 0.3;
    transition: all 0.2s ease;
    line-height: 1;
    font-size: 0.75rem;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: none;
    background: none;
}
.btn-dismiss-card:hover {
    opacity: 1;
    color: #e53e3e !important;
    background: rgba(229, 62, 62, 0.1);
}
</style>
JS;

require_once __DIR__ . '/includes/footer.php';
?>