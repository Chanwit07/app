<?php
/**
 * Save App Settings (Telegram config)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
checkRole(['admin', 'super_admin']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$action = $_POST['action'] ?? 'save';

if ($action === 'save') {
    $botToken = trim($_POST['telegram_bot_token'] ?? '');
    $chatId = trim($_POST['telegram_chat_id'] ?? '');
    $enabled = isset($_POST['telegram_enabled']) ? '1' : '0';
    $adminId = (int) $_SESSION['user_id'];

    // Upsert settings
    $settings = [
        'telegram_bot_token' => $botToken,
        'telegram_chat_id' => $chatId,
        'telegram_enabled' => $enabled
    ];

    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("
            INSERT INTO app_settings (setting_key, setting_value, updated_by) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)
        ");
        $stmt->bind_param("ssi", $key, $value, $adminId);
        $stmt->execute();
        $stmt->close();
    }

    logAudit($conn, 'อัปเดตการตั้งค่า Telegram', 'app_settings', null, json_encode([
        'enabled' => $enabled,
        'has_token' => !empty($botToken)
    ]));

    jsonResponse(true, 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
}

if ($action === 'test') {
    $botToken = trim($_POST['telegram_bot_token'] ?? '');
    $chatId = trim($_POST['telegram_chat_id'] ?? '');

    if (empty($botToken) || empty($chatId)) {
        jsonResponse(false, 'กรุณากรอก Bot Token และ Chat ID');
    }

    // Send test message
    $testMsg = "✅ <b>ทดสอบการเชื่อมต่อ Telegram</b>\n";
    $testMsg .= "━━━━━━━━━━━━━\n";
    $testMsg .= "🔧 ระบบ: " . APP_NAME . "\n";
    $testMsg .= "👤 ทดสอบโดย: " . sanitize($_SESSION['fullname']) . "\n";
    $testMsg .= "📅 วันที่: " . thaiDate(null, 'full') . "\n";
    $testMsg .= "━━━━━━━━━━━━━\n";
    $testMsg .= "🎉 <i>การเชื่อมต่อสำเร็จ!</i>";

    $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $testMsg,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        jsonResponse(false, 'ไม่สามารถเชื่อมต่อได้: ' . $error);
    }

    $response = json_decode($result, true);
    if ($httpCode === 200 && ($response['ok'] ?? false)) {
        jsonResponse(true, 'ส่งข้อความทดสอบสำเร็จ! ตรวจสอบใน Telegram');
    } else {
        $errDesc = $response['description'] ?? 'Unknown error';
        jsonResponse(false, 'Telegram Error: ' . $errDesc);
    }
}

jsonResponse(false, 'Invalid action');
