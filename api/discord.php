<?php
// api/discord.php - Multi-Webhook Discord Notification System

class DiscordNotifier {
    
    private $webhooks = [
        'new_submission'     => 'https://discord.com/api/webhooks/1473968498341318849/fNcMHhoZ5hn3J5VLMX8qABhYJqgaZVxfj5o1tAky_mb3PqF5YGVNWV8zJicKx0l_1G1v',
        'tracking'           => 'https://discord.com/api/webhooks/1473968603278872696/kNc0-76-36Ue3vhnQgNg1ElmrUlIC7jEohT_3-d0gvqgYQTHoqXSz7xJFIcJcO6tV2Xp',
        'secretary_rejected' => 'https://discord.com/api/webhooks/1473968980690735125/Jsip3dTKTEmjgEOCCLDRhFGohqL_m4s28KM52vLrhAHtF7lwNjdX_ukRYQ3_M_GeWTqY',
        'passed_initial'     => 'https://discord.com/api/webhooks/1473969123510845502/QRgj2mRCyn17_CMi9KuNjzCw3FGqTk908xmutaZoAASgUK3nANcQJn9jjJ0-TOTkNFrh',
    ];

    /**
     * Send message to a specific Discord channel
     * @param string $channel  Channel key: new_submission, tracking, secretary_rejected, passed_initial
     * @param string $message  Message content (markdown)
     * @param int    $color    Embed color (hex)
     */
    public function sendTo($channel, $message, $color = 0x3498db) {
        $url = $this->webhooks[$channel] ?? null;
        if (!$url) return false;
        return $this->sendToUrl($url, $message, $color);
    }

    /**
     * Legacy send() method - sends to new_submission channel by default
     */
    public function send($message, $color = 0x3498db) {
        return $this->sendTo('new_submission', $message, $color);
    }

    private function sendToUrl($url, $message, $color) {
        $json_data = json_encode([
            "content" => "",
            "embeds" => [
                [
                    "description" => $message,
                    "color" => $color,
                    "footer" => [
                        "text" => "Research Portal Notification",
                        "icon_url" => "https://cdn-icons-png.flaticon.com/512/2991/2991148.png"
                    ],
                    "timestamp" => date("c")
                ]
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    // ─── Convenience Methods ───────────────────────────────────────

    /** แจ้งเตือนโครงการใหม่ → ห้อง new_submission */
    public function notifyNewSubmission($projectTitle, $researcherName) {
        $msg = "📄 **มีการยื่นข้อเสนอโครงการวิจัยใหม่**\n";
        $msg .= "**เรื่อง:** $projectTitle\n";
        $msg .= "**โดย:** $researcherName\n";
        $msg .= "กรุณาตรวจสอบที่ระบบเจ้าหน้าที่";
        return $this->sendTo('new_submission', $msg, 0x2ecc71);
    }

    /** เจ้าหน้าที่ตีกลับ → ห้อง new_submission */
    public function notifyOfficerReturn($projectTitle, $reason) {
        $msg = "⚠️ **โครงการถูกตีกลับจากเจ้าหน้าที่**\n";
        $msg .= "**เรื่อง:** $projectTitle\n";
        $msg .= "**เหตุผล:** $reason\n";
        return $this->sendTo('new_submission', $msg, 0xe67e22);
    }

    /** เจ้าหน้าที่อนุมัติ (ผ่านขั้นต้น) → ห้อง passed_initial */
    public function notifyPassedInitial($projectTitle) {
        $msg = "✅ **โครงการผ่านการตรวจสอบเบื้องต้นแล้ว**\n";
        $msg .= "**เรื่อง:** $projectTitle\n";
        $msg .= "รอเลขานุการตรวจสอบขั้นถัดไป";
        return $this->sendTo('passed_initial', $msg, 0x3498db);
    }

    /** เลขาตีกลับ → ห้อง secretary_rejected + แจ้งเจ้าหน้าที่ */
    public function notifySecretaryReturn($projectTitle, $reason) {
        $msg = "🔴 **โครงการถูกตีกลับจากเลขานุการ**\n";
        $msg .= "**เรื่อง:** $projectTitle\n";
        $msg .= "**เหตุผล:** $reason\n";
        $msg .= "รายการได้ถูกส่งกลับไปยังนักวิจัยแล้ว";
        return $this->sendTo('secretary_rejected', $msg, 0xe74c3c);
    }

    /** ติดตาม Deadline → ห้อง tracking */
    public function notifyDeadlineWarning($projectTitle, $daysPassed, $daysLeft) {
        $msg = "⏳ **แจ้งเตือนติดตามงาน (ครบกำหนด 10 วัน)**\n";
        $msg .= "**โครงการ:** $projectTitle\n";
        $msg .= "**ระยะเวลา:** ผ่านมาแล้ว $daysPassed วัน (เหลือ $daysLeft วัน)\n";
        $msg .= "กรุณาติดตามสถานะการแก้ไขจากนักวิจัย";
        return $this->sendTo('tracking', $msg, 0xe74c3c);
    }

    /** อนุมัติขั้นสุดท้าย → ห้อง passed_initial */
    public function notifyFinalApproval($projectTitle) {
        $msg = "🎉 **โครงการได้รับอนุมัติขั้นสุดท้ายแล้ว!**\n";
        $msg .= "**เรื่อง:** $projectTitle\n";
        return $this->sendTo('passed_initial', $msg, 0x27ae60);
    }

    // Legacy methods (backwards compatible)
    public function notifyReturn($projectTitle, $reason) {
        return $this->notifyOfficerReturn($projectTitle, $reason);
    }

    public function notifyApproval($projectTitle, $stage) {
        return $this->notifyPassedInitial($projectTitle);
    }
}
?>
