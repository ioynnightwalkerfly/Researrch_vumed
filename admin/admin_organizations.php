<?php
session_start();
require_once '../api/db.php';

// Check Admin Role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff', 'secretary'])) {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหน่วยงานความร่วมมือ - Admin Panel</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <?php include 'includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 shadow-sm">
            <h2 class="text-xl font-bold text-gray-800">จัดการหน่วยงานความร่วมมือ (Organizations)</h2>
            <div class="text-sm text-gray-500">สำหรับหน้าบริการวิชาการ</div>
        </header>

        <div class="flex-grow overflow-y-auto p-8">
            <!-- Summary Stats & Info -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-6 border-l-4 border-blue-500">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-info-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">ระบบฐานข้อมูลแบบทำงานร่วมกัน (Hybrid Data Platform)</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            ข้อมูลสำหรับวาดกราฟเครือข่ายความร่วมมือบนหน้าเว็บไซต์ <a href="../academic_service_landing.html" target="_blank" class="text-blue-600 hover:underline">Academic Service</a> 
                            จะถูกอ่านจาก <strong>VumedHR API</strong> และ <strong>ฐานข้อมูลนี้</strong> นำมารวมเข้าด้วยกันแบบ Real-time<br>
                            * ข้อมูลจาก API จะอิงตามที่มีผู้ใช้งานจริงในระบบไม่สามารถแก้ไขได้<br>
                            * ข้อมูลแบบ Manual ด้านล่างสามารถแก้ไขเพิ่ม/ลบได้อย่างอิสระโดยไม่ต้องรอข้อมูลจาก API
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Manual Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-blue-50 px-6 py-3 border-b border-blue-100 flex items-center justify-between">
                        <h4 class="font-bold text-blue-800"><i class="fa-solid fa-database mr-2"></i> ข้อมูลบันทึกเอง (Manual)</h4>
                        <span id="stat-manual-total" class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold">กำลังโหลด...</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4" id="stat-manual-breakdown">
                            <div class="text-sm text-gray-500">กำลังประมวลผล...</div>
                        </div>
                    </div>
                </div>

                <!-- API Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-emerald-50 px-6 py-3 border-b border-emerald-100 flex items-center justify-between">
                        <h4 class="font-bold text-emerald-800"><i class="fa-solid fa-cloud-download-alt mr-2"></i> ข้อมูลจากระบบ VumedHR API</h4>
                        <span id="stat-api-total" class="bg-emerald-600 text-white px-3 py-1 rounded-full text-xs font-bold">กำลังโหลด...</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4" id="stat-api-breakdown">
                            <div class="text-sm text-gray-500">กำลังประมวลผล...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-4 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button id="tab-manual" class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        <i class="fa-solid fa-database"></i> ข้อมูลบันทึกเอง (Manual)
                    </button>
                    <button id="tab-api" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        <i class="fa-solid fa-cloud-download-alt"></i> ข้อมูลจาพ VumedHR API (Read-only)
                    </button>
                </nav>
            </div>

            <!-- Content Area: Manual Data -->
            <div id="content-manual" class="block">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-gray-700">รายการองค์กรเครือข่าย (Manual)</h3>
                        <button onclick="openAddModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> เพิ่มหน่วยงานใหม่
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white text-gray-500 font-semibold border-b">
                                <tr>
                                    <th class="px-6 py-3">รหัสอ้างอิง (Node ID)</th>
                                    <th class="px-6 py-3">ชื่อหน่วยงาน</th>
                                    <th class="px-6 py-3">ประเภท</th>
                                    <th class="px-6 py-3 text-center">จำนวนงาน</th>
                                    <th class="px-6 py-3 text-center">กลุ่ม (Group)</th>
                                    <th class="px-6 py-3 text-right">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody id="manualTableBody" class="divide-y divide-gray-100">
                                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">กำลังโหลด...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Content Area: API Data -->
            <div id="content-api" class="hidden">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-gray-700">ข้อมูลหน่วยงานจาก VumedHR API</h3>
                        <div class="text-xs text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-200 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> ซิงค์สด (Real-time)
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white text-gray-500 font-semibold border-b">
                                <tr>
                                    <th class="px-6 py-3">รหัสอ้างอิง (Node ID)</th>
                                    <th class="px-6 py-3">ชื่อหน่วยงาน</th>
                                    <th class="px-6 py-3">ประเภท</th>
                                    <th class="px-6 py-3 text-center">จำนวนงาน</th>
                                    <th class="px-6 py-3 text-center">กลุ่ม (Group)</th>
                                </tr>
                            </thead>
                            <tbody id="apiTableBody" class="divide-y divide-gray-100">
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">กำลังดึงข้อมูล...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal Form: Add / Edit -->
    <div id="orgModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 id="modalTitle" class="font-bold text-lg text-gray-800">เพิ่มองค์กรความร่วมมือ</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="px-6 py-4 overflow-y-auto">
                <form id="orgForm">
                    <input type="hidden" id="org_id" name="id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รหัสอ้างอิง (Node ID) <span class="text-red-500">*</span></label>
                            <input type="text" id="node_id" name="node_id" required placeholder="เช่น SUT หรือ MAHIDOL" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">ต้องเป็นภาษาอังกฤษติดกัน เช่น CU, KKU (ห้ามซ้ำ)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหน่วยงาน <span class="text-red-500">*</span></label>
                            <input type="text" id="org_name" name="name" required placeholder="เช่น มหาวิทยาลัยเทคโนโลยีสุรนารี" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทสถาบัน</label>
                            <select id="category" name="category" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="academic">สถาบันการศึกษา (Academic)</option>
                                <option value="gov">หน่วยงานรัฐ/มูลนิธิ (Gov)</option>
                                <option value="journal">วารสารวิชาการ/นานาชาติ (Journal)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนนับ (ครั้ง/งาน)</label>
                                <input type="number" id="records_count" name="records_count" value="1" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">จัดกลุ่ม (Group ID)</label>
                                <select id="group_id" name="group_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="A">กลุ่มบริการวิชาการ/กรรมการ</option>
                                    <option value="B">กลุ่ม Reviewer บทความ</option>
                                    <option value="custom">กลุ่มอื่น ๆ</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition text-sm">ยกเลิก</button>
                <button type="button" onclick="saveOrg()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // DOM Elements
        const tabManual = document.getElementById('tab-manual');
        const tabApi = document.getElementById('tab-api');
        const contentManual = document.getElementById('content-manual');
        const contentApi = document.getElementById('content-api');
        const modal = document.getElementById('orgModal');
        const modalInner = modal.querySelector('div.bg-white');
        const form = document.getElementById('orgForm');
        let currentAction = 'add';

        // Tab Switching Logic
        tabManual.addEventListener('click', () => {
            tabManual.classList.add('border-blue-500', 'text-blue-600');
            tabManual.classList.remove('border-transparent', 'text-gray-500');
            tabApi.classList.remove('border-blue-500', 'text-blue-600');
            tabApi.classList.add('border-transparent', 'text-gray-500');
            contentManual.classList.remove('hidden');
            contentApi.classList.add('hidden');
        });

        tabApi.addEventListener('click', () => {
            tabApi.classList.add('border-blue-500', 'text-blue-600');
            tabApi.classList.remove('border-transparent', 'text-gray-500');
            tabManual.classList.remove('border-blue-500', 'text-blue-600');
            tabManual.classList.add('border-transparent', 'text-gray-500');
            contentApi.classList.remove('hidden');
            contentManual.classList.add('hidden');
            loadApiData(); // Load API dynamically when tab clicked
        });

        // Initialize Data
        document.addEventListener('DOMContentLoaded', () => {
            loadManualData();
            loadApiData(); // Pre-load API data to calculate stats
        });

        function getCategoryBadge(cat) {
            if(cat === 'academic') return '<span class="px-2 py-0.5 rounded text-xs font-bold border bg-blue-50 text-blue-600 border-blue-200">สถาบันการศึกษา</span>';
            if(cat === 'gov') return '<span class="px-2 py-0.5 rounded text-xs font-bold border bg-emerald-50 text-emerald-600 border-emerald-200">หน่วยงานรัฐ/มูลนิธิ</span>';
            if(cat === 'journal') return '<span class="px-2 py-0.5 rounded text-xs font-bold border bg-amber-50 text-amber-600 border-amber-200">วารสาร/นานาชาติ</span>';
            return '<span class="px-2 py-0.5 rounded text-xs font-bold border bg-gray-50 text-gray-600 border-gray-200">ทั่วไป</span>';
        }

        // =======================
        // Fetch Manual Data
        // =======================
        function loadManualData() {
            fetch('../api/admin_org_action.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('manualTableBody');
                if(!data.success) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">${data.message}</td></tr>`;
                    document.getElementById('stat-manual-total').innerText = '0 รายการ';
                    document.getElementById('stat-manual-breakdown').innerHTML = '<div class="text-sm text-red-500 col-span-2 p-2 bg-red-50 rounded text-center">โหลดข้อมูลล้มเหลว</div>';
                    return;
                }
                
                // Update Manual Stats
                const orgs = data.data || [];
                let manualBreakdown = { 'academic': 0, 'gov': 0, 'journal': 0, 'other': 0 };
                let totalManualRecords = 0;
                orgs.forEach(o => {
                    totalManualRecords += parseInt(o.records_count) || 0;
                    if (manualBreakdown[o.category] !== undefined) {
                        manualBreakdown[o.category]++;
                    } else {
                        manualBreakdown['other']++;
                    }
                });
                
                document.getElementById('stat-manual-total').innerHTML = `
                    <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs font-bold">${orgs.length} องค์กร</span>
                    <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs ml-1">${totalManualRecords} เรคคอร์ด</span>
                `;
                
                document.getElementById('stat-manual-breakdown').innerHTML = `
                    <div class="flex flex-col bg-gray-50 p-2 rounded border border-gray-100"><span class="text-xs text-gray-500 mb-1">สถาบันการศึกษา</span><span class="font-bold text-lg text-blue-700">${manualBreakdown.academic}</span></div>
                    <div class="flex flex-col bg-gray-50 p-2 rounded border border-gray-100"><span class="text-xs text-gray-500 mb-1">รัฐ/มูลนิธิ</span><span class="font-bold text-lg text-emerald-700">${manualBreakdown.gov}</span></div>
                    <div class="flex flex-col bg-gray-50 p-2 rounded border border-gray-100"><span class="text-xs text-gray-500 mb-1">วารสาร/นานาชาติ</span><span class="font-bold text-lg text-amber-700">${manualBreakdown.journal}</span></div>
                    <div class="flex flex-col bg-gray-50 p-2 rounded border border-gray-100"><span class="text-xs text-gray-500 mb-1">ทั่วไป/อื่นๆ</span><span class="font-bold text-lg text-gray-700">${manualBreakdown.other}</span></div>
                `;

                if(orgs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">ยังไม่มีข้อมูลองค์กร แมนวล</td></tr>';
                    return;
                }

                tbody.innerHTML = data.data.map(org => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-mono text-sm text-gray-500">${org.node_id}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">${org.name}</td>
                        <td class="px-6 py-3">${getCategoryBadge(org.category)}</td>
                        <td class="px-6 py-3 text-center font-semibold text-gray-700">${org.records_count}</td>
                        <td class="px-6 py-3 text-center text-gray-500 text-xs">${org.group_id}</td>
                        <td class="px-6 py-3 text-right">
                            <button onclick='openEditModal(${JSON.stringify(org)})' class="text-blue-500 hover:text-blue-700 p-1 rounded" title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button onclick="deleteOrg(${org.id}, '${org.name}')" class="text-red-500 hover:text-red-700 p-1 rounded ml-2" title="ลบ">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }).catch(err => console.error(err));
        }

        // =======================
        // Fetch API Data
        // =======================
        let apiLoaded = false;
        function loadApiData() {
            if(apiLoaded) return;
            const tbody = document.getElementById('apiTableBody');
            
            // ใช้ URL เต็มตามที่ระบุในชุดทดสอบเพื่อป้องกันปัญหา path relative ผิดพลาด (ที่ทำให้โหลดหน้า HTML แทน JSON)
            fetch('https://vumedhr.vu.ac.th/vumedhr/public/api/get_organizations.php', {
                method: 'GET',
                headers: { 'Authorization': 'Bearer MHR-cf335b8cbe671f117fdb1a01e7a2af49', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if(!data || data.status !== 'success') throw new Error('Invalid API Response');
                
                let nodes = [];
                let apiBreakdown = {};
                let totalAPIRecords = data.total_records_with_org || 0; // ยึดตามเลขรวมที่ API ส่งมา

                // Extract from API groups
                if(data.groups) {
                    Object.keys(data.groups).forEach(gKey => {
                        const orgs = data.groups[gKey].organizations || [];
                        orgs.forEach(o => {
                            if(o.records_count > 0) {
                                nodes.push({ 
                                    name: o.name, 
                                    org_id: o.org_id || 'AUTO',
                                    category: o.category || 'other',
                                    count: o.records_count, 
                                    group: gKey 
                                });
                                apiBreakdown[gKey] = (apiBreakdown[gKey] || 0) + 1;
                            }
                        });
                    });
                }

                // Extract custom organizations
                if(data.custom_organizations) {
                    data.custom_organizations.forEach(o => {
                        if(o.records_count > 0) {
                            nodes.push({ 
                                name: o.name, 
                                org_id: o.org_id || 'AUTO_CUSTOM',
                                category: o.category || 'other',
                                count: o.records_count, 
                                group: 'Custom' 
                            });
                            apiBreakdown['Custom'] = (apiBreakdown['Custom'] || 0) + 1;
                        }
                    });
                }
                
                // Update API Stats
                document.getElementById('stat-api-total').innerHTML = `
                    <span class="bg-emerald-600 text-white px-2 py-1 rounded text-xs font-bold">${nodes.length} องค์กร</span>
                    <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs ml-1">${totalAPIRecords} เรคคอร์ด (จาก VumedHR)</span>
                `;
                let breakdownHtml = '';
                if (Object.keys(apiBreakdown).length > 0) {
                    for (const [group, count] of Object.entries(apiBreakdown)) {
                        breakdownHtml += `<div class="flex flex-col bg-gray-50 p-2 rounded border border-gray-100"><span class="text-xs text-gray-500 mb-1">กลุ่ม ${group}</span><span class="font-bold text-lg text-emerald-700">${count}</span></div>`;
                    }
                } else {
                    breakdownHtml = '<div class="text-sm text-gray-500 col-span-2 p-2 text-center bg-gray-50 rounded border border-gray-100">ไม่มีข้อมูลแยกกลุ่ม</div>';
                }
                document.getElementById('stat-api-breakdown').innerHTML = breakdownHtml;

                if(nodes.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">ไม่พบข้อมูลหน่วยงานที่มีจำนวนมากกว่า 0 โปรเจคใน VumedHR</td></tr>';
                    apiLoaded = true;
                    return;
                }

                tbody.innerHTML = nodes.map(n => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-mono text-sm text-blue-500 font-bold">${n.org_id}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">${n.name}</td>
                        <td class="px-6 py-3">${getCategoryBadge(n.category)}</td>
                        <td class="px-6 py-3 text-center font-semibold text-gray-700">${n.count}</td>
                        <td class="px-6 py-3 text-center text-gray-500 text-xs">${n.group}</td>
                    </tr>
                `).join('');
                apiLoaded = true;
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-400">ไม่สามารถเชื่อมต่อ VumedHR API ได้: <br><span class="text-xs">`+err.message+`</span></td></tr>`;
                document.getElementById('stat-api-total').innerText = '0 รายการ';
                document.getElementById('stat-api-breakdown').innerHTML = `<div class="text-sm text-red-500 col-span-2 p-2 bg-red-50 rounded text-center border border-red-100">โหลดข้อมูล API ล้มเหลว</div>`;
            });
        }

        // =======================
        // Modals & Forms
        // =======================
        function openAddModal() {
            currentAction = 'add';
            document.getElementById('modalTitle').innerText = 'เพิ่มองค์กรความร่วมมือ (Manual)';
            form.reset();
            document.getElementById('org_id').value = '';
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => modalInner.classList.remove('scale-95'), 10);
        }

        function openEditModal(org) {
            currentAction = 'edit';
            document.getElementById('modalTitle').innerText = 'แก้ไขข้อมูลองค์กร (Manual)';
            
            document.getElementById('org_id').value = org.id;
            document.getElementById('node_id').value = org.node_id;
            document.getElementById('org_name').value = org.name;
            document.getElementById('category').value = org.category;
            document.getElementById('records_count').value = org.records_count;
            document.getElementById('group_id').value = org.group_id;

            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => modalInner.classList.remove('scale-95'), 10);
        }

        function closeModal() {
            modalInner.classList.add('scale-95');
            setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 200);
        }

        function saveOrg() {
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            formData.append('action', currentAction);

            fetch('../api/admin_org_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, timer: 1500, showConfirmButton: false });
                    closeModal();
                    loadManualData();
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้' });
            });
        }

        function deleteOrg(id, name) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: `คุณต้องการลบ "${name}" หรือไม่? ข้อมูลจะถูกนำออกจากกราฟหน้าเว็บ`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    fetch('../api/admin_org_action.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', text: data.message, timer: 1500, showConfirmButton: false });
                            loadManualData();
                        } else {
                            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
