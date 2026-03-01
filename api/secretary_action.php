<?php
// api/secretary_action.php
session_start();
require_once 'db.php';
require_once 'discord.php';
require_once 'email_notify.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'secretary_review') {
        $subAction = $_POST['sub_action']; // approve, return, final_approve
        $projectId = $_POST['project_id'];
        $secretaryId = $_SESSION['user_id'];
        
        // Get Project and User Info
        $stmt = $conn->prepare("SELECT p.*, u.email, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) throw new Exception('Project not found');
        
        $discord = new DiscordNotifier();

        if ($subAction === 'approve') {
            // ผ่าน → pending_chairman
            $sql = "UPDATE projects SET status = 'pending_chairman', return_reason = NULL, return_issues = NULL, rejected_by = NULL, approved_by_secretary_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$secretaryId, $projectId]);
            
            echo json_encode(['success' => true, 'message' => 'ส่งต่อประธานเรียบร้อย']);

        } elseif ($subAction === 'return') {
            $reason = $_POST['reason'] ?? '';
            $issues = $_POST['issues'] ?? '[]'; // JSON string
            
            // ตีกลับ → rejected_secretary
            $sql = "UPDATE projects SET status = 'rejected_secretary', return_reason = ?, return_issues = ?, rejected_by = 'secretary' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$reason, $issues, $projectId]);
            
            // Discord → ห้อง secretary_rejected
            $discord->notifySecretaryReturn($project['title_th'], $reason);
            
            // Email → ส่งแจ้งนักวิจัย
            $issuesArr = json_decode($issues, true) ?: [];
            sendRejectionEmail($project['email'], $project['title_th'], $reason, $issuesArr, 'เลขานุการ');
            
            echo json_encode(['success' => true, 'message' => 'ตีกลับเรียบร้อย']);

        } elseif ($subAction === 'final_approve') {
            // อนุมัติขั้นสุดท้าย (แทนประธาน)
            $sql = "UPDATE projects SET status = 'approved', final_approved_at = NOW(), approved_by_secretary_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$secretaryId, $projectId]);
            
            // Discord → แจ้งอนุมัติ
            $discord->notifyFinalApproval($project['title_th']);
            
            echo json_encode(['success' => true, 'message' => 'อนุมัติขั้นสุดท้ายเรียบร้อย']);

        } elseif ($subAction === 'chairman_return') {
            // ตีกลับจากประธาน → rejected_secretary (ส่งกลับนักวิจัย)
            $reason = $_POST['reason'] ?? '';
            $issues = $_POST['issues'] ?? '[]';
            
            $sql = "UPDATE projects SET status = 'rejected_secretary', return_reason = ?, return_issues = ?, rejected_by = 'chairman' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$reason, $issues, $projectId]);
            
            // Discord → ห้อง secretary_rejected
            $discord->notifySecretaryReturn($project['title_th'], 'ประธาน: ' . $reason);
            
            // Email → แจ้งนักวิจัย
            $issuesArr = json_decode($issues, true) ?: [];
            sendRejectionEmail($project['email'], $project['title_th'], $reason, $issuesArr, 'ประธาน');
            
            echo json_encode(['success' => true, 'message' => 'ตีกลับจากประธานเรียบร้อย']);

        } else {
            throw new Exception('Invalid sub_action');
        }

    } else {
        throw new Exception('Invalid Action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
