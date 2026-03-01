<?php
// api/admin_user_action.php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// Security Check (In real app, check for 'Admin' role)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'get_users') {
        $stmt = $conn->prepare("SELECT id, username, email, firstname_th, lastname_th, role_researcher, role_coordinator, role_admin, is_verified FROM users ORDER BY id DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);

    } elseif ($action === 'create_user') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $email = $_POST['email'];
        $isRes = $_POST['role_researcher'];
        $isCoord = $_POST['role_coordinator'];
        $isAdmin = $_POST['role_admin'] ?? 0;

        // Minimal Insert (some fields nullable or default)
        // Note: id_card_number is NOT NULL in schema usually. 
        // We generate a "Fake" 13-digit ID for Admin-created users to prevent "Duplicate" errors if the DB limits length to 13 chars.
        // Format: 9 + 12 random digits (starting with 9 to indicate system/mock)
        $idCard = '9' . str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO users (username, password_hash, email, firstname_th, lastname_th, role_researcher, role_coordinator, role_admin, is_verified, id_card_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $password, $email, $fname, $lname, $isRes, $isCoord, $isAdmin, $idCard]);
        
        echo json_encode(['success' => true]);

    } elseif ($action === 'update_role') {
        $userId = $_POST['user_id'];
        $roleType = $_POST['role_type']; // 'researcher', 'coordinator', 'admin'
        $val = $_POST['value']; // 1 or 0

        $col = '';
        if ($roleType === 'coordinator') $col = 'role_coordinator';
        elseif ($roleType === 'researcher') $col = 'role_researcher';
        elseif ($roleType === 'admin') $col = 'role_admin';
        elseif ($roleType === 'officer') $col = 'role_officer';
        elseif ($roleType === 'secretary') $col = 'role_secretary';

        if ($col) {
            $stmt = $conn->prepare("UPDATE users SET $col = ? WHERE id = ?");
            $stmt->execute([$val, $userId]);
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Invalid role type");
        }


    } elseif ($action === 'update_user_details') {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $email = $_POST['email'];
        $password = $_POST['password'] ?? '';

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username=?, email=?, firstname_th=?, lastname_th=?, password_hash=? WHERE id=?";
            $params = [$username, $email, $fname, $lname, $hashed, $userId];
        } else {
            $sql = "UPDATE users SET username=?, email=?, firstname_th=?, lastname_th=? WHERE id=?";
            $params = [$username, $email, $fname, $lname, $userId];
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);

    } elseif ($action === 'delete_user') {
        $userId = $_POST['user_id'];
        // Prevent self-delete
        if ($userId == $_SESSION['user_id']) {
            throw new Exception("Cannot delete yourself");
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true]);

    } elseif ($action === 'delete_project') {
        // Admin-only: delete any project
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            throw new Exception("Admin access required");
        }
        $projectId = $_POST['project_id'];
        
        $conn->beginTransaction();
        
        // Verify project exists
        $stmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        if (!$stmt->fetch()) throw new Exception("Project not found");
        
        // Delete dependencies
        $conn->prepare("DELETE FROM project_documents WHERE project_id = ?")->execute([$projectId]);
        $conn->prepare("DELETE FROM project_team WHERE project_id = ?")->execute([$projectId]);
        
        // Delete project
        $conn->prepare("DELETE FROM projects WHERE id = ?")->execute([$projectId]);
        
        $conn->commit();
        echo json_encode(['success' => true]);

    } elseif ($action === 'update_project_status') {
        // Admin: change project status
        if ($_SESSION['role'] !== 'admin') throw new Exception('Admin only');
        
        $projectId = $_POST['project_id'] ?? null;
        $newStatus = $_POST['new_status'] ?? null;
        
        $allowed = ['draft','pending_officer','pending_secretary','pending_chairman','approved','rejected','rejected_secretary','working','published'];
        if (!in_array($newStatus, $allowed)) throw new Exception('Invalid status');
        
        $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $projectId]);
        
        echo json_encode(['success' => true, 'message' => 'สถานะอัพเดตเรียบร้อย']);

    } else {
        throw new Exception("Invalid Action");
    }

} catch (Exception $e) {
    // Check for duplicate entry
    $msg = $e->getMessage();
    if ($e instanceof PDOException && $e->errorInfo[1] == 1062) {
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'username') !== false) {
            $msg = "ชื่อผู้ใช้ (Username) นี้ถูกใช้งานแล้ว";
        } elseif (strpos($errorMsg, 'email') !== false) {
            $msg = "อีเมล (Email) นี้ถูกใช้งานแล้ว";
        } elseif (strpos($errorMsg, 'id_card_number') !== false) {
            $msg = "รหัสบัตรประชาชน/ID Card นี้ซ้ำกับในระบบ";
        } else {
            $msg = "ข้อมูลซ้ำกับในระบบ (Duplicate Entry)";
        }
    }
    echo json_encode(['success' => false, 'message' => $msg]);
}
?>
