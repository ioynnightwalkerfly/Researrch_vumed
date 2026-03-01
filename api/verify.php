<?php
// api/verify.php
require_once 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 1. ค้นหา Token ในฐานข้อมูล
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. ถ้าเจอ -> ปรับสถานะเป็น Verified และลบ Token ทิ้ง (เพื่อไม่ให้ใช้ซ้ำ)
        $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        // 3. แจ้งเตือนและเด้งไปหน้า Login
        echo "
        <script>
            alert('ยืนยันอีเมลเรียบร้อยแล้ว! บัญชีของคุณเปิดใช้งานแล้ว');
            window.location.href = '../login.html';
        </script>
        ";
    } else {
        echo "Link ยืนยันไม่ถูกต้อง หรือ บัญชีถูกเปิดใช้งานไปแล้ว";
    }
} else {
    echo "ไม่พบ Token";
}
?>