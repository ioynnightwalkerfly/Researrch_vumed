<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// Check Role (Admin or Staff)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff', 'secretary'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

try {
    switch ($action) {
        case 'fetch':
            $stmt = $conn->query("SELECT * FROM organizations_manual ORDER BY records_count DESC, id DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'add':
            $node_id = trim($_POST['node_id'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $records_count = intval($_POST['records_count'] ?? 0);
            $group_id = trim($_POST['group_id'] ?? '');

            if (empty($node_id) || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'รหัสอ้างอิงและชื่อหน่วยงานห้ามเป็นค่าว่าง']);
                exit();
            }

            // check duplicate node_id
            $check = $conn->prepare("SELECT id FROM organizations_manual WHERE node_id = ?");
            $check->execute([$node_id]);
            if ($check->rowCount() > 0) {
                 echo json_encode(['success' => false, 'message' => 'รหัสอ้างอิงนี้มีอยู่ในระบบแล้ว']);
                 exit();
            }

            $stmt = $conn->prepare("INSERT INTO organizations_manual (node_id, name, category, records_count, group_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$node_id, $name, $category, $records_count, $group_id]);
            
            echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $node_id = trim($_POST['node_id'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $records_count = intval($_POST['records_count'] ?? 0);
            $group_id = trim($_POST['group_id'] ?? '');

            if ($id <= 0 || empty($node_id) || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit();
            }

            // Check duplicate node_id excluding self
            $check = $conn->prepare("SELECT id FROM organizations_manual WHERE node_id = ? AND id != ?");
            $check->execute([$node_id, $id]);
            if ($check->rowCount() > 0) {
                 echo json_encode(['success' => false, 'message' => 'รหัสอ้างอิงนี้มีอยู่ในระบบแล้ว (ซ้ำซ้อน)']);
                 exit();
            }

            $stmt = $conn->prepare("UPDATE organizations_manual SET node_id=?, name=?, category=?, records_count=?, group_id=? WHERE id=?");
            $stmt->execute([$node_id, $name, $category, $records_count, $group_id, $id]);

            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ไอดีไม่ถูกต้อง']);
                exit();
            }

            $stmt = $conn->prepare("DELETE FROM organizations_manual WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action ไม่ถูกต้อง']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
