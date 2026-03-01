<?php
// api/test_mail.php

// แสดง Error ทั้งหมดออกมาดู
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>เริ่มการทดสอบส่งอีเมล...</h1>";

// 1. ตรวจสอบว่ามีไฟล์ PHPMailer ไหม
if (!file_exists('PHPMailer/src/PHPMailer.php')) {
    die("<h3 style='color:red;'>❌ ไม่พบไฟล์ PHPMailer! กรุณาตรวจสอบว่ามีโฟลเดอร์ api/PHPMailer อยู่จริง</h3>");
}

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // 2. ตั้งค่า Server (ใช้ Gmail)
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // เปิดบรรทัดนี้ถ้าอยากดู Log ละเอียดถ้ายิงไม่ผ่าน
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // ====================================================
    // 🔴 แก้ไขข้อมูลตรงนี้ (สำคัญ!) 🔴
    // ====================================================
    $mail->Username   = 'oopoo456123@gmail.com'; // 1. ใส่อีเมล Gmail ของคุณ (ผู้ส่ง)
    $mail->Password   = 'kekf lesm czud zqeq';  // 2. ใส่รหัส App Password 16 หลัก (ไม่ใช่รหัส Login)
    // ====================================================

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // 3. ตั้งค่าผู้รับ-ผู้ส่ง
    $mail->setFrom($mail->Username, 'Test System');
    $mail->addAddress($mail->Username); // ส่งเข้าเมลตัวเองนี่แหละ ง่ายดี

    // 4. เนื้อหา
    $mail->isHTML(true);
    $mail->Subject = 'ทดสอบระบบส่งเมล (Test Mail)';
    $mail->Body    = 'ถ้าคุณเห็นข้อความนี้ แสดงว่า <b>PHPMailer ตั้งค่าถูกต้องแล้ว!</b> 🎉 พร้อมใช้งานจริง';

    $mail->send();
    echo "<h2 style='color:green;'>✅ ส่งอีเมลสำเร็จ! (Message has been sent)</h2>";
    echo "<p>กรุณาเช็ค Inbox หรือ Junk Mail ของคุณ: " . $mail->Username . "</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ ส่งอีเมลไม่สำเร็จ (Message could not be sent)</h2>";
    echo "<p>Mailer Error: {$mail->ErrorInfo}</p>";
    
    // คำแนะนำเพิ่มเติมตาม Error ที่พบบ่อย
    if (strpos($mail->ErrorInfo, 'SMTP connect() failed') !== false) {
        echo "<hr><p><b>คำแนะนำ:</b> XAMPP อาจจะต่อเน็ตไม่ได้ หรือถูก Firewall บล็อกพอร์ต 587 หรือยังไม่เปิด extension=openssl ใน php.ini</p>";
    }
    if (strpos($mail->ErrorInfo, 'Username and Password not accepted') !== false) {
        echo "<hr><p><b>คำแนะนำ:</b> รหัสผ่านผิด! ห้ามใช้รหัส Login ปกติ ต้องใช้ <b>App Password</b> เท่านั้น</p>";
    }
}
?>