<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$userName = $_SESSION['fullname'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดโครงการ - Research Portal</title>
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #F3F4F6; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b shadow-sm sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 transition">
                        <i class="fa-solid fa-arrow-left text-xl"></i>
                    </a>
                    <span class="font-bold text-lg text-gray-800">รายละเอียดโครงการ</span>
                </div>
                <div class="flex items-center">
                   <div class="text-sm text-gray-500 mr-2">Login as:</div>
                   <div class="font-bold text-gray-700"><?php echo htmlspecialchars($userName); ?></div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">
        
        <!-- Loading State -->
        <div id="loading" class="text-center py-20">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
            <p class="text-gray-500">กำลังโหลดข้อมูล...</p>
        </div>

        <!-- Content (Hidden initially) -->
        <div id="content" class="hidden space-y-6">
            
            <!-- Header Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <i class="fa-solid fa-folder-open text-8xl"></i>
                </div>
                
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 relative z-10 w-full">
                    <div class="flex-1">
                        <div class="mb-2">
                             <span id="p-status-badge" class="px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-600">
                                Loading...
                             </span>
                        </div>
                        <h1 id="p-title-th" class="text-2xl font-bold text-gray-900 mb-1"></h1>
                        <h2 id="p-title-en" class="text-lg text-gray-500 font-medium mb-4"></h2>
                        
                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                            <div class="flex items-center"><i class="fa-solid fa-tag w-5 text-blue-500"></i> <span id="p-type"></span></div>
                            <div class="flex items-center"><i class="fa-solid fa-coins w-5 text-yellow-500"></i> <span id="p-budget"></span></div>
                            <div class="flex items-center"><i class="fa-solid fa-calendar w-5 text-green-500"></i> ยื่นเมื่อ: <span id="p-date"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div id="progress-timeline" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-6 border-b pb-2 flex items-center">
                    <i class="fa-solid fa-list-check text-indigo-500 mr-2"></i> สถานะการดำเนินการ
                </h3>
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-0">
                    <!-- Step 1: ยื่นข้อเสนอ -->
                    <div class="flex items-center gap-3">
                        <div id="step-1" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-gray-100 text-gray-400 border-gray-200 shrink-0">
                            <i class="fa-solid fa-paper-plane"></i>
                        </div>
                        <div><div class="text-sm font-bold text-gray-700">ยื่นข้อเสนอ</div><div id="step-1-sub" class="text-xs text-gray-400"></div></div>
                    </div>
                    <div class="hidden md:block w-12 h-0.5 bg-gray-200" id="line-1-2"></div>

                    <!-- Step 2: เจ้าหน้าที่ -->
                    <div class="flex items-center gap-3">
                        <div id="step-2" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-gray-100 text-gray-400 border-gray-200 shrink-0">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div><div class="text-sm font-bold text-gray-700">เจ้าหน้าที่ตรวจสอบ</div><div id="step-2-sub" class="text-xs text-gray-400"></div></div>
                    </div>
                    <div class="hidden md:block w-12 h-0.5 bg-gray-200" id="line-2-3"></div>

                    <!-- Step 3: เลขานุการ -->
                    <div class="flex items-center gap-3">
                        <div id="step-3" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-gray-100 text-gray-400 border-gray-200 shrink-0">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>
                        <div><div class="text-sm font-bold text-gray-700">เลขาตรวจสอบ</div><div id="step-3-sub" class="text-xs text-gray-400"></div></div>
                    </div>
                    <div class="hidden md:block w-12 h-0.5 bg-gray-200" id="line-3-4"></div>

                    <!-- Step 4: ประธาน -->
                    <div class="flex items-center gap-3">
                        <div id="step-4" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-gray-100 text-gray-400 border-gray-200 shrink-0">
                            <i class="fa-solid fa-gavel"></i>
                        </div>
                        <div><div class="text-sm font-bold text-gray-700">ประธานพิจารณา</div><div id="step-4-sub" class="text-xs text-gray-400"></div></div>
                    </div>
                    <div class="hidden md:block w-12 h-0.5 bg-gray-200" id="line-4-5"></div>

                    <!-- Step 5: อนุมัติ -->
                    <div class="flex items-center gap-3">
                        <div id="step-5" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-gray-100 text-gray-400 border-gray-200 shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div><div class="text-sm font-bold text-gray-700">อนุมัติแล้ว</div><div id="step-5-sub" class="text-xs text-gray-400"></div></div>
                    </div>
                </div>

                <!-- Rejection Reason (if applicable) -->
                <div id="rejection-info" class="hidden mt-6 p-4 bg-red-50 border border-red-100 rounded-lg">
                    <div class="flex items-center gap-2 text-red-700 font-bold mb-2"><i class="fa-solid fa-exclamation-circle"></i> ถูกตีกลับ</div>
                    <div id="rejection-reason" class="text-sm text-red-600"></div>
                    <div id="rejection-issues" class="mt-2 text-sm text-red-500"></div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- General Info -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fa-solid fa-circle-info text-blue-500 mr-2"></i> ข้อมูลทั่วไป
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-gray-500">แหล่งทุน:</span>
                                <span id="p-source" class="col-span-2 font-medium text-gray-900"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-gray-500">ผู้ให้ทุน:</span>
                                <span id="p-funder" class="col-span-2 font-medium text-gray-900"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-gray-500">อาสาสมัคร < 18 ปี:</span>
                                <span id="p-volunteer" class="col-span-2 font-medium text-gray-900"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fa-solid fa-file-pdf text-red-500 mr-2"></i> เอกสารแนบ
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-gray-600 font-semibold">
                                    <tr>
                                        <th class="p-3 rounded-l-lg">ประเภทเอกสาร</th>
                                        <th class="p-3">ชื่อไฟล์</th>
                                        <th class="p-3 rounded-r-lg text-right">ดาวน์โหลด</th>
                                    </tr>
                                </thead>
                                <tbody id="docs-list" class="divide-y divide-gray-100">
                                </tbody>
                            </table>
                            <div id="no-docs" class="hidden text-center text-gray-400 py-4 italic">ไม่พบเอกสารแนบ</div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Team & Actions -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Team -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fa-solid fa-users text-indigo-500 mr-2"></i> ทีมวิจัย
                        </h3>
                        <div id="team-list" class="space-y-4">
                            <!-- Dynamic Content -->
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">การดำเนินการ</h3>
                        <div class="space-y-2">
                            <button onclick="window.print()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold transition">
                                <i class="fa-solid fa-print mr-2"></i> พิมพ์หน้านี้
                            </button>
                            <!-- Future: Export PDF, History Log -->
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('id');

            if (!projectId) {
                alert('ไม่พบรหัสโครงการ');
                window.location.href = 'dashboard.php';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'get_project');
            formData.append('project_id', projectId);

            try {
                const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                const result = await res.json();

                if (result.success) {
                    renderProject(result);
                    document.getElementById('loading').classList.add('hidden');
                    document.getElementById('content').classList.remove('hidden');
                } else {
                    alert('Error: ' + result.message);
                    window.location.href = 'dashboard.php';
                }
            } catch (e) {
                console.error(e);
                alert('Connection Error');
            }
        });

        function renderProject(data) {
            const p = data.project;
            const docs = data.documents;
            const team = data.team;

            // Header
            document.getElementById('p-title-th').innerText = p.title_th || '-';
            document.getElementById('p-title-en').innerText = p.title_en || '-';
            document.getElementById('p-type').innerText = p.research_type || '-';
            document.getElementById('p-budget').innerText = p.budget ? Number(p.budget).toLocaleString() + ' บาท' : '-';
            document.getElementById('p-date').innerText = p.submission_date ? new Date(p.submission_date).toLocaleDateString('th-TH') : (p.created_at ? new Date(p.created_at).toLocaleDateString('th-TH') : '-');

            // Status Badge
            const statusMap = {
                'approved': ['bg-green-100 text-green-700', 'อนุมัติแล้ว'],
                'pending': ['bg-yellow-100 text-yellow-700', 'รอตรวจสอบ'],
                'pending_approval': ['bg-yellow-100 text-yellow-700', 'รอตรวจสอบ'],
                'pending_officer': ['bg-yellow-100 text-yellow-700', 'รอเจ้าหน้าที่ตรวจสอบ'],
                'pending_secretary': ['bg-indigo-100 text-indigo-700', 'รอเลขาตรวจสอบ'],
                'pending_chairman': ['bg-amber-100 text-amber-700', 'รอประธานพิจารณา'],
                'working': ['bg-blue-100 text-blue-700', 'กำลังดำเนินการ'],
                'published': ['bg-purple-100 text-purple-700', 'ตีพิมพ์แล้ว'],
                'rejected': ['bg-red-100 text-red-700', 'ตีกลับ (เจ้าหน้าที่)'],
                'rejected_secretary': ['bg-red-100 text-red-700', 'ตีกลับ (เลขา)'],
                'draft': ['bg-gray-200 text-gray-600', 'แบบร่าง']
            };
            const st = statusMap[p.status] || ['bg-gray-100 text-gray-600', p.status];
            const badge = document.getElementById('p-status-badge');
            badge.className = `px-3 py-1 rounded-full text-sm font-bold ${st[0]}`;
            badge.innerText = st[1];

            // Progress Timeline
            updateTimeline(p);

            // General Info
            document.getElementById('p-source').innerText = (p.source_funds === 'external' ? 'ทุนภายนอก' : (p.source_funds === 'personal' ? 'ทุนส่วนตัว' : 'ทุนภายใน')) || '-';
            document.getElementById('p-funder').innerText = p.funder_name || '-';
            document.getElementById('p-volunteer').innerText = p.volunteers_under_18 == 1 ? 'มี' : 'ไม่มี';

            // Documents
            const docsList = document.getElementById('docs-list');
            if (docs.length > 0) {
                const docTypes = {
                    '1': 'บันทึกข้อความ (Memo)',
                    '2': 'แบบเสนอโครงการ (Proposal)',
                    '3': 'เอกสารแจงข้อมูล (Participant Info)',
                    '4': 'แบบยินยอม (Consent Form)',
                    '5': 'เครื่องมือวิจัย (Instruments)',
                    '6': 'ประวัติผู้วิจัย (CV)'
                };
                docs.forEach(d => {
                    const row = document.createElement('tr');
                    const uploadPath = `uploads/projects/${p.id}/${d.file_path}`;
                    row.innerHTML = `
                        <td class="p-3 text-gray-800 font-medium">${docTypes[d.doc_type] || 'เอกสารอื่นๆ'}</td>
                        <td class="p-3 text-gray-500 truncate max-w-[150px]" title="${d.file_path}">${d.doc_name || d.file_path}</td>
                        <td class="p-3 text-right">
                            <a href="${uploadPath}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                <i class="fa-solid fa-download mr-1"></i> โหลด
                            </a>
                        </td>
                    `;
                    docsList.appendChild(row);
                });
            } else {
                document.getElementById('no-docs').classList.remove('hidden');
            }

            // Team
            const teamList = document.getElementById('team-list');
            
            // Add Self (Head)
            teamList.innerHTML += `
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                     <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                        H
                     </div>
                     <div>
                        <div class="font-bold text-gray-900">ตัวคุณ (หัวหน้าโครงการ)</div>
                        <div class="text-xs text-blue-600 font-semibold">Head Project</div>
                     </div>
                </div>
            `;

            if (team && team.length > 0) {
                team.forEach(m => {
                    const initial = m.firstname.charAt(0).toUpperCase();
                    const statusColor = m.response_status === 'accepted' ? 'text-green-500' : (m.response_status === 'rejected' ? 'text-red-500' : 'text-yellow-500');
                    const statusIcon = m.response_status === 'accepted' ? 'fa-check-circle' : (m.response_status === 'rejected' ? 'fa-times-circle' : 'fa-clock');
                    
                    teamList.innerHTML += `
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg group hover:bg-white border border-transparent hover:border-gray-200 transition">
                            <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-bold">
                                ${initial}
                            </div>
                            <div class="flex-grow">
                                <div class="font-bold text-gray-900">${m.firstname}</div>
                                <div class="text-xs text-gray-500 capitalize">${m.role}</div>
                            </div>
                            <div class="${statusColor}" title="${m.response_status}">
                                <i class="fa-solid ${statusIcon} text-lg"></i>
                            </div>
                        </div>
                    `;
                });
            }
        }

        function updateTimeline(p) {
            const status = p.status;
            // Determine current step (1-5)
            const stepMap = {
                'draft': 0,
                'pending_officer': 1, 'rejected': 1,
                'pending_secretary': 2, 'rejected_secretary': 2,
                'pending_chairman': 3,
                'approved': 4, 'working': 4, 'published': 4
            };
            const currentStep = stepMap[status] ?? 0;
            const isRejected = status === 'rejected' || status === 'rejected_secretary';

            // Style completed steps
            for (let i = 1; i <= 5; i++) {
                const el = document.getElementById('step-' + i);
                const sub = document.getElementById('step-' + i + '-sub');
                if (i <= currentStep) {
                    // Is this the rejected step?
                    if (i === currentStep + 1 && isRejected) {
                        el.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-red-100 text-red-600 border-red-300 shrink-0 ring-2 ring-red-200';
                        sub.innerText = 'ตีกลับ';
                        sub.className = 'text-xs text-red-500 font-bold';
                    } else {
                        el.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-green-500 text-white border-green-500 shrink-0';
                        sub.innerText = '✓ ผ่าน';
                        sub.className = 'text-xs text-green-500';
                    }
                } else if (i === currentStep + 1) {
                    if (isRejected) {
                        el.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-red-100 text-red-600 border-red-300 shrink-0 ring-2 ring-red-200';
                        sub.innerText = 'ตีกลับ';
                        sub.className = 'text-xs text-red-500 font-bold';
                    } else {
                        el.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-blue-100 text-blue-600 border-blue-300 shrink-0 ring-2 ring-blue-200 animate-pulse';
                        sub.innerText = 'กำลังดำเนินการ';
                        sub.className = 'text-xs text-blue-500 font-bold';
                    }
                }
                // Color connector lines
                if (i < 5) {
                    const line = document.getElementById('line-' + i + '-' + (i+1));
                    if (line && i <= currentStep) {
                        line.className = 'hidden md:block w-12 h-0.5 bg-green-400';
                    }
                }
            }

            // Step 1 special: always "submitted" if past draft
            if (currentStep >= 1) {
                const sub1 = document.getElementById('step-1-sub');
                sub1.innerText = '✓ ยื่นแล้ว';
                sub1.className = 'text-xs text-green-500';
            }
            // Step 5 if approved
            if (status === 'approved' || status === 'working' || status === 'published') {
                const el5 = document.getElementById('step-5');
                el5.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 bg-green-500 text-white border-green-500 shrink-0';
                const sub5 = document.getElementById('step-5-sub');
                sub5.innerText = '✓ อนุมัติ';
                sub5.className = 'text-xs text-green-500';
            }

            // Show rejection info
            if (isRejected && (p.return_reason || p.return_issues)) {
                document.getElementById('rejection-info').classList.remove('hidden');
                document.getElementById('rejection-reason').innerText = 'เหตุผล: ' + (p.return_reason || '-');
                try {
                    const issues = JSON.parse(p.return_issues || '[]');
                    if (issues.length > 0) {
                        document.getElementById('rejection-issues').innerHTML = 'สิ่งที่ต้องแก้ไข: ' + issues.map(i => '<span class="inline-block bg-red-100 text-red-600 px-2 py-0.5 rounded text-xs mr-1 mb-1">' + i + '</span>').join('');
                    }
                } catch(e) {}
            }
        }
    </script>
</body>
</html>
