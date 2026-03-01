<?php
// api/reset_password_action.php - Process password reset
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request');
    }

    $token = trim($_POST['token'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($token)) {
        throw new Exception('Token ไม่ถูกต้อง');
    }
    if (empty($newPassword) || strlen($newPassword) < 6) {
        throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
    }
    if ($newPassword !== $confirmPassword) {
        throw new Exception('รหัสผ่านไม่ตรงกัน');
    }

    // Find user with this token
    $stmt = $conn->prepare("SELECT id, firstname_th FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('ลิงก์รีเซ็ตไม่ถูกต้องหรือหมดอายุแล้ว กรุณาขอลิงก์ใหม่');
    }

    // Update password and clear token
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    echo json_encode(['status' => 'success', 'message' => 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
