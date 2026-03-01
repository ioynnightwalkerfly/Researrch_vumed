<?php
// api/officer_action.php
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

if ($action === 'officer_review') {
    $subAction = $_POST['sub_action']; // approve, return
    $projectId = $_POST['project_id'];
    $officerId = $_SESSION['user_id'];
    
    try {
        // Get Project and User Info
        $stmt = $conn->prepare("SELECT p.title_th, u.email, u.firstname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        $discord = new DiscordNotifier();

        if ($subAction === 'approve') {
            // Update Status → pending_secretary
            $sql = "UPDATE projects SET status = 'pending_secretary', return_reason = NULL, return_issues = NULL, rejected_by = NULL, approved_by_officer_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$officerId, $projectId]);
            
            // Discord → ห้อง passed_initial (แจ้งเลขา)
            $discord->notifyPassedInitial($project['title_th']);

        } elseif ($subAction === 'return') {
            $reason = $_POST['reason'];
            $issues = $_POST['issues']; // JSON string
            
            // Update Status → rejected
            $sql = "UPDATE projects SET status = 'rejected', return_reason = ?, return_issues = ?, rejected_by = 'officer' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$reason, $issues, $projectId]);
            
            // Discord → ห้อง new_submission
            $discord->notifyOfficerReturn($project['title_th'], $reason);
            
            // Email → แจ้งนักวิจัย
            $issuesArr = json_decode($issues, true) ?: [];
            sendRejectionEmail($project['email'], $project['title_th'], $reason, $issuesArr, 'เจ้าหน้าที่');
        }

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}
?>
