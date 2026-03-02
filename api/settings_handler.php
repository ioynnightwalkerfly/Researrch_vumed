<?php
// api/settings_handler.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userRole = $_SESSION['role'] ?? '';
$isAdmin = false;
if (is_array($userRole)) {
    $isAdmin = in_array('admin', $userRole);
} else {
    $isAdmin = ($userRole === 'admin');
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

try {
    if ($action === 'get_settings') {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        echo json_encode(['success' => true, 'settings' => $settings]);
    } 
    elseif ($action === 'update_setting') {
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'Permission denied. Admins only.']);
            exit();
        }

        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';

        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'Missing setting key']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
        
        // If no rows were updated (e.g., key didn't exist, though we seeded it), we might want to insert.
        // But for our specific case, the seed handles it. Let's do an UPSERT just in case.
        if ($stmt->rowCount() === 0) {
            $stmtInsert = $conn->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmtInsert->execute([$key, $value]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Setting updated']);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
