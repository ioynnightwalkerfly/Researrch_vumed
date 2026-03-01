<?php
// respond_invite.php
require_once 'api/db.php';

$token = $_GET['token'] ?? '';
$action = $_POST['action'] ?? '';
$message = '';

if ($token) {
    // Check Token
    $stmt = $conn->prepare("SELECT pt.id, pt.firstname, pt.role, pt.response_status, p.title_th 
                            FROM project_team pt 
                            JOIN projects p ON pt.project_id = p.id 
                            WHERE pt.token = ?");
    $stmt->execute([$token]);
    $invite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invite) {
        $message = "ลิงก์ไม่ถูกต้องหรือหมดอายุ";
    } elseif ($action) {
        // Process Accept/Reject
        $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';
        $update = $conn->prepare("UPDATE project_team SET response_status = ?, response_date = NOW() WHERE id = ?");
        $update->execute([$newStatus, $invite['id']]);
        
        $statusText = ($newStatus === 'accepted') ? "ตอบรับ" : "ปฏิเสธ";
        $message = "บันทึกการ{$statusText}เรียบร้อยแล้ว ขอบคุณครับ";
        $invite['response_status'] = $newStatus; // Update local var for display
    }
} else {
    $message = "ไม่พบ Token";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตอบรับคำเชิญเข้าร่วมโครงการ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg bg-blue-50 text-blue-800">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($invite): ?>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">คำเชิญเข้าร่วมโครงการ</h1>
            <p class="text-gray-600 mb-6">โครงการ: <b><?php echo htmlspecialchars($invite['title_th']); ?></b></p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <p><b>เรียนคุณ:</b> <?php echo htmlspecialchars($invite['firstname']); ?></p>
                <p><b>บทบาท:</b> <?php echo ($invite['role']=='co_researcher') ? 'ผู้ร่วมวิจัย' : 'ที่ปรึกษา'; ?></p>
                <p><b>สถานะปัจจุบัน:</b> 
                    <?php 
                        if($invite['response_status'] == 'pending') echo '<span class="text-yellow-600">รอการตอบรับ</span>';
                        if($invite['response_status'] == 'accepted') echo '<span class="text-green-600">ตอบรับแล้ว</span>';
                        if($invite['response_status'] == 'rejected') echo '<span class="text-red-600">ปฏิเสธแล้ว</span>';
                    ?>
                </p>
            </div>

            <?php if ($invite['response_status'] === 'pending'): ?>
                <form method="POST" class="flex gap-4 justify-center">
                    <button type="submit" name="action" value="accept" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 shadow font-bold w-full transition transform hover:scale-105">
                        <i class="fa-solid fa-check mr-1"></i> ยินดีเข้าร่วม
                    </button>
                    <button type="submit" name="action" value="reject" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 font-bold w-full transition">
                        ปฏิเสธ
                    </button>
                </form>
            <?php else: ?>
                <div class="text-gray-500 text-sm">คุณได้ทำการตอบรับหรือปฏิเสธคำเชิญนี้ไปแล้ว</div>
            <?php endif; ?>

        <?php else: ?>
             <div class="text-red-500 font-bold text-xl"><i class="fa-solid fa-circle-xmark text-4xl mb-4 text-red-400 block"></i> ไม่พบข้อมูลคำเชิญ</div>
        <?php endif; ?>
    </div>

</body>
</html>
