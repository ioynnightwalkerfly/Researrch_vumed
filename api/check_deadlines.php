<?php
// api/check_deadlines.php - Deadline checker & reminder sender
session_start();
require_once 'db.php';
require_once 'discord.php';
require_once 'email_notify.php';

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'check';
    $discord = new DiscordNotifier();

    if ($action === 'send_deadline_reminder') {
        // Send reminder for a single project
        $projectId = $_POST['project_id'];
        
        $stmt = $conn->prepare("
            SELECT p.*, u.email, u.firstname_th, u.lastname_th, 
                   DATEDIFF(NOW(), p.updated_at) as days_since
            FROM projects p JOIN users u ON p.user_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) throw new Exception('Project not found');
        
        $days = $project['days_since'];
        $name = $project['firstname_th'] . ' ' . $project['lastname_th'];
        
        // Send email reminder
        sendDeadlineReminderEmail($project['email'], $project['title_th'], $days, $project['return_reason'] ?? '');
        
        // Discord notification
        $msg = "⏰ **แจ้งเตือน Deadline**\n";
        $msg .= "**โครงการ:** {$project['title_th']}\n";
        $msg .= "**ผู้วิจัย:** {$name}\n";
        $msg .= "**ตีกลับมาแล้ว:** {$days} วัน\n";
        $msg .= "กรุณาติดตามให้นักวิจัยส่งแก้ไข";
        $discord->sendTo('tracking', $msg, 0xe67e22);
        
        echo json_encode(['success' => true, 'message' => "Reminder sent to {$project['email']}"]);

    } elseif ($action === 'send_all_reminders') {
        // Send reminders for all overdue projects
        $deadlineDays = (int)($_POST['deadline_days'] ?? 10);
        
        $stmt = $conn->prepare("
            SELECT p.*, u.email, u.firstname_th, u.lastname_th, 
                   DATEDIFF(NOW(), p.updated_at) as days_since
            FROM projects p JOIN users u ON p.user_id = u.id 
            WHERE p.status IN ('rejected', 'rejected_secretary')
            AND DATEDIFF(NOW(), p.updated_at) >= ?
        ");
        $stmt->execute([$deadlineDays]);
        $overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($overdue as $project) {
            $days = $project['days_since'];
            sendDeadlineReminderEmail($project['email'], $project['title_th'], $days, $project['return_reason'] ?? '');
            $count++;
        }
        
        // Discord summary
        if ($count > 0) {
            $msg = "⏰ **แจ้งเตือน Deadline รวม**\n";
            $msg .= "ส่งแจ้งเตือนไปยังนักวิจัย **{$count}** ราย\n";
            $msg .= "โครงการที่เกิน {$deadlineDays} วัน";
            $discord->sendTo('tracking', $msg, 0xe74c3c);
        }
        
        echo json_encode(['success' => true, 'count' => $count]);

    } else {
        // Default: check and send (legacy GET request)
        $stmt = $conn->prepare("
            SELECT p.*, u.email, u.firstname_th, u.lastname_th
            FROM projects p JOIN users u ON p.user_id = u.id
            WHERE p.status IN ('rejected', 'rejected_secretary')
            AND DATEDIFF(NOW(), p.updated_at) >= 8
        ");
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $alerts = 0;
        foreach ($projects as $p) {
            $days = (int)((time() - strtotime($p['updated_at'])) / 86400);
            $msg = "⏰ **Deadline ใกล้ครบกำหนด**\n";
            $msg .= "**โครงการ:** {$p['title_th']}\n";
            $msg .= "**ผู้วิจัย:** {$p['firstname_th']} {$p['lastname_th']}\n";
            $msg .= "**ตีกลับมาแล้ว:** {$days} วัน";
            $discord->sendTo('tracking', $msg, 0xe74c3c);
            $alerts++;
        }
        
        echo json_encode(['success' => true, 'alerts_sent' => $alerts]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Helper: Send deadline reminder email
function sendDeadlineReminderEmail($to, $projectTitle, $days, $reason) {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'oopoo456123@gmail.com';
        $mail->Password   = 'kekf lesm czud zqeq';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($mail->Username, 'Research Portal System');
        $mail->addAddress($to);

        $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'vumedhr.vu.ac.th';
        $link = "$protocol://$host/research/dashboard.php";

        $mail->isHTML(true);
        $mail->Subject = "⏰ แจ้งเตือน: กรุณาส่งแก้ไขโครงการวิจัย - $projectTitle";
        $mail->Body = "
            <div style='font-family:Sarabun,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#f59e0b;color:white;padding:20px;border-radius:12px 12px 0 0;text-align:center;'>
                    <h2 style='margin:0;'>⏰ แจ้งเตือน Deadline</h2>
                </div>
                <div style='background:white;padding:24px;border:1px solid #e5e7eb;border-radius:0 0 12px 12px;'>
                    <p>เรียน ผู้วิจัย</p>
                    <p>โครงการวิจัยของคุณถูกตีกลับมาแล้ว <strong style='color:#dc2626;'>{$days} วัน</strong></p>
                    
                    <div style='background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:16px;margin:16px 0;'>
                        <p style='margin:0 0 8px 0;'><strong>ชื่อโครงการ:</strong> $projectTitle</p>
                        " . ($reason ? "<p style='margin:0;'><strong>เหตุผลที่ตีกลับ:</strong> $reason</p>" : "") . "
                    </div>
                    
                    <p style='color:#dc2626;font-weight:bold;'>กรุณาแก้ไขและส่งใหม่โดยเร็วที่สุด</p>
                    
                    <p style='text-align:center;margin:20px 0;'>
                        <a href='$link' style='background:#2563EB;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;'>เข้าสู่ระบบแก้ไข</a>
                    </p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                    <p style='color:#9ca3af;font-size:12px;text-align:center;'>Research Portal System</p>
                </div>
            </div>
        ";
        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Deadline Email Error: " . $e->getMessage());
        return false;
    }
}
?>
