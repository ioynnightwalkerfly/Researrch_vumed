<?php
// chairman/dashboard.php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
$userName = $_SESSION['fullname'];

// Fetch pending_chairman projects
$stmt = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending_chairman' ORDER BY p.updated_at DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPending = count($projects);

// Approved projects
$stmtApproved = $conn->prepare("SELECT COUNT(*) FROM projects WHERE status = 'approved'");
$stmtApproved->execute();
$totalApproved = $stmtApproved->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประธาน - Chairman Dashboard</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; } .scrollbar-hide::-webkit-scrollbar { display: none; }</style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-yellow-400">CHAIRMAN</h1>
            <p class="text-xs text-gray-500 mt-1">ประธานพิจารณา</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-yellow-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-gavel w-6"></i>
                <span>รออนุมัติ</span>
                <?php if ($totalPending > 0): ?>
                <span class="ml-auto bg-white text-yellow-600 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $totalPending; ?></span>
                <?php endif; ?>
            </a>

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i>
                <span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center font-bold text-white">
                    <?php echo function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1); ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="text-xs text-yellow-400">Chairman</div>
                </div>
            </div>
            <a href="../api/logout.php" class="block w-full text-center py-2 rounded border border-gray-700 hover:bg-red-600 hover:border-red-600 hover:text-white transition text-gray-400 text-sm">
                <i class="fa-solid fa-sign-out-alt mr-2"></i> ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center z-10">
            <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-gavel mr-2 text-yellow-600"></i>แดชบอร์ดประธาน</h2>
        </header>

        <div class="flex-grow overflow-auto p-6 scrollbar-hide">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-hourglass-half text-yellow-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalPending; ?></div><div class="text-xs text-gray-500">รออนุมัติ</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-check-circle text-green-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalApproved; ?></div><div class="text-xs text-gray-500">อนุมัติแล้วทั้งหมด</div></div>
                    </div>
                </div>
            </div>

            <!-- Project List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100">
                    <h3 class="font-bold text-yellow-800"><i class="fa-solid fa-gavel mr-2"></i>รายการรออนุมัติขั้นสุดท้าย</h3>
                </div>
                <?php if (empty($projects)): ?>
                <div class="p-12 text-center text-gray-400"><i class="fa-solid fa-check-circle text-4xl mb-3"></i><p>ไม่มีรายการรออนุมัติ</p></div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3">วันที่ส่ง</th><th class="px-6 py-3 text-center">ดำเนินการ</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($projects as $p): ?>
                        <tr class="hover:bg-yellow-50 transition">
                            <td class="px-6 py-4"><div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div><div class="text-xs text-gray-400">#<?php echo $p['id']; ?></div></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-gray-500 text-xs"><?php echo $p['submission_date'] ?? '-'; ?></td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="finalApprove(<?php echo $p['id']; ?>)" class="bg-green-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700 transition"><i class="fa-solid fa-check mr-1"></i>อนุมัติ</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        async function finalApprove(projectId) {
            if(!confirm('ยืนยันอนุมัติขั้นสุดท้าย?')) return;
            const formData = new FormData();
            formData.append('action', 'secretary_review');
            formData.append('sub_action', 'final_approve');
            formData.append('project_id', projectId);
            try {
                const res = await fetch('../api/secretary_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) { alert('อนุมัติเรียบร้อย'); location.reload(); }
                else alert('Error: ' + data.message);
            } catch(e) { alert('Connection Error'); }
        }
    </script>
</body>
</html>
