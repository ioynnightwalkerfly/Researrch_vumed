<?php
// api/send_reset_link.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure columns exist
try {
    $conn->exec("ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(64) DEFAULT NULL");
} catch (\Throwable $e) {}
try {
    $conn->exec("ALTER TABLE users ADD COLUMN password_reset_expires DATETIME DEFAULT NULL");
} catch (\Throwable $e) {}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }

    $email = $_POST['email'] ?? '';
    if (empty($email)) {
        throw new Exception('กรุณากรอกอีเมล');
    }

    // 1. Check if Email Exists
    $stmt = $conn->prepare("SELECT id, firstname_th FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Security: Don't reveal if email exists or not, but for UX let's say "Email checked" or just return success even if not found (blind).
        // For this internal project, accurate error is maybe better for debugging/user.
        throw new Exception('ไม่พบอีเมลนี้ในระบบ');
    }

    // 2. Generate Token & Expiry (1 Hour)
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // 3. Update User
    $updateSql = "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->execute([$token, $expires, $user['id']]);

    // 4. Send Email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // Reuse credentials from register_action.php
    $mail->Username   = 'oopoo456123@gmail.com'; 
    $mail->Password   = 'kekf lesm czud zqeq';
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mail->Username, 'Research Portal System');
    $mail->addAddress($email, $user['firstname_th']);

    // Link
    $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname(dirname($_SERVER['PHP_SELF'])); // Go up one level from /api/
    // Note: Since script is in /api/, dirname is /research/api. We want /research/
    // Actually typically setup is localhost/research/api/send_reset.php
    // We want localhost/research/reset_password.php (not created yet, but link should point there)
    
    // Fix path logic:
    // If current is /research/api/send...
    // dirname is /research/api
    // dirname(dirname) is /research
    $basePath = dirname(dirname($_SERVER['PHP_SELF']));
    $resetLink = "$protocol://$host$basePath/reset_password.php?token=$token";

    $mail->isHTML(true);
    $mail->Subject = 'รีเซ็ตรหัสผ่าน (Reset Password)';
    $mail->Body    = "
        <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #2563EB;'>สวัสดีคุณ {$user['firstname_th']}</h2>
            <p>มีการแจ้งลืมรหัสผ่านสำหรับบัญชีของคุณ</p>
            <p>หากคุณต้องการตั้งรหัสผ่านใหม่ กรุณากดปุ่มด้านล่าง (ลิงก์มีอายุ 1 ชั่วโมง):</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='$resetLink' style='background:#2563EB; color:white; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold;'>ตั้งรหัสผ่านใหม่</a>
            </p>
            <p style='font-size: 12px; color: #999;'>หากคุณไม่ได้เป็นผู้ร้องขอ กรุณาเพิกเฉยต่ออีเมลฉบับนี้</p>
        </div>
    ";

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'ส่งลิงก์รีเซ็ตเรียบร้อย']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
