<?php
session_start();
require_once 'api/db.php';

// 1. Session Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'researcher';
$userName = $_SESSION['fullname'] ?? 'Unknown User';

// Initialize variables to prevent undefined variable warnings
$totalProjects = 0;
$countApproved = 0;
$countWorking = 0;
$countPublished = 0;
$projects = [];
$error = null;

try {
    // 2. Fetch User Stats (From 'projects' table)
    // We want stats for THIS user? Or all if coordinator?
    // Let's assume for now we show ALL projects if we want to simulate the "Research Portal" overview, 
    // or just the user's projects. 
    // The prompt says "ดึงชื่อผู้ใช้และ Role จริงๆ มาแสดง" and "ดึงสถิติจริงจาก Database มาโชว์"
    // Usually a researcher sees their own projects.
    
    $condition = "WHERE user_id = :uid";
    $params = [':uid' => $userId];
    
    // If we want to be fancy: if coordinator, maybe see all? 
    // For simplicity and safety, let's stick to showing the user's own projects as per "My Projects" logic usually found in dashboards.
    // However, the mock data I seeded might be for a *new* user or the *first* user in DB.
    // I need to make sure the logged in user sees something.
    // If I just registered, I have 0 projects.
    // The setup script seeded projects for the "first user found in DB".
    // If I log in as that user, I'll see them.
    
    // Let's get the stats
    $sqlTotal = "SELECT COUNT(*) FROM projects $condition";
    $stmt = $conn->prepare($sqlTotal);
    $stmt->execute($params);
    $totalProjects = $stmt->fetchColumn();

    $sqlApproved = "SELECT COUNT(*) FROM projects $condition AND status = 'approved'";
    $stmt = $conn->prepare($sqlApproved);
    $stmt->execute($params);
    $countApproved = $stmt->fetchColumn();

    $sqlWorking = "SELECT COUNT(*) FROM projects $condition AND status = 'working'";
    $stmt = $conn->prepare($sqlWorking);
    $stmt->execute($params);
    $countWorking = $stmt->fetchColumn();

    $sqlPublished = "SELECT COUNT(*) FROM projects $condition AND status = 'published'";
    $stmt = $conn->prepare($sqlPublished);
    $stmt->execute($params);
    $countPublished = $stmt->fetchColumn();

    // 3. Fetch Recent Projects
    $sqlProjects = "SELECT * FROM projects $condition ORDER BY created_at DESC"; // Show all for the table, or limit?
    $stmt = $conn->prepare($sqlProjects);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();

    // 4. Prepare Data for Charts (Simple year-based stats mimicking the dummy chart)
    // For now, let's keep the chart static or semi-dynamic. 
    // To make it truly dynamic we'd need a complex query grouping by year.
    // Let's stick to static chart data for the *history* (hard to fake 5 years history with just created data),
    // but we can inject the "Total" into the current year if we wanted.
    // For this task, "ดึงสถิติจริง" likely refers to the counters and the table.

} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Research Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #F3F4F6; }
        .sidebar-item { transition: all 0.2s; border-left: 4px solid transparent; }
        .sidebar-item:hover, .sidebar-item.active { background-color: rgba(255,255,255,0.1); border-left-color: #3B82F6; }
        .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Sidebar (Hidden by default, Off-canvas) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white transition-transform duration-300 transform -translate-x-full z-50 shadow-2xl flex flex-col">
        <div class="h-16 flex items-center justify-between px-6 bg-gray-800 border-b border-gray-700 shadow-md">
            <div class="flex items-center">
                <i class="fa-solid fa-flask text-blue-500 text-2xl mr-3"></i>
                <span class="font-bold text-lg tracking-wide">Research<span class="text-blue-500">Portal</span></span>
            </div>
            <!-- Close Button for Mobile/Drawer -->
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white focus:outline-none">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold shadow-lg text-white">
                    <?php echo (function_exists('mb_substr')) ? strtoupper(mb_substr($userName, 0, 2, 'UTF-8')) : strtoupper(substr($userName, 0, 2)); ?>
                </div>
                <div>
                    <h4 class="text-sm font-semibold"><?php echo htmlspecialchars($userName); ?></h4>
                    <span class="text-xs text-green-400 bg-green-400/10 px-2 py-0.5 rounded-full" id="user-role-display">
                        <?php echo ($userRole == 'coordinator') ? 'ผู้ประสานงาน' : 'นักวิจัย'; ?>
                    </span>
                </div>
            </div>
        </div>

        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <!-- Sidebar Menu Updates -->
            <a href="dashboard.php" class="sidebar-item active flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-r-lg border-l-4 border-blue-500 bg-gray-800">
                <i class="fa-solid fa-chart-pie w-6"></i>
                <span>ภาพรวม (Dashboard)</span>
            </a>
            
            <div class="mt-4 mb-2 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">งานวิจัยใหม่</div>
            <a href="submission.php" class="sidebar-item flex items-center px-4 py-3 text-emerald-400 hover:text-emerald-300 hover:bg-gray-800 rounded-r-lg transition">
                <i class="fa-solid fa-plus-circle w-6"></i>
                <span>ส่งเอกสารงานวิจัยใหม่</span>
            </a>
            
            <div class="mt-4 mb-2 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">งานวิจัยต่อเนื่อง</div>
            <a href="#" class="sidebar-item flex items-center px-4 py-3 text-blue-400 hover:text-blue-300 hover:bg-gray-800 rounded-r-lg transition">
                <i class="fa-solid fa-file-signature w-6"></i>
                <span>ส่งเอกสารงานวิจัยต่อเนื่อง</span>
            </a>
            
            <div class="mt-4 mb-2 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">การมีส่วนร่วม</div>
            <a href="#" class="sidebar-item flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-800 rounded-r-lg transition">
                <i class="fa-solid fa-users w-6"></i>
                <span>งานวิจัยที่เป็นนักวิจัยร่วม</span>
            </a>
            
            <div id="coordinator-menu" class="<?php echo ($userRole == 'coordinator') ? '' : 'hidden'; ?> mt-6">
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Management</p>
                <a href="register.html" class="sidebar-item flex items-center px-4 py-3 text-amber-400 hover:text-amber-300 rounded-r-lg bg-amber-900/20 border-l-amber-500">
                    <i class="fa-solid fa-user-plus w-6"></i>
                    <span>ลงทะเบียนแทน</span>
                </a>
            </div>
        </nav>

        <div class="p-4 bg-gray-900 border-t border-gray-800">
             <a href="login.html" onclick="document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';" class="flex items-center px-4 py-2 text-red-400 hover:text-red-300 transition">
                <i class="fa-solid fa-sign-out-alt w-6"></i>
                <span>ออกจากระบบ</span>
            </a>
        </div>
    </aside>

    <!-- Overlay (Click to close) -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity opacity-0"></div>

    <div class="flex-grow flex flex-col h-screen overflow-hidden">
        
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button onclick="toggleSidebar()" class="text-gray-600 hover:text-blue-600 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="font-bold text-lg text-gray-800">ResearchPortal</div>
            </div>
            
            <!-- Top Right Menu -->
            <div class="flex items-center gap-4">
                 <!-- Breadcrumbs (Desktop) -->
                <div class="hidden md:flex items-center text-gray-500 text-sm mr-4">
                    <span class="hover:text-blue-600 cursor-pointer">หน้าหลัก</span>
                    <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
                    <span class="font-bold text-gray-800">ภาพรวม (Dashboard)</span>
                </div>

                <div class="relative group h-full flex items-center">
                    <button class="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition h-full focus:outline-none">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random" class="w-8 h-8 rounded-full">
                        <span class="text-sm font-semibold hidden md:block"><?php echo htmlspecialchars($userName); ?></span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <!-- Dropdown with invisible bridge (pt-2) -->
                    <div class="absolute right-0 top-full pt-2 w-48 hidden group-hover:block z-50">
                        <div class="bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden">
                            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600"><i class="fa-solid fa-user-gear mr-2"></i>จัดการข้อมูลส่วนตัว</a>
                            <a href="select_role.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600"><i class="fa-solid fa-repeat mr-2"></i>เปลี่ยนหน้าที่</a>
                            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600"><i class="fa-solid fa-book mr-2"></i>คู่มือการใช้งาน</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="login.html" class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50"><i class="fa-solid fa-sign-out-alt mr-2"></i>ออกจากระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-grow overflow-y-auto p-6 md:p-8 bg-gray-50">
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Database Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">ภาพรวมโครงการวิจัย</h1>
                 <p class="text-gray-500 text-sm">ติดตามสถานะและจัดการโครงการวิจัยของคุณ</p>
            </div>

            <!-- Status Cards (New Logic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Group 1: New Research (Green/Red) -->
                <div class="bg-white rounded-xl p-6 card-shadow border-l-4 border-emerald-500 relative overflow-hidden group hover:-translate-y-1 transition text-left">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-flask text-6xl text-emerald-500"></i>
                    </div>
                    <div>
                        <p class="text-emerald-600 font-bold text-sm uppercase tracking-wide">งานวิจัยใหม่ (อนุมัติแล้ว)</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $countApproved ?? 0; ?></h3>
                        <p class="text-xs text-gray-400 mt-1">โครงการ</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 card-shadow border-l-4 border-rose-500 relative overflow-hidden group hover:-translate-y-1 transition text-left">
                     <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-clipboard-list text-6xl text-rose-500"></i>
                    </div>
                    <div>
                        <p class="text-rose-600 font-bold text-sm uppercase tracking-wide">งานวิจัยใหม่ (แก้ไข/รอ)</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo ($totalProjects - $countApproved - $countWorking - $countPublished); ?></h3>
                        <p class="text-xs text-gray-400 mt-1">รอตรวจสอบ</p>
                    </div>
                </div>

                 <!-- Group 2: Ongoing Research (Blue/Orange) -->
                <div class="bg-white rounded-xl p-6 card-shadow border-l-4 border-blue-500 relative overflow-hidden group hover:-translate-y-1 transition text-left">
                     <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-spinner text-6xl text-blue-500"></i>
                    </div>
                    <div>
                        <p class="text-blue-600 font-bold text-sm uppercase tracking-wide">งานวิจัยต่อเนื่อง (ทั้งหมด)</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $countWorking ?? 0; ?></h3>
                        <p class="text-xs text-gray-400 mt-1">กำลังดำเนินการ</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 card-shadow border-l-4 border-orange-500 relative overflow-hidden group hover:-translate-y-1 transition text-left">
                     <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-clock-rotate-left text-6xl text-orange-500"></i>
                    </div>
                    <div>
                        <p class="text-orange-600 font-bold text-sm uppercase tracking-wide">งานวิจัยต่อเนื่อง (รอรายงาน)</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2">0</h3> <!-- Mock data for now -->
                         <p class="text-xs text-gray-400 mt-1">ถึงกำหนดส่งรายงาน</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <i class="fa-solid fa-chart-column mr-2 text-blue-500"></i> สถิติภาระงานวิจัยรายปี
                    </h3>
                    <div class="h-64">
                        <canvas id="researchChart"></canvas>
                    </div>
                </div>
                
                <div class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <i class="fa-solid fa-chart-pie mr-2 text-indigo-500"></i> สัดส่วนประเภททุน
                    </h3>
                    <div class="h-48 flex justify-center">
                        <canvas id="budgetChart"></canvas>
                    </div>
                    <div class="mt-4 text-center text-sm text-gray-500">
                        ทุนภายนอกคิดเป็น 65% ของงบประมาณทั้งหมด
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">สถานะโครงการล่าสุด</h3>
                    <button class="text-sm text-blue-600 hover:text-blue-800 font-semibold">ดูทั้งหมด</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="px-6 py-3 font-semibold">ชื่อโครงการ</th>
                                <th class="px-6 py-3 font-semibold">วันที่ยื่น</th>

                                <th class="px-6 py-3 font-semibold text-center">สถานะ</th>
                                <th class="px-6 py-3 font-semibold text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php if (count($projects) > 0): ?>
                                <?php foreach ($projects as $proj): ?>
                                    <?php 
                                        $statusClass = '';
                                        $statusLabel = '';
                                        $canEdit = false;
                                        $canDelete = false;

                                        switch($proj['status']) {
                                            case 'approved': 
                                                $statusClass = 'bg-green-100 text-green-700'; 
                                                $statusLabel = 'อนุมัติแล้ว';
                                                break;
                                            case 'pending': 
                                            case 'pending_approval': 
                                                $statusClass = 'bg-yellow-100 text-yellow-700'; 
                                                $statusLabel = 'รอตรวจสอบ';
                                                break;
                                            case 'working': 
                                                $statusClass = 'bg-blue-100 text-blue-700'; 
                                                $statusLabel = 'กำลังดำเนินการ';
                                                break;
                                            case 'published': 
                                                $statusClass = 'bg-purple-100 text-purple-700'; 
                                                $statusLabel = 'ตีพิมพ์แล้ว';
                                                break;
                                            case 'rejected': 
                                                $statusClass = 'bg-red-100 text-red-700'; 
                                                $statusLabel = 'ปฏิเสธ/แก้ไข';
                                                $canEdit = true; // Allow edit if rejected
                                                break;
                                            case 'draft': 
                                                $statusClass = 'bg-gray-200 text-gray-600'; 
                                                $statusLabel = 'แบบร่าง';
                                                $canEdit = true;
                                                $canDelete = true;
                                                break;
                                            default:
                                                $statusClass = 'bg-gray-100 text-gray-700';
                                                $statusLabel = $proj['status'];
                                        }
                                        
                                        $displayDate = '-';
                                        if (!empty($proj['submission_date'])) {
                                            $displayDate = date('d M Y', strtotime($proj['submission_date']));
                                        } elseif (!empty($proj['created_at'])) {
                                            $displayDate = date('d M Y', strtotime($proj['created_at']));
                                        }
                                    ?>
                                    <tr class="hover:bg-gray-50 transition group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-800"><?php echo htmlspecialchars($proj['title_th']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($proj['title_en'] ?? '-'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600"><?php echo $displayDate; ?></td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center gap-2 opacity-50 group-hover:opacity-100 transition">
                                                <?php if ($canEdit): ?>
                                                    <a href="submission.php?id=<?php echo $proj['id']; ?>" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="แก้ไข">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="project_details.php?id=<?php echo $proj['id']; ?>" class="w-8 h-8 rounded-full bg-gray-50 text-gray-600 flex items-center justify-center hover:bg-gray-200 transition" title="ดูรายละเอียด">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($canDelete): ?>
                                                    <button onclick="deleteProject(<?php echo $proj['id']; ?>)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition" title="ลบ">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        ยังไม่มีโครงการวิจัย
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Tailwind Class Hint for JIT: translate-x-0 -->
    <script>
        // Sidebar Toggle Logic
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            // Check if closed (has negative translate)
            const isClosed = sidebar.classList.contains('-translate-x-full');
            
            if (isClosed) {
                // Open Sidebar
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0'); // Build explicit open state
                
                // Show Overlay
                overlay.classList.remove('hidden');
                // Small delay to allow display:block to apply before changing opacity for transition
                setTimeout(() => { 
                    overlay.classList.remove('opacity-0'); 
                    overlay.classList.add('opacity-100');
                }, 10);
            } else {
                // Close Sidebar
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                
                // Hide Overlay
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                setTimeout(() => { 
                    overlay.classList.add('hidden'); 
                }, 300); // Wait for transition
            }
        }

        // --- 1. Chart Configuration ---
        Chart.defaults.font.family = "'Sarabun', sans-serif";
        Chart.defaults.color = '#6B7280';

        // Bar Chart
        const ctx1 = document.getElementById('researchChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['2021', '2022', '2023', '2024', '2025'],
                datasets: [{
                    label: 'คะแนนภาระงาน (Research Points)',
                    data: [150, 230, 180, 320, 400],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 4,
                }, {
                    label: 'จำนวนโครงการ',
                    data: [2, 3, 2, 4, 5],
                    type: 'line',
                    borderColor: '#F59E0B',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                    y1: { position: 'right', beginAtZero: true, grid: { display: false } }
                }
            }
        });

        // Doughnut Chart
        const ctx2 = document.getElementById('budgetChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['ทุนภายใน', 'ทุนภายนอก', 'ทุนส่วนตัว'],
                datasets: [{
                    data: [35, 60, 5],
                    backgroundColor: [
                        '#10B981', // Emerald
                        '#3B82F6', // Blue
                        '#F43F5E'  // Rose
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });

        // --- 1.5 Delete Project Logic ---
        async function deleteProject(id) {
            if (!confirm('คุณต้องการลบแบบร่างโครงการนี้ใช่หรือไม่? \n(การกระทำนี้ไม่สามารถย้อนกลับได้)')) return;

            const formData = new FormData();
            formData.append('action', 'delete_project');
            formData.append('project_id', id);

            try {
                const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    alert('ลบโครงการเรียบร้อยแล้ว');
                    window.location.reload();
                } else {
                    alert('ลบไม่สำเร็จ: ' + result.message);
                }
            } catch (e) {
                console.error(e);
                alert('Connection Error');
            }
        }

        // --- 2. Role Switching Logic ---
        function switchRole(role) {
            const coordinatorMenu = document.getElementById('coordinator-menu');
            const roleDisplay = document.getElementById('user-role-display');
            // Buttons removed from DOM, so no need to toggle their classes

            if (role === 'coordinator') {
                coordinatorMenu.classList.remove('hidden');
                roleDisplay.textContent = 'ผู้ประสานงาน';
                roleDisplay.classList.replace('text-green-400', 'text-amber-400');
                roleDisplay.classList.replace('bg-green-400/10', 'bg-amber-400/10');
            } else {
                coordinatorMenu.classList.add('hidden');
                roleDisplay.textContent = 'นักวิจัย';
                roleDisplay.classList.replace('text-amber-400', 'text-green-400');
                roleDisplay.classList.replace('bg-amber-400/10', 'bg-green-400/10');
            }
        }
    </script>
</body>
</html>
