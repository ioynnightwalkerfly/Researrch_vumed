<?php
// api/login_action.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('กรุณากรอกอีเมลและรหัสผ่าน');
    }

    // ดึงข้อมูลผู้ใช้ด้วย Email + ทุก Role
    $sql = "SELECT id, username, password_hash, is_verified, firstname_th, lastname_th, email,
                   role_researcher, role_coordinator, role_admin, role_officer, role_secretary 
            FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        
        // เช็คยืนยันอีเมล
        if ($user['is_verified'] == 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'บัญชีของคุณยังไม่ได้ยืนยันตัวตนทางอีเมล กรุณาตรวจสอบ Inbox หรือ Junk Mail เพื่อกดลิงก์ยืนยัน'
            ]);
            exit;
        }

        // สร้าง Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['firstname_th'] . ' ' . $user['lastname_th'];
        $_SESSION['user_email'] = $user['email'];
        
        // เก็บสิทธิ์ต่างๆ ลง Session
        $_SESSION['user_roles'] = [
            'researcher'  => true,
            'coordinator' => !empty($user['role_coordinator']),
            'admin'       => !empty($user['role_admin']),
            'officer'     => !empty($user['role_officer']),
            'secretary'   => !empty($user['role_secretary']),
        ];

        unset($_SESSION['role']); 

        echo json_encode([
            'status' => 'success', 
            'redirect' => 'select_role.php'
        ]);

    } else {
        throw new Exception('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>