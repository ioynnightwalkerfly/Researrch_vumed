<?php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}
$userName = $_SESSION['fullname'] ?? 'Admin';

$statusFilter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Status map for Thai labels and colors
$statusConfig = [
    'draft'             => ['label' => 'แบบร่าง',           'color' => 'bg-gray-100 text-gray-600 border-gray-200'],
    'pending_officer'   => ['label' => 'รอเจ้าหน้าที่',       'color' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
    'pending_secretary' => ['label' => 'รอเลขานุการ',        'color' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
    'pending_chairman'  => ['label' => 'รอประธาน',          'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
    'approved'          => ['label' => 'อนุมัติแล้ว',         'color' => 'bg-green-100 text-green-700 border-green-200'],
    'rejected'          => ['label' => 'ตีกลับ (เจ้าหน้าที่)', 'color' => 'bg-red-100 text-red-700 border-red-200'],
    'rejected_secretary'=> ['label' => 'ตีกลับ (เลขา)',      'color' => 'bg-red-100 text-red-700 border-red-200'],
    'working'           => ['label' => 'กำลังดำเนินการ',      'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
    'published'         => ['label' => 'ตีพิมพ์แล้ว',        'color' => 'bg-purple-100 text-purple-700 border-purple-200'],
];

try {
    // Fetch projects
    $sql = "SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE 1=1";
    $params = [];

    if ($statusFilter !== 'all') {
        $sql .= " AND p.status = ?";
        $params[] = $statusFilter;
    }
    if ($search) {
        $sql .= " AND (p.title_th LIKE ? OR p.title_en LIKE ? OR u.firstname_th LIKE ? OR u.lastname_th LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    $sql .= " ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status counts for badges
    $stmtCounts = $conn->query("SELECT status, COUNT(*) as cnt FROM projects GROUP BY status");
    $statusCounts = [];
    foreach ($stmtCounts->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $statusCounts[$row['status']] = $row['cnt'];
    }
    $totalAll = array_sum($statusCounts);

} catch (Exception $e) {
    $error = $e->getMessage();
    $projects = [];
    $statusCounts = [];
    $totalAll = 0;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการโครงการวิจัย - Admin</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; } .scrollbar-hide::-webkit-scrollbar { display: none; }</style>
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
                <i class="fa-solid fa-chart-pie w-6"></i><span>ภาพรวมระบบ</span>
            </a>
            <a href="admin_users.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-users-gear w-6"></i><span>จัดการผู้ใช้งาน</span>
            </a>
            <a href="admin_projects.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-folder-tree w-6"></i><span>จัดการโครงการวิจัย</span>
            </a>
            <a href="admin_academic_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-handshake-angle w-6"></i><span>ข่าวบริการวิชาการ</span>
            </a>
            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Access Modes</div>
            <a href="../officer/dashboard.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-user-shield w-6"></i><span>โหมดเจ้าหน้าที่</span>
            </a>
            <a href="../secretary/dashboard.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-file-contract w-6"></i><span>โหมดเลขานุการ</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <a href="../api/logout.php" class="block w-full text-center py-2 rounded border border-gray-700 hover:bg-red-600 hover:border-red-600 hover:text-white transition text-gray-400 text-sm">
                <i class="fa-solid fa-sign-out-alt mr-2"></i>ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b px-6 py-4 shadow-sm z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-folder-tree mr-2 text-blue-600"></i>จัดการโครงการวิจัย</h2>
                <div class="text-sm text-gray-500">ทั้งหมด <span class="font-bold text-gray-800"><?php echo $totalAll; ?></span> โครงการ</div>
            </div>
        </header>

        <div class="flex-grow overflow-auto p-6 scrollbar-hide">

            <!-- Status Filter Tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="?status=all&search=<?php echo urlencode($search); ?>" class="px-3 py-1.5 rounded-full text-xs font-bold border transition <?php echo $statusFilter=='all' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'; ?>">
                    ทั้งหมด <span class="ml-1 opacity-70">(<?php echo $totalAll; ?>)</span>
                </a>
                <?php foreach ($statusConfig as $key => $cfg): ?>
                <a href="?status=<?php echo $key; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1.5 rounded-full text-xs font-bold border transition <?php echo $statusFilter==$key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'; ?>">
                    <?php echo $cfg['label']; ?> <span class="ml-1 opacity-70">(<?php echo $statusCounts[$key] ?? 0; ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Bar -->
            <form class="mb-6 flex gap-3">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <div class="relative flex-grow max-w-md">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" placeholder="ค้นหาชื่อโครงการ / นักวิจัย..." value="<?php echo htmlspecialchars($search); ?>" class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition font-bold">ค้นหา</button>
                <?php if ($search): ?>
                <a href="?status=<?php echo $statusFilter; ?>" class="bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-300 transition">ล้าง</a>
                <?php endif; ?>
            </form>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b">
                            <tr>
                                <th class="px-4 py-3 w-16">ID</th>
                                <th class="px-4 py-3">ชื่อโครงการ</th>
                                <th class="px-4 py-3">นักวิจัย</th>
                                <th class="px-4 py-3 text-center w-48">สถานะ</th>
                                <th class="px-4 py-3 text-right w-28">อัพเดต</th>
                                <th class="px-4 py-3 text-center w-28">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($projects as $p): 
                                $sc = $statusConfig[$p['status']] ?? ['label' => $p['status'], 'color' => 'bg-gray-100 text-gray-600 border-gray-200'];
                            ?>
                            <tr class="hover:bg-gray-50 transition" id="row-<?php echo $p['id']; ?>">
                                <td class="px-4 py-3 font-mono text-gray-400 text-xs">#<?php echo $p['id']; ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($p['title_th']); ?></div>
                                    <div class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($p['title_en'] ?? ''); ?> · <?php echo $p['research_type'] ?? ''; ?></div>
                                </td>
                                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <select onchange="changeStatus(<?php echo $p['id']; ?>, this.value, '<?php echo $p['status']; ?>')" class="text-xs font-bold rounded-full px-2 py-1 border cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-300 <?php echo $sc['color']; ?>">
                                        <?php foreach ($statusConfig as $key => $cfg): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $p['status'] == $key ? 'selected' : ''; ?>><?php echo $cfg['label']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-400 text-xs">
                                    <?php 
                                    $dateStr = $p['updated_at'] ?? $p['submission_date'] ?? $p['created_at'];
                                    echo $dateStr ? date('d/m/y H:i', strtotime($dateStr)) : '-'; 
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="../officer/review.php?id=<?php echo $p['id']; ?>" target="_blank" class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-500 flex items-center justify-center transition" title="ดูรายละเอียด">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <button onclick="deleteProject(<?php echo $p['id']; ?>)" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition" title="ลบ">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($projects) === 0): ?>
                    <div class="p-12 text-center text-gray-400">
                        <i class="fa-solid fa-folder-open text-4xl mb-3"></i>
                        <p>ไม่พบข้อมูลโครงการ</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center text-xs text-gray-400 mt-4">แสดง <?php echo count($projects); ?> จาก <?php echo $totalAll; ?> โครงการ</div>
        </div>
    </main>

    <script>
        async function changeStatus(projectId, newStatus, oldStatus) {
            if (newStatus === oldStatus) return;
            if (!confirm(`เปลี่ยนสถานะโครงการ #${projectId} เป็น "${newStatus}" ?`)) {
                // Revert dropdown
                location.reload();
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_project_status');
            formData.append('project_id', projectId);
            formData.append('new_status', newStatus);

            try {
                const res = await fetch('../api/admin_user_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    // Flash green
                    const row = document.getElementById('row-' + projectId);
                    row.classList.add('bg-green-50');
                    setTimeout(() => row.classList.remove('bg-green-50'), 1500);
                } else {
                    alert('Error: ' + data.message);
                    location.reload();
                }
            } catch(e) { alert('Connection Error'); location.reload(); }
        }

        async function deleteProject(id) {
            if(!confirm('⚠️ คำเตือน: การลบโครงการจะไม่สามารถกู้คืนได้\nยืนยันที่จะลบโครงการ #' + id + ' หรือไม่?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_project');
            formData.append('project_id', id);
            
            try {
                const res = await fetch('../api/admin_user_action.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    document.getElementById('row-' + id).remove();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch(e) { alert('Connection Error'); }
        }
    </script>
</body>
</html>
