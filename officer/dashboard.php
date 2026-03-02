<?php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
$userName = $_SESSION['fullname'] ?? 'Officer';

// Fetch Pending Officer Projects
$stmtPending = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending_officer' ORDER BY p.submission_date ASC");
$stmtPending->execute();
$pendingProjects = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// Fetch Returned (rejected by officer) Projects
try {
    $stmtReturned = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'rejected' AND p.rejected_by = 'officer' ORDER BY p.updated_at DESC LIMIT 20");
    $stmtReturned->execute();
    $returnedProjects = $stmtReturned->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback if rejected_by column doesn't exist yet
    $stmtReturned = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'rejected' ORDER BY p.updated_at DESC LIMIT 20");
    $stmtReturned->execute();
    $returnedProjects = $stmtReturned->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Passed (approved by officer → pending_secretary+)
$stmtPassed = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status IN ('pending_secretary', 'pending_chairman', 'approved') ORDER BY p.updated_at DESC LIMIT 20");
$stmtPassed->execute();
$passedProjects = $stmtPassed->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalPending = count($pendingProjects);
$totalReturned = count($returnedProjects);
$totalPassed = count($passedProjects);

// All projects count
$stmtAll = $conn->prepare("SELECT COUNT(*) FROM projects WHERE status != 'draft'");
$stmtAll->execute();
$totalAll = $stmtAll->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เจ้าหน้าที่ - Officer Dashboard</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; } .scrollbar-hide::-webkit-scrollbar { display: none; }</style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-green-400">OFFICER</h1>
            <p class="text-xs text-gray-500 mt-1">เจ้าหน้าที่ตรวจสอบ</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-green-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-inbox w-6"></i>
                <span>รอตรวจสอบ</span>
                <?php if ($totalPending > 0): ?>
                <span class="ml-auto bg-white text-green-600 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $totalPending; ?></span>
                <?php endif; ?>
            </a>
            <a href="javascript:void(0)" onclick="scrollToSection('returned-section')" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-rotate-left w-6"></i>
                <span>ตีกลับแล้ว</span>
                <?php if ($totalReturned > 0): ?>
                <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $totalReturned; ?></span>
                <?php endif; ?>
            </a>
            <a href="javascript:void(0)" onclick="scrollToSection('passed-section')" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-circle-check w-6"></i>
                <span>ผ่านแล้ว</span>
            </a>

            <div class="mt-6 pt-4 border-t border-gray-700">
                <a href="deadlines.php" class="w-full flex items-center px-4 py-3 text-yellow-400 hover:text-yellow-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fa-solid fa-clock w-6"></i>
                    <span>จัดการ Deadline</span>
                </a>
            </div>

            <div class="mt-6 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i>
                <span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center font-bold text-white">
                    <?php echo function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1); ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="text-xs text-green-400">Officer</div>
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
            <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-user-shield mr-2 text-green-600"></i>แดชบอร์ดเจ้าหน้าที่</h2>
        </header>

        <div id="mainScroll" class="flex-grow overflow-auto p-6 scrollbar-hide">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-folder text-blue-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalAll; ?></div><div class="text-xs text-gray-500">โครงการทั้งหมด</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-inbox text-yellow-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalPending; ?></div><div class="text-xs text-gray-500">รอตรวจสอบ</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-rotate-left text-orange-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalReturned; ?></div><div class="text-xs text-gray-500">ตีกลับ (รอแก้ไข)</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-circle-check text-green-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalPassed; ?></div><div class="text-xs text-gray-500">ผ่านแล้ว</div></div>
                    </div>
                </div>
            </div>

            <!-- Pending Officer Review -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100">
                    <h3 class="font-bold text-yellow-800"><i class="fa-solid fa-inbox mr-2"></i>รายการรอตรวจสอบ</h3>
                </div>
                <?php if (empty($pendingProjects)): ?>
                <div class="p-12 text-center text-gray-400">
                    <i class="fa-solid fa-check-circle text-4xl mb-3"></i>
                    <p>ไม่มีรายการรอตรวจสอบในขณะนี้</p>
                </div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3">วันที่ยื่น</th><th class="px-6 py-3 text-center">ดำเนินการ</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($pendingProjects as $p): ?>
                        <tr class="hover:bg-yellow-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div>
                                <div class="text-xs text-gray-400">#<?php echo $p['id']; ?> · <?php echo $p['research_type']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                <?php echo $p['submission_date'] ? date('d/m/Y H:i', strtotime($p['submission_date'])) : '-'; ?>
                                <?php 
                                if ($p['submission_date']) {
                                    $daysPassed = (time() - strtotime($p['submission_date'])) / (60 * 60 * 24);
                                    if ($daysPassed > 7) {
                                        echo '<div class="text-xs text-red-500 font-bold mt-1"><i class="fa-solid fa-clock"></i> ใกล้ครบกำหนด (' . floor($daysPassed) . ' วัน)</div>';
                                    }
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="review.php?id=<?php echo $p['id']; ?>" class="bg-green-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700 transition">
                                    <i class="fa-solid fa-magnifying-glass mr-1"></i>ตรวจสอบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Returned Projects -->
            <div id="returned-section" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-orange-50 px-6 py-4 border-b border-orange-100">
                    <h3 class="font-bold text-orange-800"><i class="fa-solid fa-rotate-left mr-2"></i>ตีกลับแล้ว (รอนักวิจัยแก้ไข)</h3>
                </div>
                <?php if (empty($returnedProjects)): ?>
                <div class="p-8 text-center text-gray-400"><p>ไม่มีรายการที่ตีกลับ</p></div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3">เหตุผล</th><th class="px-6 py-3 text-right">วันที่ตีกลับ</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($returnedProjects as $p): ?>
                        <tr class="hover:bg-orange-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div>
                                <div class="text-xs text-gray-400">#<?php echo $p['id']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-orange-600 text-xs max-w-[200px] truncate"><?php echo htmlspecialchars($p['return_reason'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-right text-gray-500 text-xs">
                                <?php echo $p['updated_at'] ? date('d/m/Y H:i', strtotime($p['updated_at'])) : '-'; ?>
                                <?php 
                                if ($p['updated_at']) {
                                    $daysPassed = floor((time() - strtotime($p['updated_at'])) / (60 * 60 * 24));
                                    echo "<div class='text-xs " . ($daysPassed >= 8 ? 'text-red-500 font-bold' : 'text-gray-400') . " mt-1'>ผ่านมา {$daysPassed} วัน</div>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Passed Projects -->
            <div id="passed-section" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-green-50 px-6 py-4 border-b border-green-100">
                    <h3 class="font-bold text-green-800"><i class="fa-solid fa-circle-check mr-2"></i>ผ่านการตรวจสอบแล้ว</h3>
                </div>
                <?php if (empty($passedProjects)): ?>
                <div class="p-8 text-center text-gray-400"><p>ยังไม่มีรายการที่ผ่าน</p></div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3 text-center">สถานะปัจจุบัน</th><th class="px-6 py-3 text-right">อัพเดตล่าสุด</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($passedProjects as $p): 
                        $statusLabel = ['pending_secretary' => 'รอเลขา', 'pending_chairman' => 'รอประธาน', 'approved' => 'อนุมัติแล้ว'];
                        $statusColor = ['pending_secretary' => 'bg-indigo-100 text-indigo-700', 'pending_chairman' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700'];
                    ?>
                        <tr class="hover:bg-green-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div>
                                <div class="text-xs text-gray-400">#<?php echo $p['id']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $statusColor[$p['status']] ?? 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo $statusLabel[$p['status']] ?? $p['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500 text-xs"><?php echo $p['updated_at'] ? date('d/m/Y H:i', strtotime($p['updated_at'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        async function checkDeadlines() {
            if(!confirm('สั่งตรวจสอบ Deadline และแจ้งเตือน Discord?')) return;
            try {
                const res = await fetch('../api/check_deadlines.php');
                const result = await res.json();
                if(result.success) alert(`ส่งการแจ้งเตือนแล้ว: ${result.alerts_sent} รายการ`);
                else alert('Error: ' + result.message);
            } catch(e) { alert('Connection Error'); }
        }

        function scrollToSection(id) {
            const container = document.getElementById('mainScroll');
            const target = document.getElementById(id);
            if (container && target) {
                const top = target.offsetTop - container.offsetTop - 20;
                container.scrollTo({ top: top, behavior: 'smooth' });
                // Flash highlight
                target.style.transition = 'box-shadow 0.3s';
                target.style.boxShadow = '0 0 0 3px #22c55e';
                setTimeout(() => { target.style.boxShadow = 'none'; }, 2000);
            }
        }
    </script>
</body>
</html>
