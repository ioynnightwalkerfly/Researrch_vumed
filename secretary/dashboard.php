<?php
// secretary/dashboard.php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
$userName = $_SESSION['fullname'];

// Fetch pending_secretary projects
$stmtPending = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending_secretary' ORDER BY p.updated_at DESC");
$stmtPending->execute();
$pendingProjects = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending_chairman projects (for final approval)
$stmtChairman = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th FROM projects p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending_chairman' ORDER BY p.updated_at DESC");
$stmtChairman->execute();
$chairmanProjects = $stmtChairman->fetchAll(PDO::FETCH_ASSOC);

try {
    $stmtSettings = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'meeting_system_enabled'");
    $meetingSystemEnabled = $stmtSettings->fetchColumn() === '1';
} catch (Exception $e) {
    $meetingSystemEnabled = false;
}

// Stats
$totalPending = count($pendingProjects);
$totalChairman = count($chairmanProjects);

$stmtApproved = $conn->prepare("SELECT COUNT(*) FROM projects WHERE status = 'approved'");
$stmtApproved->execute();
$totalApproved = $stmtApproved->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลขานุการ - Secretary Dashboard</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; } .scrollbar-hide::-webkit-scrollbar { display: none; }</style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-indigo-400">SECRETARY</h1>
            <p class="text-xs text-gray-500 mt-1">เลขานุการ</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-indigo-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-inbox w-6"></i>
                <span>รอตรวจสอบ</span>
                <?php if ($totalPending > 0): ?>
                <span class="ml-auto bg-white text-indigo-600 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $totalPending; ?></span>
                <?php endif; ?>
            </a>
            <a href="#chairman-section" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-gavel w-6"></i>
                <span>รออนุมัติ (ประธาน)</span>
                <?php if ($totalChairman > 0): ?>
                <span class="ml-auto bg-yellow-500 text-gray-900 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $totalChairman; ?></span>
                <?php endif; ?>
            </a>
            
            <?php if ($meetingSystemEnabled): ?>
            <a href="../meeting_calendar.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-calendar-check w-6"></i>
                <span>จัดการนัดหมายการประชุม</span>
            </a>
            <?php endif; ?>

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i>
                <span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white">
                    <?php echo function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1); ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="text-xs text-indigo-400">Secretary</div>
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
            <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-file-contract mr-2 text-indigo-600"></i>แดชบอร์ดเลขานุการ</h2>
        </header>

        <div class="flex-grow overflow-auto p-6 scrollbar-hide">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-inbox text-indigo-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalPending; ?></div><div class="text-xs text-gray-500">รอตรวจสอบ</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-gavel text-yellow-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalChairman; ?></div><div class="text-xs text-gray-500">รออนุมัติ (ประธาน)</div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center"><i class="fa-solid fa-check-circle text-green-600 text-xl"></i></div>
                        <div><div class="text-2xl font-bold text-gray-800"><?php echo $totalApproved; ?></div><div class="text-xs text-gray-500">อนุมัติแล้ว</div></div>
                    </div>
                </div>
            </div>

            <!-- Pending Secretary Review -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                    <h3 class="font-bold text-indigo-800"><i class="fa-solid fa-inbox mr-2"></i>รายการรอตรวจสอบ (ผ่านจากเจ้าหน้าที่แล้ว)</h3>
                </div>
                <?php if (empty($pendingProjects)): ?>
                <div class="p-12 text-center text-gray-400"><i class="fa-solid fa-check-circle text-4xl mb-3"></i><p>ไม่มีรายการรอตรวจสอบ</p></div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3">วันที่ส่ง</th><th class="px-6 py-3 text-center">ดำเนินการ</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($pendingProjects as $p): ?>
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-6 py-4"><div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div><div class="text-xs text-gray-400">#<?php echo $p['id']; ?></div></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-gray-500 text-xs"><?php echo $p['submission_date'] ?? '-'; ?></td>
                            <td class="px-6 py-4 text-center"><a href="review.php?id=<?php echo $p['id']; ?>" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition"><i class="fa-solid fa-magnifying-glass mr-1"></i>ตรวจสอบ</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pending Chairman (Secretary can approve on behalf) -->
            <div id="chairman-section" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100">
                    <h3 class="font-bold text-yellow-800"><i class="fa-solid fa-gavel mr-2"></i>รออนุมัติขั้นสุดท้าย (ประธาน)</h3>
                    <p class="text-xs text-yellow-600 mt-1">เลขานุการสามารถกดอนุมัติแทนประธานได้</p>
                </div>
                <?php if (empty($chairmanProjects)): ?>
                <div class="p-12 text-center text-gray-400"><i class="fa-solid fa-hourglass text-4xl mb-3"></i><p>ไม่มีรายการรออนุมัติ</p></div>
                <?php else: ?>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500"><tr>
                        <th class="px-6 py-3">โครงการ</th><th class="px-6 py-3">ผู้วิจัย</th><th class="px-6 py-3 text-center">ดำเนินการ</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    <?php foreach ($chairmanProjects as $p): ?>
                        <tr class="hover:bg-yellow-50 transition">
                            <td class="px-6 py-4"><div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title_th']); ?></div><div class="text-xs text-gray-400">#<?php echo $p['id']; ?></div></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($p['firstname_th'] . ' ' . $p['lastname_th']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="finalApprove(<?php echo $p['id']; ?>)" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700 transition"><i class="fa-solid fa-check mr-1"></i>อนุมัติ</button>
                                    <button onclick="openReturnModal(<?php echo $p['id']; ?>, '<?php echo addslashes(htmlspecialchars($p['title_th'])); ?>')" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 transition"><i class="fa-solid fa-rotate-left mr-1"></i>ตีกลับ</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Return Modal -->
    <div id="returnModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6">
            <h3 class="font-bold text-lg text-red-700 mb-4"><i class="fa-solid fa-rotate-left mr-2"></i>ตีกลับจากประธาน</h3>
            <p class="text-sm text-gray-500 mb-2">โครงการ: <span id="returnProjectTitle" class="font-bold text-gray-800"></span></p>
            <input type="hidden" id="returnProjectId">
            
            <label class="block text-sm font-bold text-gray-700 mb-1 mt-4">เหตุผลที่ตีกลับ <span class="text-red-500">*</span></label>
            <textarea id="returnReason" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" placeholder="ระบุเหตุผล..."></textarea>

            <label class="block text-sm font-bold text-gray-700 mb-2 mt-4">ประเด็นที่ต้องแก้ไข</label>
            <div class="grid grid-cols-2 gap-2 text-sm" id="issueChecks">
                <label class="flex items-center gap-2"><input type="checkbox" value="เอกสารไม่ครบถ้วน" class="issue-cb"> เอกสารไม่ครบถ้วน</label>
                <label class="flex items-center gap-2"><input type="checkbox" value="ข้อมูลไม่ถูกต้อง" class="issue-cb"> ข้อมูลไม่ถูกต้อง</label>
                <label class="flex items-center gap-2"><input type="checkbox" value="งบประมาณไม่เหมาะสม" class="issue-cb"> งบประมาณไม่เหมาะสม</label>
                <label class="flex items-center gap-2"><input type="checkbox" value="ระเบียบวิธีวิจัยมีปัญหา" class="issue-cb"> ระเบียบวิธีวิจัยมีปัญหา</label>
                <label class="flex items-center gap-2"><input type="checkbox" value="จริยธรรมการวิจัย" class="issue-cb"> จริยธรรมการวิจัย</label>
                <label class="flex items-center gap-2"><input type="checkbox" value="อื่นๆ" class="issue-cb"> อื่นๆ</label>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button onclick="closeReturnModal()" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100 transition text-sm">ยกเลิก</button>
                <button onclick="submitChairmanReturn()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-bold"><i class="fa-solid fa-paper-plane mr-1"></i>ยืนยันตีกลับ</button>
            </div>
        </div>
    </div>

    <script>
        async function finalApprove(projectId) {
            if(!confirm('ยืนยันอนุมัติขั้นสุดท้าย (แทนประธาน)?')) return;
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

        function openReturnModal(projectId, title) {
            document.getElementById('returnProjectId').value = projectId;
            document.getElementById('returnProjectTitle').innerText = title;
            document.getElementById('returnReason').value = '';
            document.querySelectorAll('.issue-cb').forEach(cb => cb.checked = false);
            document.getElementById('returnModal').classList.remove('hidden');
        }

        function closeReturnModal() {
            document.getElementById('returnModal').classList.add('hidden');
        }

        async function submitChairmanReturn() {
            const projectId = document.getElementById('returnProjectId').value;
            const reason = document.getElementById('returnReason').value.trim();
            if (!reason) { alert('กรุณาระบุเหตุผล'); return; }

            const issues = [];
            document.querySelectorAll('.issue-cb:checked').forEach(cb => issues.push(cb.value));

            const formData = new FormData();
            formData.append('action', 'secretary_review');
            formData.append('sub_action', 'chairman_return');
            formData.append('project_id', projectId);
            formData.append('reason', reason);
            formData.append('issues', JSON.stringify(issues));

            try {
                const res = await fetch('../api/secretary_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) { alert('ตีกลับจากประธานเรียบร้อย'); location.reload(); }
                else alert('Error: ' + data.message);
            } catch(e) { alert('Connection Error'); }
        }
    </script>
</body>
</html>
