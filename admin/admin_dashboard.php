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

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-blue-400">ADMIN PANEL</h1>
            <p class="text-xs text-gray-500 mt-1">System Control Center</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="admin_dashboard.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg transition shadow-md">
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
