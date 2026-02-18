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
            <span class="kanban-count">
                <?= count($grouped['Completed']) ?>
            </span>
        </div>
        <div class="kanban-cards" id="completedCards">
            <?php foreach ($grouped['Completed'] as $item): ?>
                <?= renderKanbanCard($item) ?>
            <?php endforeach; ?>
            <?php if (empty($grouped['Completed'])): ?>
                <div class="empty-state"><i class="fas fa-inbox d-block"></i>
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
function renderKanbanCard($item)
{
    $type = $item['_type'];
    $id = $item['id'];
    $status = $item['status'];

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

    $html = '<div class="kanban-card" onclick=\'showDetail(' . $escapedItem . ')\'>';
    $html .= '<div class="card-type" style="color: ' . $typeColor . ';">' . $typeLabel . ' #' . $id . '</div>';
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

$extraJs = <<<'JS'
<script src="assets/js/kanban.js"></script>
JS;

require_once __DIR__ . '/includes/footer.php';
?>