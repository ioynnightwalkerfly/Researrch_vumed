<?php
// secretary/review.php
session_start();
require_once '../api/db.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$projectId = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT p.*, u.firstname_th, u.lastname_th, u.email as user_email FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project) { echo "Project not found"; exit(); }

    $stmtDocs = $conn->prepare("SELECT * FROM project_documents WHERE project_id = ?");
    $stmtDocs->execute([$projectId]);
    $documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

    $stmtTeam = $conn->prepare("SELECT * FROM project_team WHERE project_id = ?");
    $stmtTeam->execute([$projectId]);
    $team = $stmtTeam->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบโครงการ - Secretary Review</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Sarabun', sans-serif; background-color: #F8FAFC; } </style>
</head>
<body class="bg-gray-50 min-h-screen pb-12">

    <header class="bg-white shadow-sm border-b sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-500 hover:text-indigo-600 transition"><i class="fa-solid fa-arrow-left"></i> กลับ</a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="font-bold text-gray-800 text-lg">ตรวจสอบโครงการ (เลขานุการ)</h1>
            </div>
            <div class="text-sm text-gray-500">
                Project ID: <span class="font-mono text-gray-800 font-bold">#<?php echo $project['id']; ?></span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                        <h2 class="font-bold text-indigo-800"><i class="fa-solid fa-file-invoice mr-2"></i> ข้อมูลทั่วไป</h2>
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold border border-indigo-200">
                            <?php echo $project['status']; ?>
                        </span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase">ชื่อโครงการ (TH)</label>
                            <div class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($project['title_th']); ?></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase">ประเภทการวิจัย</label>
                                <div class="font-medium text-gray-700"><?php echo $project['research_type']; ?></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase">แหล่งทุน</label>
                                <div class="font-medium text-gray-700"><?php echo $project['source_funds']; ?></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase">ผู้วิจัย</label>
                                <div class="font-medium text-gray-700"><?php echo htmlspecialchars($project['firstname_th'] . ' ' . $project['lastname_th']); ?></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase">อีเมลผู้วิจัย</label>
                                <div class="font-medium text-gray-700"><?php echo htmlspecialchars($project['user_email']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                        <h2 class="font-bold text-indigo-800"><i class="fa-solid fa-folder-open mr-2"></i> เอกสารแนบ</h2>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 font-semibold border-b"><tr>
                                <th class="px-6 py-3 w-10"></th><th class="px-6 py-3">ชื่อเอกสาร</th><th class="px-6 py-3 text-right">ไฟล์</th><th class="px-6 py-3 text-center w-32">ตรวจสอบ</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($documents as $doc): ?>
                                <tr class="group hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-center"><i class="fa-solid fa-file-pdf text-red-500 text-lg"></i></td>
                                    <td class="px-6 py-4"><div class="font-medium text-gray-800"><?php echo htmlspecialchars($doc['doc_name'] ?? 'Document'); ?></div></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="../uploads/projects/<?php echo $projectId . '/' . $doc['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                            <i class="fa-solid fa-eye mr-1"></i> ดูไฟล์
                                        </a>
                                    </td>
                                        <td class="px-6 py-4 text-center bg-gray-50/50">
                                            <div class="flex flex-col gap-2">
                                                <label class="flex items-center gap-2 cursor-pointer text-sm mb-1" onclick="document.getElementById('doc_comment_<?php echo $doc['id']; ?>').classList.add('hidden')">
                                                    <input type="radio" name="doc_status_<?php echo $doc['id']; ?>" value="correct" class="w-4 h-4 text-green-600 focus:ring-green-500 border-gray-300">
                                                    <span class="text-green-700">✅ คุณสมบัติครบ</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer text-sm" onclick="document.getElementById('doc_comment_<?php echo $doc['id']; ?>').classList.remove('hidden'); document.getElementById('doc_comment_<?php echo $doc['id']; ?>').focus();">
                                                    <input type="radio" name="doc_status_<?php echo $doc['id']; ?>" value="revision" class="w-4 h-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                                                    <span class="text-orange-600">⚠️ มีการแก้ไข</span>
                                                </label>
                                                <textarea id="doc_comment_<?php echo $doc['id']; ?>" rows="2" class="hidden mt-2 p-2.5 text-sm border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-orange-500 outline-none" placeholder="ระบุรายละเอียดที่ต้องแก้ไข..."></textarea>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Action Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 sticky top-24">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="font-bold text-gray-800 text-lg">ผลการพิจารณา (เลขานุการ)</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-green-50 transition" onclick="toggleReason(false)">
                                <input type="radio" name="action" value="approve" class="w-5 h-5 text-green-600" checked>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-800">✅ คุณสมบัติครบ</span>
                                    <span class="block text-xs text-gray-500">นำเข้าวาระการประชุม</span>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-orange-50 transition" onclick="toggleReason(true)">
                                <input type="radio" name="action" value="return" class="w-5 h-5 text-orange-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-800">⚠️ คุณสมบัติไม่ครบ</span>
                                    <span class="block text-xs text-gray-500">แจ้งสิ่งที่ต้องแก้ไขกลับไปยังผู้วิจัย</span>
                                </div>
                            </label>
                        </div>

                        <div id="return-reason-area" class="hidden space-y-3">
                            <div class="bg-orange-50 p-5 rounded-lg border border-orange-200 text-sm text-gray-800">
                                <p class="font-bold mb-3 text-orange-800"><i class="fa-solid fa-list-check mr-2"></i>สิ่งที่ต้องแก้ไขเชิงคุณสมบัติ (ภาพรวม):</p>
                                <textarea id="reason-text" rows="5" class="w-full p-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="ระบุเหตุผลอธิบายเพิ่มเติมที่ต้องแก้ไขภาพรวม..."></textarea>
                            </div>
                        </div>

                        <button onclick="submitReview()" class="w-full py-3 px-4 bg-gray-900 text-white rounded-lg font-bold shadow-lg hover:bg-black transition">
                            ยืนยันผลการพิจารณา
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleAction(action) {
            const area = document.getElementById('return-reason-area');
            if(action === 'return') area.classList.remove('hidden');
            else area.classList.add('hidden');
        }

        async function submitReview() {
            const action = document.querySelector('input[name="action"]:checked').value;
            const projectId = <?php echo $projectId; ?>;
            
            // Collect documents marked as 'revision' in the table along with their comments
            const revisionDocs = [];
            document.querySelectorAll('input[type="radio"][value="revision"]:checked').forEach(radio => {
                const tr = radio.closest('tr');
                const docName = tr.querySelector('.font-medium').innerText.trim();
                const docId = radio.name.replace('doc_status_', '');
                const commentInput = document.getElementById('doc_comment_' + docId);
                const comment = commentInput && commentInput.value.trim() !== '' ? ` (หมายเหตุ: ${commentInput.value.trim()})` : '';
                revisionDocs.push('ต้องแก้ไขเอกสาร: ' + docName + comment);
            });
            
            const allIssues = [...revisionDocs];
            const reason = document.getElementById('reason-text').value;

            if (action === 'return' && allIssues.length === 0 && reason.trim() === '') {
                alert('กรุณาระบุสิ่งที่ต้องแก้ไขภาพรวม หรือเลือกเอกสารที่ไม่ครบ/ต้องแก้ไข');
                return;
            }

            const confirmMsg = action === 'approve' ? 'ยืนยันส่งต่อประธาน?' : 
                               action === 'final_approve' ? 'ยืนยันอนุมัติขั้นสุดท้าย?' : 'ยืนยันตีกลับ?';
            if(!confirm(confirmMsg)) return;

            const formData = new FormData();
            formData.append('action', 'secretary_review');
            formData.append('sub_action', action);
            formData.append('project_id', projectId);
            if(action === 'return') {
                formData.append('reason', reason);
                formData.append('issues', JSON.stringify(allIssues));
            }

            try {
                const res = await fetch('../api/secretary_action.php', { method: 'POST', body: formData });
                const result = await res.json();
                if(result.success) {
                    alert('บันทึกสำเร็จ');
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch(e) { alert('Connection Error'); }
        }
    </script>
</body>
</html>
