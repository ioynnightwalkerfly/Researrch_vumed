<?php
// api/register_action.php (Final Version with Email Verification)

// ปิด Error HTML เพื่อกัน JSON พัง
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// เรียกไฟล์ที่จำเป็น
require_once 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }

    // 1. รับค่าและตรวจสอบ
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_card = $_POST['id_card_number'] ?? '';

    if(empty($username) || empty($password) || empty($id_card) || empty($email)) {
        throw new Exception('กรุณากรอกข้อมูลสำคัญให้ครบถ้วน');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 2. จัดการไฟล์แนบ (อัปโหลด CV)
    $cv_path = null;
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        // จำกัดขนาด 5MB (5 * 1024 * 1024 bytes)
        if ($_FILES['cv_file']['size'] > 5242880) {
            throw new Exception('ขนาดไฟล์ CV ต้องไม่เกิน 5MB');
        }

        // เช็ค MIME Type แท้จริง
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['cv_file']['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception('ประเภทไฟล์ไม่รองรับ กรุณาอัปโหลด PDF, JPG หรือ PNG เท่านั้น');
        }

        $uploadDir = '../uploads/cv/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
        $fileExt = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid('cv_') . '.' . $fileExt;
        
        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $uploadDir . $newFileName)) {
            $cv_path = $newFileName;
        } else {
             throw new Exception('เกิดข้อผิดพลาดในการบันทึกไฟล์ Server');
        }
    }

    // 3. สร้าง Token สำหรับยืนยันอีเมล
    $token = bin2hex(random_bytes(32));

    // 4. บันทึกลง Database (is_verified = 0 เพราะต้องรอยืนยัน)
    $sql = "INSERT INTO users (
                username, password_hash, email, id_card_number, verification_token, is_verified,
                prefix_th, firstname_th, lastname_th, prefix_eng, firstname_eng, lastname_eng,
                phone_office, mobile_phone, faculty, is_external,
                qual_health_personnel, qual_social_scientist, qual_non_medical, qual_community_rep, qual_lawyer,
                role_researcher, role_coordinator, cv_file_path
            ) VALUES (
                ?, ?, ?, ?, ?, 0, 
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $username, $password_hash, $email, $id_card, $token,
        $_POST['prefix_th'], $_POST['firstname_th'], $_POST['lastname_th'],
        $_POST['prefix_eng'], $_POST['firstname_eng'], $_POST['lastname_eng'],
        $_POST['phone_office'], $_POST['mobile_phone'], $_POST['faculty'], isset($_POST['is_external']) ? 1 : 0,
        isset($_POST['qual_health']) ? 1 : 0, isset($_POST['qual_social']) ? 1 : 0,
        isset($_POST['qual_non_med']) ? 1 : 0, isset($_POST['qual_comm']) ? 1 : 0, isset($_POST['qual_law']) ? 1 : 0,
        isset($_POST['role_researcher']) ? 1 : 0, isset($_POST['role_coordinator']) ? 1 : 0,
        $cv_path
    ]);

    // 5. ส่งอีเมลยืนยัน (ใช้ PHPMailer)
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // =======================================================
    // 🔴 แก้ไขอีเมลและรหัสผ่าน App Password ตรงนี้ให้เหมือนที่เทสผ่าน 🔴
    // =======================================================
    $mail->Username   = 'oopoo456123@gmail.com'; 
    $mail->Password   = 'kekf lesm czud zqeq';
    // =======================================================

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mail->Username, 'Research Portal System');
    $mail->addAddress($email, $_POST['firstname_th']);

    // สร้างลิงก์ยืนยันอัตโนมัติ
    $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']); 
    $verifyLink = "$protocol://$host$path/verify.php?token=$token";

    $mail->isHTML(true);
    $mail->Subject = 'ยืนยันการลงทะเบียน (Verify Registration)';
    $mail->Body    = "
        <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #2563EB;'>สวัสดีคุณ {$_POST['firstname_th']}</h2>
            <p>ขอบคุณที่สมัครสมาชิก <b>ระบบฐานข้อมูลนักวิจัย</b></p>
            <p>กรุณากดปุ่มด้านล่างเพื่อยืนยันอีเมลและเปิดใช้งานบัญชี:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='$verifyLink' style='background:#2563EB; color:white; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold;'>ยืนยันบัญชี (Verify Account)</a>
            </p>
            <p style='font-size: 12px; color: #999;'>หากปุ่มใช้งานไม่ได้ ให้คลิกลิงก์นี้: <a href='$verifyLink'>$verifyLink</a></p>
        </div>
    ";

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'ลงทะเบียนสำเร็จ! กรุณาตรวจสอบอีเมลเพื่อยืนยันตัวตน']);

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
         $errorMessage = "ข้อมูลซ้ำ: ";
        if (strpos($e->getMessage(), 'email') !== false) $errorMessage .= "อีเมลนี้ใช้ไปแล้ว";
        elseif (strpos($e->getMessage(), 'username') !== false) $errorMessage .= "ชื่อผู้ใช้นี้ใช้ไปแล้ว";
        elseif (strpos($e->getMessage(), 'id_card_number') !== false) $errorMessage .= "เลขบัตรนี้ใช้ไปแล้ว";
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>