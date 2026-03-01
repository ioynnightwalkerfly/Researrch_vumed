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
            $issuesHtml = '<h3 style="color:#dc2626;margin-top:16px;">สิ่งที่ต้องแก้ไข:</h3><ul style="margin:8px 0;padding-left:20px;">';
            foreach ($issues as $issue) {
                $issuesHtml .= "<li style='margin:4px 0;'>$issue</li>";
            }
            $issuesHtml .= '</ul>';
        }

        // Build link
        $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'vumedhr.vu.ac.th';
        $link = "$protocol://$host/research/dashboard.php";

        $mail->isHTML(true);
        $mail->Subject = "โครงการวิจัยถูกตีกลับ - $projectTitle";
        $mail->Body = "
            <div style='font-family:Sarabun,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#dc2626;color:white;padding:20px;border-radius:12px 12px 0 0;text-align:center;'>
                    <h2 style='margin:0;'>⚠️ โครงการถูกตีกลับ</h2>
                </div>
                <div style='background:white;padding:24px;border:1px solid #e5e7eb;border-radius:0 0 12px 12px;'>
                    <p>เรียน ผู้วิจัย</p>
                    <p>โครงการวิจัยของคุณถูกตีกลับจาก <strong>{$rejectedBy}</strong></p>
                    
                    <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin:16px 0;'>
                        <p style='margin:0 0 8px 0;'><strong>ชื่อโครงการ:</strong> $projectTitle</p>
                        <p style='margin:0;'><strong>เหตุผล:</strong> $reason</p>
                    </div>
                    
                    $issuesHtml
                    
                    <p style='margin-top:24px;'>กรุณาเข้าสู่ระบบเพื่อแก้ไขและยื่นใหม่:</p>
                    <p style='text-align:center;margin:20px 0;'>
                        <a href='$link' style='background:#2563EB;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;'>เข้าสู่ระบบ</a>
                    </p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                    <p style='color:#9ca3af;font-size:12px;text-align:center;'>Research Portal System</p>
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
