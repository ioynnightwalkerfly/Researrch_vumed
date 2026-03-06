<?php
session_start();
require_once '../api/db.php';

// Check Admin Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];

// Fetch Statistics
$meetingSystemEnabled = false;
try {
    // Total Projects
    $stmt = $conn->query("SELECT COUNT(*) FROM projects");
    $totalProjects = $stmt->fetchColumn();

    // Pending Officer
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'pending_officer'");
    $pendingOfficer = $stmt->fetchColumn();

    // Pending Secretary
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'pending_secretary'");
    $pendingSecretary = $stmt->fetchColumn();

    // Completed / Approved (Assuming 'approved' or 'completed')
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'approved' OR status = 'completed'");
    $completed = $stmt->fetchColumn();

    // Rejected
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'rejected'");
    $rejected = $stmt->fetchColumn();

    // Recent Projects
    $stmt = $conn->query("SELECT p.id, p.title_th, p.status, p.submission_date, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id ORDER BY p.submission_date DESC LIMIT 5");
    $recentProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <h2 class="text-xl font-bold text-gray-800">Dashboard Overview</h2>
            <div class="text-sm text-gray-500">Updated: <?php echo date('d/m/Y H:i'); ?></div>
        </header>

        <!-- Stats Grid -->
        <div class="flex-grow overflow-y-auto p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Total -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">โครงการทั้งหมด</div>
                        <div class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalProjects; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-folder"></i>
                    </div>
                </div>

                <!-- Pending Officer -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">รอเจ้าหน้าที่ตรวจสอบ</div>
                        <div class="text-3xl font-bold text-blue-600 mt-1"><?php echo $pendingOfficer; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>

                <!-- Pending Secretary -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">รอเลขานุการตรวจสอบ</div>
                        <div class="text-3xl font-bold text-purple-600 mt-1"><?php echo $pendingSecretary; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">ตีกลับ / แก้ไข</div>
                        <div class="text-3xl font-bold text-orange-500 mt-1"><?php echo $rejected; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                </div>
            </div>



            <!-- Recent Activity Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-700">โครงการวิจัยล่าสุด</h3>
                    <a href="admin_projects.php" class="text-sm text-blue-600 hover:underline">ดูทั้งหมด <i class="fa-solid fa-chevron-right text-xs"></i></a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white text-gray-500 font-semibold border-b">
                            <tr>
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">ชื่อโครงการ</th>
                                <th class="px-6 py-3">นักวิจัย</th>
                                <th class="px-6 py-3">สถานะ</th>
                                <th class="px-6 py-3 text-right">วันที่ยื่น</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($recentProjects) > 0): ?>
                                <?php foreach ($recentProjects as $p): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-mono text-gray-400">#<?php echo $p['id']; ?></td>
                                        <td class="px-6 py-4 max-w-xs truncate font-medium text-gray-800">
                                            <?php echo htmlspecialchars($p['title_th']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs font-bold border 
                                                <?php 
                                                    if ($p['status'] == 'approved') echo 'bg-green-100 text-green-700 border-green-200';
                                                    elseif ($p['status'] == 'rejected') echo 'bg-red-100 text-red-700 border-red-200';
                                                    elseif (strpos($p['status'], 'pending') !== false) echo 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                                    else echo 'bg-gray-100 text-gray-600 border-gray-200';
                                                ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-500">
                                            <?php echo date('d M Y', strtotime($p['submission_date'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">ยังไม่มีข้อมูลโครงการ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                // Revert toggle if failed
                document.getElementById('toggleMeetingSystem').checked = (value === '0');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            document.getElementById('toggleMeetingSystem').checked = (value === '0');
        });
    }
    </script>
</body>
</html>
