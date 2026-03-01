<?php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
$userName = $_SESSION['fullname'] ?? 'Officer';

// Settings defaults
$deadlineDays = 10; // Default 10 days
$warningDays = 7;   // Warning at 7 days

// Fetch all returned/rejected projects with day counts
$stmtRejected = $conn->prepare("
    SELECT p.*, u.firstname_th, u.lastname_th, u.email,
           DATEDIFF(NOW(), p.updated_at) as days_since_return
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.status IN ('rejected', 'rejected_secretary')
    ORDER BY p.updated_at ASC
");
$stmtRejected->execute();
$rejectedProjects = $stmtRejected->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalRejected = count($rejectedProjects);
$overdue = 0;
$warning = 0;
$onTime = 0;
foreach ($rejectedProjects as $p) {
    $days = (int)$p['days_since_return'];
    if ($days >= $deadlineDays) $overdue++;
    elseif ($days >= $warningDays) $warning++;
    else $onTime++;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Deadline - Officer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        @keyframes pulse-red { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .pulse-red { animation: pulse-red 2s infinite; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-green-400">OFFICER</h1>
            <p class="text-xs text-gray-500 mt-1">เจ้าหน้าที่ตรวจสอบ</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-inbox w-6"></i><span>รอตรวจสอบ</span>
            </a>
            <a href="deadlines.php" class="flex items-center px-4 py-3 bg-yellow-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-clock w-6"></i><span>จัดการ Deadline</span>
                <?php if ($overdue > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full pulse-red"><?php echo $overdue; ?></span>
                <?php endif; ?>
            </a>

            <div class="mt-6 pt-4 border-t border-gray-700">
                <div class="px-4 text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Settings</div>
                <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                    <i class="fa-solid fa-exchange-alt w-6"></i><span>เปลี่ยนบทบาท</span>
                </a>
            </div>
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
                <i class="fa-solid fa-sign-out-alt mr-2"></i>ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center z-10">
            <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-clock mr-2 text-yellow-600"></i>จัดการ Deadline โครงการตีกลับ</h2>
            <button onclick="sendAllReminders()" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-yellow-600 transition">
                <i class="fa-solid fa-bell mr-1"></i>ส่งแจ้งเตือนทั้งหมดที่เกิน
            </button>
        </header>

        <div id="mainScroll" class="flex-grow overflow-auto p-6 scrollbar-hide">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-list text-blue-600"></i></div>
                        <div><div class="text-xl font-bold text-gray-800"><?php echo $totalRejected; ?></div><div class="text-xs text-gray-500">ตีกลับทั้งหมด</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-green-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-check text-green-600"></i></div>
                        <div><div class="text-xl font-bold text-green-600"><?php echo $onTime; ?></div><div class="text-xs text-gray-500">ยังไม่ถึงกำหนด</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-yellow-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-exclamation-triangle text-yellow-600"></i></div>
                        <div><div class="text-xl font-bold text-yellow-600"><?php echo $warning; ?></div><div class="text-xs text-gray-500">ใกล้ครบกำหนด</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-red-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-fire text-red-600"></i></div>
                        <div><div class="text-xl font-bold text-red-600"><?php echo $overdue; ?></div><div class="text-xs text-gray-500">เกินกำหนดแล้ว</div></div>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <h3 class="font-bold text-gray-800 mb-3"><i class="fa-solid fa-cog mr-2 text-gray-500"></i>ตั้งค่าการแจ้งเตือน</h3>
                <div class="flex flex-wrap gap-6 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">กำหนดส่งแก้ไข (วัน)</label>
                        <input type="number" id="deadlineDays" value="<?php echo $deadlineDays; ?>" min="1" max="90" class="w-24 border rounded-lg px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-yellow-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">เตือนเมื่อถึง (วัน)</label>
                        <input type="number" id="warningDays" value="<?php echo $warningDays; ?>" min="1" max="90" class="w-24 border rounded-lg px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-yellow-300">
                    </div>
                    <button onclick="applySettings()" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-700 transition">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i>อัพเดตตาราง
                    </button>
                    <div class="text-xs text-gray-400 flex items-center gap-1">
                        <i class="fa-solid fa-info-circle"></i>
                        เปลี่ยนค่าแล้วกดอัพเดตเพื่อปรับสีในตาราง
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" id="deadlineTable">
                        <thead class="bg-gray-50 border-b text-gray-500 font-semibold">
                            <tr>
                                <th class="px-5 py-3 w-16">ID</th>
                                <th class="px-5 py-3">โครงการ</th>
                                <th class="px-5 py-3">ผู้วิจัย</th>
                                <th class="px-5 py-3 text-center">ตีกลับจาก</th>
                                <th class="px-5 py-3 text-center">วันที่ตีกลับ</th>
                                <th class="px-5 py-3 text-center">ผ่านมา</th>
                                <th class="px-5 py-3 text-center">สถานะ</th>
                                <th class="px-5 py-3 text-center">แจ้งเตือน</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($rejectedProjects)): ?>
                            <tr><td colspan="8" class="p-12 text-center text-gray-400"><i class="fa-solid fa-check-circle text-4xl mb-3"></i><p>ไม่มีรายการตีกลับ</p></td></tr>
                            <?php else: ?>
                            <?php foreach ($rejectedProjects as $p): 
                                $days = (int)$p['days_since_return'];
                                $rejectedByLabel = ($p['rejected_by'] ?? '') === 'secretary' ? 'เลขา' : (($p['rejected_by'] ?? '') === 'chairman' ? 'ประธาน' : 'เจ้าหน้าที่');
                                
                                if ($days >= $deadlineDays) {
                                    $statusBg = 'bg-red-100'; $statusBadge = 'bg-red-500 text-white'; $statusText = 'เกินกำหนด!';
                                } elseif ($days >= $warningDays) {
                                    $statusBg = 'bg-yellow-50'; $statusBadge = 'bg-yellow-400 text-yellow-900'; $statusText = 'ใกล้ครบกำหนด';
                                } else {
                                    $statusBg = ''; $statusBadge = 'bg-green-100 text-green-700'; $statusText = 'ยังมีเวลา';
                                }
                                $remaining = $deadlineDays - $days;
                            ?>
                            <tr class="hover:bg-gray-50 transition deadline-row <?php echo $statusBg; ?>" data-days="<?php echo $days; ?>">
                                <td class="px-5 py-3 font-mono text-gray-400 text-xs">#<?php echo $p['id']; ?></td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($p['title_th']); ?></div>
                                    <div class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($p['return_reason'] ?? '-'); ?></div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-gray-700"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo htmlspecialchars($p['email']); ?></div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"><?php echo $rejectedByLabel; ?></span>
                                </td>
                                <td class="px-5 py-3 text-center text-xs text-gray-500">
                                    <?php echo $p['updated_at'] ? date('d/m/Y', strtotime($p['updated_at'])) : '-'; ?>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <div class="text-lg font-bold <?php echo $days >= $deadlineDays ? 'text-red-600 pulse-red' : ($days >= $warningDays ? 'text-yellow-600' : 'text-gray-700'); ?>">
                                        <?php echo $days; ?> วัน
                                    </div>
                                    <?php if ($remaining > 0): ?>
                                    <div class="text-xs text-gray-400">เหลือ <?php echo $remaining; ?> วัน</div>
                                    <?php else: ?>
                                    <div class="text-xs text-red-500 font-bold">เกิน <?php echo abs($remaining); ?> วัน</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <button onclick="sendReminder(<?php echo $p['id']; ?>, '<?php echo addslashes(htmlspecialchars($p['title_th'])); ?>', '<?php echo htmlspecialchars($p['email']); ?>')" 
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition" title="ส่งแจ้งเตือนทาง Email + Discord">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex gap-6 mt-4 text-xs text-gray-500">
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span> ยังมีเวลา</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> ใกล้ครบกำหนด (<?php echo $warningDays; ?>+ วัน)</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> เกินกำหนด (<?php echo $deadlineDays; ?>+ วัน)</div>
            </div>
        </div>
    </main>

    <script>
        function applySettings() {
            const deadline = parseInt(document.getElementById('deadlineDays').value);
            const warning = parseInt(document.getElementById('warningDays').value);
            if (warning >= deadline) { alert('วันเตือนต้องน้อยกว่าวันกำหนด'); return; }

            document.querySelectorAll('.deadline-row').forEach(row => {
                const days = parseInt(row.dataset.days);
                const statusCell = row.querySelector('td:nth-child(7) span');
                const daysCell = row.querySelector('td:nth-child(6)');
                const remaining = deadline - days;

                row.classList.remove('bg-red-100', 'bg-yellow-50');
                
                if (days >= deadline) {
                    row.classList.add('bg-red-100');
                    statusCell.className = 'px-2 py-1 rounded-full text-xs font-bold bg-red-500 text-white';
                    statusCell.textContent = 'เกินกำหนด!';
                } else if (days >= warning) {
                    row.classList.add('bg-yellow-50');
                    statusCell.className = 'px-2 py-1 rounded-full text-xs font-bold bg-yellow-400 text-yellow-900';
                    statusCell.textContent = 'ใกล้ครบกำหนด';
                } else {
                    statusCell.className = 'px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700';
                    statusCell.textContent = 'ยังมีเวลา';
                }
            });
        }

        async function sendReminder(projectId, title, email) {
            if (!confirm(`ส่งแจ้งเตือนไปยัง ${email}\nโครงการ: ${title}`)) return;
            const formData = new FormData();
            formData.append('action', 'send_deadline_reminder');
            formData.append('project_id', projectId);
            try {
                const res = await fetch('../api/check_deadlines.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) alert('ส่งแจ้งเตือนเรียบร้อย (Email + Discord)');
                else alert('Error: ' + data.message);
            } catch(e) { alert('Connection Error'); }
        }

        async function sendAllReminders() {
            const deadline = parseInt(document.getElementById('deadlineDays').value);
            if (!confirm(`ส่งแจ้งเตือนโครงการทั้งหมดที่เกิน ${deadline} วัน?`)) return;
            const formData = new FormData();
            formData.append('action', 'send_all_reminders');
            formData.append('deadline_days', deadline);
            try {
                const res = await fetch('../api/check_deadlines.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) alert(`ส่งแจ้งเตือนแล้ว ${data.count} รายการ`);
                else alert('Error: ' + data.message);
            } catch(e) { alert('Connection Error'); }
        }
    </script>
</body>
</html>
