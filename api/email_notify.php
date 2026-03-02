<?php
// api/email_notify.php - Email Notification via PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;

function sendRejectionEmail($to, $projectTitle, $reason, $issues = [], $rejectedBy = 'เจ้าหน้าที่') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'oopoo456123@gmail.com';
        $mail->Password   = 'kekf lesm czud zqeq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($mail->Username, 'Research Portal System');
        $mail->addAddress($to);

        // Build issues HTML
        $issuesHtml = '';
        if (!empty($issues)) {
            $issuesHtml = '
            <div style="background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:20px; margin:20px 0;">
                <h3 style="color:#c2410c; margin-top:0; margin-bottom:12px; font-size:16px;">
                    <span style="margin-right:8px;">⚠️</span>เอกสารที่ต้องแก้ไข / ส่งเพิ่มเติม:
                </h3>
                <ul style="margin:0; padding-left:24px; color:#431407;">';
            foreach ($issues as $issue) {
                $issuesHtml .= "<li style='margin-bottom:8px; line-height:1.5;'><strong>$issue</strong></li>";
            }
            $issuesHtml .= '</ul>
            </div>';
        }

        // Build link
        $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'vumedhr.vu.ac.th';
        $link = "$protocol://$host/research/dashboard.php";

        $mail->isHTML(true);
        $mail->Subject = "โครงการวิจัยถูกตีกลับให้แก้ไขเอกสาร - $projectTitle";
        $mail->Body = "
            <div style='font-family:Sarabun,sans-serif;max-width:600px;margin:0 auto;background:#f9fafb;padding:20px;'>
                <div style='background:#ea580c;color:white;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                    <h2 style='margin:0;font-size:24px;'>⚠️ แจ้งเตือน: เอกสารไม่ครบ/ต้องแก้ไข</h2>
                </div>
                <div style='background:white;padding:32px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 12px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);'>
                    <p style='font-size:16px;color:#374151;'>เรียน ผู้วิจัย,</p>
                    <p style='font-size:16px;color:#374151;line-height:1.6;'>
                        แบบเสนอโครงการวิจัยของคุณถูกพิจารณาโดย <strong>{$rejectedBy}</strong> และพบว่า <span style='color:#ea580c;font-weight:bold;'>เอกสารยังไม่ครบถ้วน หรือมีจุดที่ต้องแก้ไข</span>
                    </p>
                    
                    <div style='background:#f3f4f6;border-radius:8px;padding:16px;margin:20px 0;border-left:4px solid #3b82f6;'>
                        <p style='margin:0 0 8px 0;color:#1f2937;'><strong>ชื่อโครงการ:</strong> <span style='color:#4b5563;'>$projectTitle</span></p>
                        " . ($reason ? "<p style='margin:0;color:#1f2937;'><strong>รายละเอียดเพิ่มเติมจากเจ้าหน้าที่:</strong> <span style='color:#4b5563;'>$reason</span></p>" : "") . "
                    </div>
                    
                    $issuesHtml
                    
                    <p style='margin-top:24px;font-size:16px;color:#374151;'>กรุณาเข้าสู่ระบบเพื่อดำเนินการแก้ไขและแนบเอกสารให้ครบถ้วน จากนั้นกดยื่นใหม่อีกครั้ง</p>
                    <p style='text-align:center;margin:32px 0;'>
                        <a href='$link' style='background:#2563EB;color:white;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;font-size:16px;box-shadow:0 2px 4px rgba(37,99,235,0.2);'>เข้าสู่ระบบเพื่อแก้ไข</a>
                    </p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:32px 0 20px 0;'>
                    <p style='color:#9ca3af;font-size:13px;text-align:center;margin:0;'>ส่งจากระบบ Research Portal System<br>กรุณาอย่าตอบกลับอีเมลนี้</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Rejection Email Error: " . $e->getMessage());
        return false;
    }
}
?>
