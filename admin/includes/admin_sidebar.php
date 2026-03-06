<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Define menu items
$menuItems = [
    'admin_dashboard.php' => ['icon' => 'fa-chart-pie', 'label' => 'ภาพรวมระบบ'],
    'admin_users.php' => ['icon' => 'fa-users-gear', 'label' => 'จัดการผู้ใช้งาน'],
    'admin_projects.php' => ['icon' => 'fa-folder-tree', 'label' => 'จัดการโครงการวิจัย'],
    'admin_publication_stats.php' => ['icon' => 'fa-chart-donut', 'label' => 'จัดการสถิติภาพรวม'],
    'admin_pr_news.php' => ['icon' => 'fa-bullhorn', 'label' => 'ข่าวประชาสัมพันธ์'],
    'admin_activities.php' => ['icon' => 'fa-calendar-days', 'label' => 'กิจกรรมฝ่ายฯ'],
    'admin_academic_news.php' => ['icon' => 'fa-handshake-angle', 'label' => 'ข่าวบริการวิชาการ'],
    'admin_organizations.php' => ['icon' => 'fa-network-wired', 'label' => 'องค์กรความร่วมมือ']
];

$meetingSystemEnabled = false;
try {
    // Determine meeting system state if this connection exists
    if (isset($conn)) {
        $stmt_setting = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'meeting_system_enabled'");
        $val = $stmt_setting->fetchColumn();
        if ($val !== false) {
            $meetingSystemEnabled = ($val === '1');
        }
    }
} catch (Exception $e) {}

// Use fallback username if not set in some pages
$uName = $userName ?? $_SESSION['fullname'] ?? 'Administrator';
?>

<!-- Guarantee Fonts & FontAwesome are loaded, resolving "font changes" issue -->
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>body { font-family: 'Sarabun', sans-serif !important; }</style>

<!-- Sidebar -->
<aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0 z-50">
    <div class="p-6 text-center border-b border-gray-800">
        <h1 class="text-xl font-bold tracking-wider text-blue-400">ADMIN PANEL</h1>
        <p class="text-xs text-gray-500 mt-1">System Control Center</p>
    </div>
    
    <nav class="flex-grow p-4 space-y-2 overflow-y-auto scrollbar-hide">
        <?php foreach ($menuItems as $url => $item): ?>
            <a href="<?php echo $url; ?>" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage === $url) ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                <i class="fa-solid <?php echo $item['icon']; ?> w-6"></i>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
        
        <?php if ($meetingSystemEnabled): ?>
            <a href="../meeting_calendar.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage === 'meeting_calendar.php') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                <i class="fa-solid fa-calendar-check w-6"></i>
                <span>จัดการนัดหมายการประชุม</span>
            </a>
        <?php endif; ?>

        <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Access Modes</div>
        <a href="../officer/dashboard.php" target="_blank" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
            <i class="fa-solid fa-user-shield w-6"></i>
            <span>โหมดเจ้าหน้าที่</span>
        </a>
        <a href="../secretary/dashboard.php" target="_blank" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
            <i class="fa-solid fa-file-contract w-6"></i>
            <span>โหมดเลขานุการ</span>
        </a>

        <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
        <a href="admin_settings.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage === 'admin_settings.php') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
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
            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                <?php echo (function_exists('mb_substr')) ? mb_substr($uName, 0, 1, 'UTF-8') : substr($uName, 0, 1); ?>
            </div>
            <div class="truncate">
                <div class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($uName); ?></div>
                <div class="text-xs text-green-400">Administrator</div>
            </div>
        </div>
        <a href="../api/logout.php" class="block w-full text-center py-2 rounded border border-gray-700 hover:bg-red-600 hover:border-red-600 hover:text-white transition text-gray-400 text-sm">
            <i class="fa-solid fa-sign-out-alt mr-2"></i> ออกจากระบบ
        </a>
    </div>
</aside>
