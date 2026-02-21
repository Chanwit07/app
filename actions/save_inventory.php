<?php
/**
 * Inventory Assets - CRUD Actions (AJAX)
 * Handles: create, update, delete, toggle_status for all asset types
 */
require_once __DIR__ . '/../auth.php';
checkAuth();

// Only admins can manage assets
if (!isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์ดำเนินการ');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ============================
// CREATE & UPDATE (Handle together since fields are the same)
// ============================
if ($action === 'create' || $action === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $is_update = ($action === 'update' && $id > 0);

    // Common Fields
    $asset_type = $_POST['asset_type'] ?? '';
    if (!in_array($asset_type, ['fixed_asset', 'container', 'computer'])) {
        jsonResponse(false, 'ประเภทสินทรัพย์ไม่ถูกต้อง');
    }

    $asset_code = sanitize($_POST['asset_code'] ?? '');
    $asset_name = sanitize($_POST['asset_name'] ?? '');

    if (empty($asset_code) || empty($asset_name)) {
        jsonResponse(false, 'กรุณากรอกรหัสและชื่อรายการให้ครบถ้วน');
    }

    $quantity = (int) ($_POST['quantity'] ?? 1);
    // Computers usually have qty = 1
    if ($asset_type === 'computer')
        $quantity = 1;

    $unit = sanitize($_POST['unit'] ?? '');
    $asset_value = (float) ($_POST['asset_value'] ?? 0);
    $acquisition_date = !empty($_POST['acquisition_date']) ? $_POST['acquisition_date'] : null;

    $responsible_dept = sanitize($_POST['responsible_dept'] ?? '');
    $install_department = sanitize($_POST['install_department'] ?? '');
    $install_section = sanitize($_POST['install_section'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    $computer_type = null;
    $brand = null;
    $model = null;
    $serial_number = null;
    $warranty_years = null;

    if ($asset_type === 'computer') {
        $computer_type = $_POST['computer_type'] ?? null;
        if (!in_array($computer_type, ['own', 'rent']))
            $computer_type = null;

        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $serial_number = sanitize($_POST['serial_number'] ?? '');
        $warranty_years = !empty($_POST['warranty_years']) ? (int) $_POST['warranty_years'] : null;
    }

    // Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = handleUpload($_FILES['image'], 'inventory');
        if ($uploaded) {
            $image_path = $uploaded;
        } else {
            jsonResponse(false, 'อัปโหลดรูปภาพไม่สำเร็จ กรุณาตรวจสอบขนาดและนามสกุลไฟล์');
        }
    }

    if ($is_update) {
        // Keep old image if no new one
        if (!$image_path) {
            $image_path = $_POST['existing_image'] ?? null;
        }

        $stmt = $conn->prepare("UPDATE inventory_assets SET 
            asset_type=?, asset_code=?, asset_name=?, quantity=?, unit=?, 
            asset_value=?, acquisition_date=?, responsible_dept=?, install_department=?, 
            install_section=?, remarks=?, image_path=?, computer_type=?, 
            brand=?, model=?, serial_number=?, warranty_years=?
            WHERE id=?");

        $stmt->bind_param(
            "sssisdsssssssssiii",
            $asset_type,
            $asset_code,
            $asset_name,
            $quantity,
            $unit,
            $asset_value,
            $acquisition_date,
            $responsible_dept,
            $install_department,
            $install_section,
            $remarks,
            $image_path,
            $computer_type,
            $brand,
            $model,
            $serial_number,
            $warranty_years,
            $id
        );
        $action_log = 'แก้ไขรายการ #' . $id;
    } else {
        $created_by = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO inventory_assets (
            asset_type, asset_code, asset_name, quantity, unit, 
            asset_value, acquisition_date, responsible_dept, install_department, 
            install_section, remarks, image_path, computer_type, 
            brand, model, serial_number, warranty_years, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssisdsssssssssiii",
            $asset_type,
            $asset_code,
            $asset_name,
            $quantity,
            $unit,
            $asset_value,
            $acquisition_date,
            $responsible_dept,
            $install_department,
            $install_section,
            $remarks,
            $image_path,
            $computer_type,
            $brand,
            $model,
            $serial_number,
            $warranty_years,
            $created_by
        );
        $action_log = 'เพิ่มรายการใหม่: ' . $asset_code;
    }

    if ($stmt->execute()) {
        $target_id = $is_update ? $id : $stmt->insert_id;
        logAudit($conn, $action_log, 'inventory_assets', $target_id);
        jsonResponse(true, $is_update ? 'บันทึกการแก้ไขสำเร็จ' : 'เพิ่มรายการสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
    $stmt->close();
}

// ============================
// DELETE
// ============================
if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("DELETE FROM inventory_assets WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAudit($conn, 'ลบรายการ (Hard Delete) #' . $id, 'inventory_assets', $id);
        jsonResponse(true, 'ลบสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด: ' . $conn->error);
    }
    $stmt->close();
}

// ============================
// UPDATE STATUS
// ============================
if ($action === 'update_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    if ($id <= 0 || !in_array($new_status, ['active', 'broken', 'written_off'])) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("UPDATE inventory_assets SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);

    if ($stmt->execute()) {
        logAudit($conn, "เปลี่ยนสถานะรายการ #{$id} เป็น {$new_status}", 'inventory_assets', $id);
        jsonResponse(true, 'อัปเดตสถานะสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาด');
    }
    $stmt->close();
}

// ============================
// GET (single item for edit)
// ============================
if ($action === 'get') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("SELECT * FROM inventory_assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        // add formatted urls for quick preview
        if ($item['image_path']) {
            $item['full_image_url'] = BASE_URL . '/uploads/' . $item['image_path'];
        }
        jsonResponse(true, '', ['item' => $item]);
    } else {
        jsonResponse(false, 'ไม่พบข้อมูล');
    }
}

jsonResponse(false, 'ไม่ระบุ action');
