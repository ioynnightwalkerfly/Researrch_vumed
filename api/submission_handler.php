<?php
// api/submission_handler.php
session_start();
require_once 'db.php';

// PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;

ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

function sendInvitationEmail($email, $name, $role, $projectTitle, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // 🔴🔴🔴 YOUR EMAIL CREDENTIALS HERE 🔴🔴🔴
        $mail->Username   = 'oopoo456123@gmail.com'; 
        $mail->Password   = 'kekf lesm czud zqeq';
        // ------------------------------------------
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($mail->Username, 'Research Portal System');
        $mail->addAddress($email, $name);

        $roleText = ($role == 'co_researcher') ? 'ผู้ร่วมวิจัย (Co-Researcher)' : 'ที่ปรึกษาโครงการ (Advisor)';
        
        // Link to response page
        $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname(dirname($_SERVER['PHP_SELF'])); // Go up one level from /api/
        $link = "$protocol://$host$path/respond_invite.php?token=$token";

        $mail->isHTML(true);
        $mail->Subject = "คำเชิญเข้าร่วมโครงการวิจัย: $projectTitle";
        $mail->Body    = "
            <h2>เรียนคุณ $name</h2>
            <p>คุณได้รับเชิญให้เข้าร่วมโครงการวิจัยเรื่อง <b>\"$projectTitle\"</b></p>
            <p>ในฐานะ: <b>$roleText</b></p>
            <p>กรุณาคลิกลิงก์ด้านล่างเพื่อ ตอบรับ หรือ ปฏิเสธ คำเชิญ:</p>
            <p><a href='$link' style='background:#2563EB;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>ดูรายละเอียดและตอบรับ</a></p>
            <p><small>หากลิงก์ไม่ทำงาน: $link</small></p>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false; // For now just return false, don't crash
    }
}

try {
    // --------------------------------------------------------
    // ACTION: SAVE DRAFT
    // --------------------------------------------------------
    if ($action === 'save_draft') {
        $projectId = $_POST['project_id'] ?? null;
        if(empty($_POST['title_th'])) throw new Exception("Title required");
        
        $fields = [
            'research_type', 'title_th', 'title_en', 'source_funds', 
            'funder_name', 'volunteers_under_18', 'sponsor_project_id', 
            'doc_number', 'doc_date'
        ];
        
        // Collect params
        $params = [];
        foreach($fields as $f) {
            $val = $_POST[$f] ?? null;
            $params[] = ($val === '') ? null : $val;
        }

        if ($projectId) {
            $sql = "UPDATE projects SET research_type=?, title_th=?, title_en=?, source_funds=?, funder_name=?, volunteers_under_18=?, sponsor_project_id=?, doc_number=?, doc_date=? WHERE id=? AND user_id=?";
            $params[] = $projectId;
            $params[] = $userId;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "INSERT INTO projects (research_type, title_th, title_en, source_funds, funder_name, volunteers_under_18, sponsor_project_id, doc_number, doc_date, user_id, status) VALUES (?,?,?,?,?,?,?,?,?,?, 'draft')";
            $params[] = $userId;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $projectId = $conn->lastInsertId();
        }
        echo json_encode(['success' => true, 'project_id' => $projectId]);

    // --------------------------------------------------------
    // ACTION: UPLOAD FILE
    // --------------------------------------------------------
    } elseif ($action === 'upload_file') {
        $projectId = $_POST['project_id'];
        $docType = $_POST['doc_type_id']; // 1-6
        
        if (!$projectId) throw new Exception("Project ID missing");
        if (!isset($_FILES['file'])) throw new Exception("No file uploaded");
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errMessages = [
                UPLOAD_ERR_INI_SIZE   => 'ขนาดไฟล์เกินที่กำหนดใน php.ini',
                UPLOAD_ERR_FORM_SIZE  => 'ขนาดไฟล์เกินที่ฟอร์มกำหนด',
                UPLOAD_ERR_PARTIAL    => 'ไฟล์ถูกอัปโหลดเพียงบางส่วน',
                UPLOAD_ERR_NO_FILE    => 'ไม่มีไฟล์ถูกอัปโหลด',
                UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราวบนเซิร์ฟเวอร์',
                UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ลงบนดิสก์ได้',
                UPLOAD_ERR_EXTENSION  => 'การอัปโหลดหยุดชะงักเกิดจาก PHP extension'
            ];
            $msg = $errMessages[$file['error']] ?? 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ (' . $file['error'] . ')';
            throw new Exception("Upload Error: " . $msg);
        }

        // Prepare Dir
        $targetDir = "../uploads/projects/$projectId/";
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                throw new Exception("Failed to create directory: $targetDir");
            }
        }

        // Security: Limit file size to 10MB (10 * 1024 * 1024)
        if ($file['size'] > 10485760) {
            throw new Exception("ขนาดไฟล์ต้องไม่เกิน 10MB");
        }

        /* 
        // ปิดการตรวจสอบ MIME type ชั่วคราวตามที่ขอ
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = [
            'application/pdf', 
            'image/jpeg', 
            'image/png', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception("ประเภทไฟล์ไม่รองรับ (รับเฉพาะ PDF, Word, JPG, PNG)");
        }
        */

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = "doc_{$docType}_" . time() . ".$ext";
        $targetPath = $targetDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // DB Insert/Update
            // Check if exists
            $check = $conn->prepare("SELECT id FROM project_documents WHERE project_id = ? AND doc_type = ?");
            $check->execute([$projectId, $docType]);
            
            if ($check->rowCount() > 0) {
                $sql = "UPDATE project_documents SET file_path = ?, doc_name = ?, uploaded_at = NOW() WHERE project_id = ? AND doc_type = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$safeName, $file['name'], $projectId, $docType]);
            } else {
                $sql = "INSERT INTO project_documents (project_id, doc_type, file_path, doc_name, uploaded_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$projectId, $docType, $safeName, $file['name']]);
            }
            echo json_encode(['success' => true, 'file_name' => $file['name']]);
        } else {
            throw new Exception("File upload failed");
        }

    // --------------------------------------------------------
    // ACTION: INVITE TEAM
    // --------------------------------------------------------
    } elseif ($action === 'invite_team') {
        $projectId = $_POST['project_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        // Check dup
        $check = $conn->prepare("SELECT id FROM project_team WHERE project_id = ? AND email = ?");
        $check->execute([$projectId, $email]);
        if ($check->rowCount() > 0) throw new Exception("Already invited");

        $token = bin2hex(random_bytes(32));

        $sql = "INSERT INTO project_team (project_id, firstname, email, role, response_status, token, invited_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId, $name, $email, $role, $token]);
        $memberId = $conn->lastInsertId();

        // Get Project Title for Email
        $pStmt = $conn->prepare("SELECT title_th FROM projects WHERE id = ?");
        $pStmt->execute([$projectId]);
        $project = $pStmt->fetch();
        $projectTitle = $project['title_th'] ?? 'Untitled Project';

        // Send Email
        sendInvitationEmail($email, $name, $role, $projectTitle, $token);

        echo json_encode(['success' => true, 'member_id' => $memberId]);

    // --------------------------------------------------------
    // ACTION: SUBMIT PROJECT
    // --------------------------------------------------------
    } elseif ($action === 'submit_project') {
        $projectId = $_POST['project_id'];
        
        // Update Status
        // Update Status
        $sql = "UPDATE projects SET status = 'pending_officer', submission_date = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId, $userId]);

        // Get Project Details for Discord
        $pStmt = $conn->prepare("SELECT title_th FROM projects WHERE id = ?");
        $pStmt->execute([$projectId]);
        $project = $pStmt->fetch();
        
        // Notification
        require_once 'discord.php';
        $discord = new DiscordNotifier();
        $discord->notifyNewSubmission($project['title_th'], $_SESSION['fullname'] ?? 'Unknown');
        
        echo json_encode(['success' => true]);

    } elseif ($action === 'search_users') {
        $keyword = $_POST['keyword'] ?? '';
        if (strlen($keyword) < 2) {
            echo json_encode(['success' => true, 'users' => []]);
            exit;
        }
        $search = "%$keyword%";
        $stmt = $conn->prepare("SELECT id, firstname_th, lastname_th, email, role_researcher, role_coordinator FROM users WHERE (firstname_th LIKE ? OR lastname_th LIKE ? OR email LIKE ?) LIMIT 10");
        $stmt->execute([$search, $search, $search]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);

    } elseif ($action === 'get_team_status') {
         $projectId = $_POST['project_id'];
         $stmt = $conn->prepare("SELECT id, firstname, email, role, response_status, response_date FROM project_team WHERE project_id = ?");
         $stmt->execute([$projectId]);
         $team = $stmt->fetchAll(PDO::FETCH_ASSOC);
         echo json_encode(['success' => true, 'team' => $team]);

    } elseif ($action === 'delete_team_member') {
        $memberId = $_POST['member_id'];
        $conn->prepare("DELETE FROM project_team WHERE id = ?")->execute([$memberId]);
        echo json_encode(['success' => true]);

    } elseif ($action === 'get_project') {
        $projectId = $_POST['project_id'];
        
        // 1. Get Project
        $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) throw new Exception("Project not found");
        
        // 2. Get Documents
        $stmtDocs = $conn->prepare("SELECT doc_type, file_path, uploaded_at FROM project_documents WHERE project_id = ?");
        $stmtDocs->execute([$projectId]);
        $documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Get Team
        $stmtTeam = $conn->prepare("SELECT id, firstname, email, role, response_status, response_date FROM project_team WHERE project_id = ?");
        $stmtTeam->execute([$projectId]);
        $team = $stmtTeam->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'project' => $project,
            'documents' => $documents,
            'team' => $team
        ]);

    } elseif ($action === 'simulate_accept') {
         $memberId = $_POST['member_id'];
         $conn->prepare("UPDATE project_team SET response_status='accepted' WHERE id=?")->execute([$memberId]);
         echo json_encode(['success' => true]);

    } elseif ($action === 'delete_project') {
         $projectId = $_POST['project_id'];
         // Verify ownership
         try {
             $conn->beginTransaction();
             
             // Check ownership OR Admin role
             if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                 $stmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
                 $stmt->execute([$projectId]);
             } else {
                 $stmt = $conn->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
                 $stmt->execute([$projectId, $userId]);
             }
             
             if (!$stmt->fetch()) throw new Exception("Access Denied or Not Found");
             
             // Delete Dependencies (If no FK Cascade)
             $conn->prepare("DELETE FROM project_documents WHERE project_id = ?")->execute([$projectId]);
             $conn->prepare("DELETE FROM project_team WHERE project_id = ?")->execute([$projectId]);
             
             // Delete Project
             $conn->prepare("DELETE FROM projects WHERE id = ?")->execute([$projectId]);
             
             $conn->commit();
             echo json_encode(['success' => true]);
         } catch (Exception $e) {
             $conn->rollBack();
             throw $e;
         }

    } else {
        throw new Exception("Invalid Action");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
