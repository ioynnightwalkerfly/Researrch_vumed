<?php
session_start();
require_once '../api/db.php';

// Check Admin Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];

try {
    // Fetch Settings
    $stmt = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'meeting_system_enabled'");
    $meetingSystemEnabled = $stmt->fetchColumn() === '1';

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Research Portal</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-blue-400">ADMIN PANEL</h1>
            <p class="text-xs text-gray-500 mt-1">System Control Center</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="admin_dashboard.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-chart-pie w-6"></i>
                <span>ภาพรวมระบบ</span>
            </a>
            <a href="admin_users.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-users-gear w-6"></i>
                <span>จัดการผู้ใช้งาน</span>
            </a>
            <a href="admin_projects.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-folder-tree w-6"></i>
                <span>จัดการโครงการวิจัย</span>
            </a>
            
            <a href="admin_publication_stats.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-chart-donut w-6"></i>
                <span>จัดการสถิติภาพรวม</span>
            </a>
            <a href="admin_pr_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-bullhorn w-6"></i>
                <span>ข่าวประชาสัมพันธ์</span>
            </a>
            <a href="admin_activities.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-calendar-days w-6"></i>
                <span>กิจกรรมฝ่ายฯ</span>
            </a>
            <a href="admin_academic_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-handshake-angle w-6"></i>
                <span>ข่าวบริการวิชาการ</span>
            </a>
            
            <a href="../meeting_calendar.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-calendar-check w-6"></i>
                <span>จัดการนัดหมายการประชุม</span>
            </a>
            
            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Access Modes</div>
            <a href="../officer/dashboard.php" target="_blank" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-user-shield w-6"></i>
                <span>เข้าสู่โหมดเจ้าหน้าที่</span>
            </a>
             <!-- Future Secretary Link -->
             <!-- <a href="../secretary/dashboard.php" target="_blank" ...> -->

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="admin_settings.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-cogs w-6"></i>
                <span>ตั้งค่าระบบ</span>
            </a>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i>
                <span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
             <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                    <?php echo (function_exists('mb_substr')) ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1); ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-medium"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="text-xs text-green-400">Administrator</div>
                </div>
            </div>
            <a href="../api/logout.php" class="block w-full text-center py-2 rounded border border-gray-700 hover:bg-red-600 hover:border-red-600 hover:text-white transition text-gray-400 text-sm">
                <i class="fa-solid fa-sign-out-alt mr-2"></i> ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 shadow-sm">
            <h2 class="text-xl font-bold text-gray-800">ตั้งค่าระบบ (System Settings)</h2>
            <div class="text-sm text-gray-500">Updated: <?php echo date('d/m/Y H:i'); ?></div>
        </header>

        <!-- Stats Grid -->
        <div class="flex-grow overflow-y-auto p-8">

            <!-- System Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-700">ตั้งค่าระบบ (System Settings)</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between max-w-md">
                        <div>
                            <div class="font-medium text-gray-800">ระบบปฏิทินนัดหมายการประชุม</div>
                            <div class="text-sm text-gray-500">เปิด/ปิด การใช้งานระบบปฏิทินสำหรับทุกคน</div>
                        </div>
                        <div class="mt-2 text-right">
                            <select id="meetingSystemSelect" onchange="updateSetting('meeting_system_enabled', this.value)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                                <option value="1" <?php echo $meetingSystemEnabled ? 'selected' : ''; ?>>✅ เปิดใช้งาน (Enabled)</option>
                                <option value="0" <?php echo !$meetingSystemEnabled ? 'selected' : ''; ?>>❌ ปิดชั่วคราว (Disabled)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            
        </div>
    </main>

    <script>
    function updateSetting(key, value) {
        const formData = new FormData();
        formData.append('action', 'update_setting');
        formData.append('key', key);
        formData.append('value', value);

        fetch('../api/settings_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optional: Show a small toast notification instead of alert
                console.log('Setting updated successfully');
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
                // Revert select if failed
                document.getElementById('meetingSystemSelect').value = (value === '1' ? '0' : '1');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            document.getElementById('meetingSystemSelect').value = (value === '1' ? '0' : '1');
        });
    }
    </script>
</body>
</html>
