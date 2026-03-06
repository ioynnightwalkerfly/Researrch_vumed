<?php
session_start();
require_once '../api/db.php';

// Check Admin Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];

$meetingSystemEnabled = false;
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

    <?php include 'includes/admin_sidebar.php'; ?>

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
