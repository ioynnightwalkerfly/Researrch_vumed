<?php
session_start();
require_once 'api/db.php';

// Check Login
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
    <title>ยื่นเสนอโครงการใหม่ - Research Portal</title>
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #F9FAFB; }
        .step-circle { width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 9999px; font-weight: bold; transition: all 0.2s; }
        .step-circle.active { background-color: #2563EB; color: white; border: 2px solid #2563EB; }
        .step-circle.completed { background-color: #10B981; color: white; border: 2px solid #10B981; }
        .step-circle.inactive { background-color: white; color: #9CA3AF; border: 2px solid #E5E7EB; }
        .input-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
        .input-group input, .input-group select, .input-group textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #D1D5DB; border-radius: 0.5rem; outline: none; transition: border-color 0.15s; }
        .input-group input:focus, .input-group select:focus, .input-group textarea:focus { border-color: #3B82F6; box-shadow: 0 0 0 2px #93C5FD; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="h-16 bg-white border-b flex items-center justify-between px-6 shadow-sm sticky top-0 z-30">
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-gray-500 hover:text-blue-600"><i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก</a>
            <div class="h-6 w-px bg-gray-300 mx-2"></div>
            <h1 class="font-bold text-gray-800 text-lg">ยื่นเสนอโครงการวิจัยใหม่</h1>
        </div>
        <div class="text-sm text-gray-500">
            ผู้ยื่น: <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($userName); ?></span>
        </div>
    </header>

    <!-- Steps Indicator -->
    <div class="bg-white border-b py-6 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between relative">
                <!-- Line -->
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-600 -z-10 transition-all duration-300" style="width: 0%" id="progress-bar"></div>

                <!-- Step 1 -->
                <div class="flex flex-col items-center bg-white px-2">
                    <div class="step-circle active" id="step-1-circle">1</div>
                    <span class="text-xs font-medium mt-1 text-blue-600">ข้อมูลทั่วไป</span>
                </div>
                <!-- Step 2 -->
                <div class="flex flex-col items-center bg-white px-2">
                    <div class="step-circle inactive" id="step-2-circle">2</div>
                    <span class="text-xs font-medium mt-1 text-gray-400">เอกสารงานวิจัย</span>
                </div>
                <!-- Step 3 -->
                <div class="flex flex-col items-center bg-white px-2">
                    <div class="step-circle inactive" id="step-3-circle">3</div>
                    <span class="text-xs font-medium mt-1 text-gray-400">ผู้ร่วมวิจัย</span>
                </div>
                 <!-- Step 4 -->
                <div class="flex flex-col items-center bg-white px-2">
                    <div class="step-circle inactive" id="step-4-circle">4</div>
                    <span class="text-xs font-medium mt-1 text-gray-400">ที่ปรึกษา</span>
                </div>
                 <!-- Step 5 -->
                <div class="flex flex-col items-center bg-white px-2">
                    <div class="step-circle inactive" id="step-5-circle">5</div>
                    <span class="text-xs font-medium mt-1 text-gray-400">ยืนยันการส่ง</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-50 py-8 px-4">
        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <form id="submissionForm" class="p-8">
                
                <!-- STEP 1: General Info -->
                <div id="step-1-content" class="step-content">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-blue-500"></i> 1. ข้อมูลทั่วไปโครงการ
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- 1.1 Research Type -->
                        <div class="input-group">
                            <label>1.1 ประเภทของโครงการวิจัย <span class="text-red-500">*</span></label>
                            <select name="research_type" class="bg-gray-50" required>
                                <option value="">-- กรุณาเลือก --</option>
                                <option value="health_science">โครงการวิจัยทางวิทยาศาสตร์สุขภาพ</option>
                                <option value="social_science">โครงการวิจัยทางสังคมศาสตร์</option>
                                <option value="science">โครงการทางวิทยาศาสตร์</option>
                                <option value="other">อื่นๆ</option>
                            </select>
                        </div>

                        <!-- 1.2 Title TH -->
                        <div class="input-group">
                            <label>1.2 ชื่อโครงการ (ภาษาไทย) <span class="text-red-500">*</span></label>
                            <input type="text" name="title_th" placeholder="ระบุชื่อโครงการภาษาไทย" required>
                        </div>

                        <!-- 1.3 Title EN -->
                        <div class="input-group">
                            <label>1.3 Research Title (English) <span class="text-red-500">*</span></label>
                            <input type="text" name="title_en" placeholder="Research Title in English" required>
                            <p class="text-xs text-gray-500 mt-1">* ชื่อนี้จะปรากฏในฐานข้อมูลและหนังสือแจ้งผล</p>
                        </div>

                        <!-- 1.4 Funding Source -->
                        <div class="input-group">
                            <label>1.4 แหล่งทุน <span class="text-red-500">*</span></label>
                            <select name="source_funds" class="bg-gray-50" onchange="toggleFunderInput(this)" required>
                                <option value="">-- กรุณาเลือก --</option>
                                <option value="personal">เงินทุนส่วนตัว</option>
                                <option value="foundation">ทุนจากมูลนิธิ องค์กรอิสระ หรือสมาคม</option>
                                <option value="government">ทุนจากหน่วยงานของรัฐบาลไทย</option>
                                <option value="university">ทุนภายในมหาวิทยาลัยวงษ์ชวลิตกุล</option>
                                <option value="private">ทุนสนับสนุนจากเอกชน</option>
                                <option value="other">อื่นๆ</option>
                            </select>
                        </div>

                        <!-- 1.5 Funder Name -->
                        <div class="input-group hidden" id="funder-name-group">
                            <label>1.5 ชื่อหน่วยงาน/บริษัทที่ให้ทุน</label>
                            <input type="text" name="funder_name" placeholder="ระบุชื่อหน่วยงาน">
                        </div>

                        <!-- 1.6 Volunteers < 18 -->
                        <div class="input-group">
                            <label>1.6 มีอาสาสมัครต่ำกว่า 18 ปี หรือไม่? <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer font-normal">
                                    <input type="radio" name="volunteers_under_18" value="0" checked class="w-4 h-4 text-blue-600">
                                    ไม่ใช่
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-normal">
                                    <input type="radio" name="volunteers_under_18" value="1" class="w-4 h-4 text-blue-600">
                                    ใช่ (มีอาสาสมัคร/ข้อมูล/ตัวอย่างชีวภาพ จากผู้เยาว์)
                                </label>
                            </div>
                        </div>

                    </div> <!-- End of space-y-6 -->

                    <div class="mt-10 pt-6 border-t flex justify-end">
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg shadow-lg font-bold transition transform hover:scale-105" onclick="nextStep(2)">
                            ถัดไป: เอกสารงานวิจัย <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Documents -->
                <div id="step-2-content" class="step-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-blue-500"></i> 2. เอกสารงานวิจัย
                    </h2>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <div>
                            <p class="font-bold">คำแนะนำ:</p>
                            <p>กรุณาอัปโหลดเอกสารที่เกี่ยวข้องให้ครบถ้วน หากไม่มีเอกสารใดให้ข้ามไป (ถ้าไม่บังคับ) หากมีเอกสารเพิ่มเติมนอกเหนือจากรายการ ให้กดปุ่ม "เพิ่มเอกสารอื่นๆ"</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-sm border-b uppercase tracking-wider">
                                    <th class="p-3 text-center w-12">#</th>
                                    <th class="p-3">ชื่อเอกสาร</th>
                                    <th class="p-3 w-20">Ver.</th>
                                    <th class="p-3 w-28">วันที่ยื่นเอกสาร</th>
                                    <th class="p-3 text-center w-20">จำเป็น</th>
                                    <th class="p-3 w-40 text-center">ไฟล์</th>
                                    <th class="p-3 w-24 text-center">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-100">
                                <!-- Row 1: Payment Proof -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">1</td>
                                    <td class="p-3 font-medium">หลักฐานการชำระเงิน <span class="text-red-500">*</span></td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center"><span class="text-red-500 font-bold">ใช่</span></td>
                                    <td class="p-3 text-center text-xs text-gray-400" id="doc-status-1">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(1)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-1" class="hidden" onchange="updateFileState(1, this)">
                                    </td>
                                </tr>
                                <!-- Row 2: Exemption Form -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">2</td>
                                    <td class="p-3">
                                        <div class="font-medium">แบบเสนอเพื่อขอยกเว้นการพิจารณาฯ</div>
                                        <div class="flex gap-2 mt-1">
                                            <a href="#" class="text-xs text-blue-500 hover:underline"><i class="fa-solid fa-download"></i> TH Form</a>
                                            <a href="#" class="text-xs text-blue-500 hover:underline"><i class="fa-solid fa-download"></i> EN Form</a>
                                        </div>
                                    </td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center"><span class="text-red-500 font-bold">ใช่</span></td>
                                    <td class="p-3 text-center text-xs text-gray-400" id="doc-status-2">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(2)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-2" class="hidden" onchange="updateFileState(2, this)">
                                    </td>
                                </tr>
                                <!-- Row 3: Full Proposal -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">3</td>
                                    <td class="p-3 font-medium">โครงการวิจัยฉบับสมบูรณ์ (Full Proposal) <span class="text-red-500">*</span></td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center"><span class="text-red-500 font-bold">ใช่</span></td>
                                    <td class="p-3 text-center text-xs text-gray-400" id="doc-status-3">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(3)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-3" class="hidden" onchange="updateFileState(3, this)">
                                    </td>
                                </tr>
                                <!-- Row 4: Instruments -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">4</td>
                                    <td class="p-3">
                                        <div class="font-medium">เครื่องมือที่ใช้ในการวิจัย</div>
                                        <div class="text-xs text-gray-500">(เช่น แบบบันทึกข้อมูล, แบบสอบถาม)</div>
                                    </td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center text-gray-400 text-xs">ถ้ามี</td>
                                    <td class="p-3 text-center text-xs text-gray-400">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(4)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-4" class="hidden" onchange="updateFileState(4, this)">
                                    </td>
                                </tr>
                                <!-- Row 5: Bio Samples -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">5</td>
                                    <td class="p-3 font-medium">หนังสือขออนุญาตใช้ตัวอย่างชีวภาพ <span class="text-xs text-gray-500">(ถ้าเกี่ยวข้อง)</span></td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center text-gray-400 text-xs">ถ้ามี</td>
                                    <td class="p-3 text-center text-xs text-gray-400">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(5)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-5" class="hidden" onchange="updateFileState(5, this)">
                                    </td>
                                </tr>
                                <!-- Row 6: Other Related -->
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-center">6</td>
                                    <td class="p-3 font-medium">เอกสารประกอบอื่นๆ ที่เกี่ยวข้อง <span class="text-xs text-gray-500">(ถ้าเกี่ยวข้อง)</span></td>
                                    <td class="p-3"><input type="text" value="1.0" class="w-full border rounded px-1 py-0.5 text-xs text-center"></td>
                                    <td class="p-3"><input type="date" class="w-full border rounded px-1 py-0.5 text-xs"></td>
                                    <td class="p-3 text-center text-gray-400 text-xs">ถ้ามี</td>
                                    <td class="p-3 text-center text-xs text-gray-400">ยังไม่เลือกไฟล์</td>
                                    <td class="p-3 text-center">
                                        <button type="button" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm" onclick="triggerUpload(6)">
                                            <i class="fa-solid fa-upload mr-1"></i> อัปโหลด
                                        </button>
                                        <input type="file" id="file-6" class="hidden" onchange="updateFileState(6, this)">
                                    </td>
                                </tr>
                            </tbody>
                            <!-- Dynamic Body for Added Docs -->
                            <tbody id="other-docs-body" class="text-sm divide-y divide-gray-100 border-t border-gray-200 bg-yellow-50/30">
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <button type="button" class="text-blue-600 text-sm hover:underline"><i class="fa-solid fa-rotate-right"></i> โหลดใหม่</button>
                        <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition" onclick="openAddDocModal()">
                            <i class="fa-solid fa-plus mr-1"></i> เพิ่มเอกสารอื่นๆ
                        </button>
                    </div>

                    <!-- Navigation -->
                    <div class="mt-10 pt-6 border-t flex justify-between">
                        <button type="button" class="text-gray-500 hover:text-gray-700 font-semibold px-4" onclick="prevStep(1)">
                            <i class="fa-solid fa-arrow-left mr-2"></i> ย้อนกลับ
                        </button>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg shadow-lg font-bold transition transform hover:scale-105" onclick="nextStep(3)">
                            ถัดไป: ผู้ร่วมวิจัย <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                </div>

                <!-- STEP 3: Co-researchers -->
                <div id="step-3-content" class="step-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b flex items-center gap-2">
                        <i class="fa-solid fa-users text-blue-500"></i> 3. ผู้ร่วมวิจัย
                    </h2>

                    <div class="mb-6 flex gap-2">
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition" onclick="openSearchModal('co_researcher')">
                            <i class="fa-solid fa-user-plus mr-2"></i> เพิ่มผู้ร่วมวิจัย
                        </button>
                        <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition" onclick="loadTeamData()">
                            <i class="fa-solid fa-rotate-right mr-1"></i> รีโหลดข้อมูล
                        </button>
                    </div>

                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-sm border-b uppercase tracking-wider">
                                    <th class="p-3">ชื่อ-นามสกุล</th>
                                    <th class="p-3">บทบาท</th>
                                    <th class="p-3 text-center">สถานะการเชิญ</th>
                                    <th class="p-3 text-center">การตอบรับ</th>
                                    <th class="p-3 text-center">วันที่ตอบรับ</th>
                                    <th class="p-3 text-center">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody id="co-researcher-list" class="text-sm divide-y divide-gray-100">
                                <!-- Self as Head Project -->
                                <tr class="bg-blue-50/50">
                                    <td class="p-3 font-semibold text-gray-800"><?php echo htmlspecialchars($userName); ?></td>
                                    <td class="p-3 text-blue-600">หัวหน้าโครงการ</td>
                                    <td class="p-3 text-center">-</td>
                                    <td class="p-3 text-center"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-bold">ยืนยันแล้ว</span></td>
                                    <td class="p-3 text-center text-gray-400"><i class="fa-solid fa-lock"></i></td>
                                </tr>
                                <!-- Example Row (Mock) -->
                                <!-- Will be populated by JS -->
                            </tbody>
                        </table>
                        <div id="no-co-researcher" class="p-4 text-center text-gray-400 text-sm italic">
                            ยังไม่มีผู้ร่วมวิจัยเพิ่มเติม
                        </div>
                    </div>

                    <div class="mt-10 pt-6 border-t flex justify-between">
                        <button type="button" class="text-gray-500 hover:text-gray-700 font-semibold px-4" onclick="prevStep(2)">
                            <i class="fa-solid fa-arrow-left mr-2"></i> ย้อนกลับ
                        </button>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg shadow-lg font-bold transition transform hover:scale-105" onclick="nextStep(4)">
                            ถัดไป: ที่ปรึกษา <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Advisor -->
                <div id="step-4-content" class="step-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b flex items-center gap-2">
                        <i class="fa-solid fa-user-tie text-blue-500"></i> 4. อาจารย์ที่ปรึกษา
                    </h2>

                    <div class="mb-6 flex gap-2">
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition" onclick="openSearchModal('advisor')">
                            <i class="fa-solid fa-user-plus mr-2"></i> เพิ่มที่ปรึกษา
                        </button>
                        <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition" onclick="loadTeamData()">
                            <i class="fa-solid fa-rotate-right mr-1"></i> รีโหลดข้อมูล
                        </button>
                    </div>

                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-sm border-b uppercase tracking-wider">
                                    <th class="p-3">ชื่อ-นามสกุล</th>
                                    <th class="p-3">ตำแหน่ง</th>
                                    <th class="p-3 text-center">สถานะการเชิญ</th>
                                    <th class="p-3 text-center">การตอบรับ</th>
                                    <th class="p-3 text-center">วันที่ตอบรับ</th>
                                    <th class="p-3 text-center">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody id="advisor-list" class="text-sm divide-y divide-gray-100">
                                <!-- Will be populated by JS -->
                            </tbody>
                        </table>
                         <div id="no-advisor" class="p-4 text-center text-gray-400 text-sm italic">
                            ยังไม่มีอาจารย์ที่ปรึกษา
                        </div>
                    </div>

                    <div class="mt-10 pt-6 border-t flex justify-between">
                        <button type="button" class="text-gray-500 hover:text-gray-700 font-semibold px-4" onclick="prevStep(3)">
                            <i class="fa-solid fa-arrow-left mr-2"></i> ย้อนกลับ
                        </button>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg shadow-lg font-bold transition transform hover:scale-105" onclick="prepareSummary()">
                            ถัดไป: สรุปและยืนยัน <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 5: Confirmation -->
                <div id="step-5-content" class="step-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-blue-500"></i> 5. ยืนยันการส่งโครงการ
                    </h2>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800">
                        <p class="font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> หมายเหตุสำคัญ:</p>
                        <p>กรุณาตรวจสอบความถูกต้องของข้อมูล หากผู้ร่วมวิจัยหรือที่ปรึกษายังไม่กด "ยอมรับ" โครงการ ระบบอาจจะยังไม่ส่งเรื่องเข้าสู่กระบวนการพิจารณา</p>
                    </div>

                    <div class="space-y-4" id="summary-content">
                        <!-- Summary Loaded via JS -->
                    </div>

                    <div class="mt-10 pt-6 border-t flex justify-between">
                        <button type="button" class="text-gray-500 hover:text-gray-700 font-semibold px-4" onclick="prevStep(4)">
                            <i class="fa-solid fa-arrow-left mr-2"></i> ย้อนกลับ
                        </button>
                        <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-lg shadow-lg font-bold transition transform hover:scale-105" onclick="submitProject()">
                            <i class="fa-solid fa-paper-plane mr-2"></i> ยืนยันและส่งโครงการ
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <!-- Search Modal -->
    <div id="search-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden transform transition-all scale-100">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800" id="modal-title">ค้นหาผู้ร่วมวิจัย</h3>
                <button onclick="closeSearchModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6">
                <div class="relative mb-4">
                    <input type="text" id="search-input" placeholder="พิมพ์ชื่อ, นามสกุล หรืออีเมล..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none transition">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                
                <div class="mb-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">ผลการค้นหา</div>
                <div id="search-results" class="space-y-2 max-h-60 overflow-y-auto">
                    <!-- Mock Results -->
                    <div class="p-3 border rounded-lg hover:bg-blue-50 cursor-pointer flex justify-between items-center group transition" onclick="selectUser('ดร. สมชาย ใจดี', 'somchai@example.com')">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">ส</div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">ดร. สมชาย ใจดี</p>
                                <p class="text-xs text-gray-500">คณะเทคโนโลยีสารสนเทศ</p>
                            </div>
                        </div>
                        <button class="text-blue-600 opacity-0 group-hover:opacity-100 text-sm font-semibold transition">เลือก</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Custom Document Modal -->
    <div id="add-doc-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all scale-100">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-file-circle-plus text-blue-600 mr-2"></i> เพิ่มเอกสารอื่นๆ</h3>
                <button onclick="closeAddDocModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="input-group">
                    <label>ชื่อเอกสาร (ภาษาไทย)</label>
                    <input type="text" id="custom-doc-name-th" placeholder="ระบุชื่อเอกสารภาษาไทย" class="w-full border p-2 rounded">
                </div>
                <div class="input-group">
                    <label>ชื่อเอกสาร (ภาษาอังกฤษ)</label>
                    <input type="text" id="custom-doc-name-en" placeholder="Document Name (English)" class="w-full border p-2 rounded">
                </div>
                <div class="grid grid-cols-2 gap-4">
                     <div class="input-group">
                        <label>เวอร์ชั่น (ฉบับที่)</label>
                        <input type="text" id="custom-doc-ver" placeholder="เช่น 1.0" class="w-full border p-2 rounded text-center">
                    </div>
                    <div class="input-group">
                        <label>วันที่เอกสาร</label>
                        <input type="date" id="custom-doc-date" class="w-full border p-2 rounded">
                    </div>
                </div>
                 <div class="input-group">
                    <label>ไฟล์เอกสาร (Word/PDF เท่านั้น)</label>
                    <input type="file" id="custom-doc-file" accept=".pdf,.doc,.docx" class="w-full border p-1 rounded text-sm text-gray-500 bg-gray-50">
                </div>

                <div class="pt-4 flex justify-end gap-2">
                    <button onclick="closeAddDocModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">ยกเลิก</button>
                    <button onclick="saveOtherDoc()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg">เพิ่มเอกสาร</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 5;
        let currentProjectId = null; // Store Draft ID

        // 1. Save Draft (Step 1)
        async function saveDraft() {
            const form = document.getElementById('submissionForm');
            const formData = new FormData(form);
            formData.append('action', 'save_draft');
            if (currentProjectId) formData.append('project_id', currentProjectId);

            // Basic Validation Check
            if(!formData.get('research_type') || !formData.get('title_th')) {
                 alert('กรุณากรอกข้อมูลจำเป็นให้ครบถ้วน (ประเภทโครงการ, ชื่อโครงการ)');
                 return false;
            }

            try {
                const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.success) {
                    currentProjectId = result.project_id;
                    // Update URL so refresh keeps data
                    const newUrl = window.location.pathname + '?id=' + currentProjectId;
                    window.history.replaceState({path:newUrl}, '', newUrl);
                    console.log('Draft Saved:', currentProjectId);
                    return true;
                } else {
                    alert('Error: ' + result.message);
                    return false;
                }
            } catch (e) {
                console.error(e);
                alert('Connection Error');
                return false;
            }
        }

        // Navigation
        async function nextStep(targetStep) {
            // Validation Steps
            if (currentStep === 1) {
                // Explicit Validation for UX
                const title = document.querySelector('input[name="title_th"]').value.trim();
                const type = document.querySelector('select[name="research_type"]').value;
                
                if (!title) { alert('กรุณาระบุชื่อโครงการ (ภาษาไทย)'); return; }
                if (!type) { alert('กรุณาระบุประเภทโครงการวิจัย'); return; }

                // Change Button to Loading State
                const nextBtn = document.querySelector('button[onclick="nextStep(2)"]');
                const originalText = nextBtn.innerHTML;
                nextBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';
                nextBtn.disabled = true;

                // Save Draft before moving
                const saved = await saveDraft();
                
                // Revert Button
                nextBtn.innerHTML = originalText;
                nextBtn.disabled = false;

                if (!saved) return; 
            }

            // Step 2 Validation (Documents)
            if (currentStep === 2) {
                 const requiredDocs = [1, 2, 3];
                 let missing = false;
                 requiredDocs.forEach(id => {
                     const el = document.getElementById(`doc-status-${id}`);
                     if (el && el.innerText.trim() === 'ยังไม่เลือกไฟล์') missing = true;
                 });

                 if (missing) {
                     alert('กรุณาอัปโหลดเอกสารจำเป็นให้ครบถ้วน (รายการที่ 1, 2 และ 3)');
                     return;
                 }
            }

            // Step 3 Validation (Co-researchers)
            if (currentStep === 3) {
                const researcherRows = document.querySelectorAll('#co-researcher-list .team-dynamic-row');
                let hasPending = false;
                researcherRows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    if (status !== 'accepted') {
                        hasPending = true;
                    }
                });

                if (hasPending) {
                    alert('ไม่สามารถทำรายการต่อได้ เนื่องจากมีผู้ร่วมวิจัยที่ยังไม่ตอบรับคำเชิญ\nกรุณารอการตอบรับ หรือลบรายชื่อออกก่อน');
                    return;
                }
            }
            
            if (currentStep === 4) {
                const advisorRows = document.querySelectorAll('#advisor-list .team-dynamic-row');
                let hasPending = false;
                advisorRows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    if (status !== 'accepted') {
                        hasPending = true;
                    }
                });

                if (hasPending) {
                    alert('ไม่สามารถทำรายการต่อได้ เนื่องจากมีที่ปรึกษาที่ยังไม่ตอบรับคำเชิญ\nกรุณารอการตอบรับ หรือลบรายชื่อออกก่อน');
                    return;
                }
            }

            if (targetStep > totalSteps || targetStep < 1) return;
            
            // Animation/Transition Logic
            document.getElementById(`step-${currentStep}-content`).classList.add('hidden');
            document.getElementById(`step-${targetStep}-content`).classList.remove('hidden');
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            currentStep = targetStep;
            updateProgress(currentStep);
            
            // If Step 5 (Summary), prepare layout
            if (currentStep === 5) prepareSummary();
        }
        
        // ... (prevStep, updateProgress ...)
        function prevStep(targetStep) {
             document.getElementById(`step-${currentStep}-content`).classList.add('hidden');
             document.getElementById(`step-${targetStep}-content`).classList.remove('hidden');
             currentStep = targetStep;
             updateProgress(currentStep);
             window.scrollTo(0, 0);
        }

        function updateProgress(step) {
            // ... same as before
            const percentage = ((step - 1) / (totalSteps - 1)) * 100;
            // Assuming there's an element with id 'progress-bar'
            // document.getElementById('progress-bar').style.width = percentage + '%'; 
            for (let i = 1; i <= totalSteps; i++) {
                const circle = document.getElementById(`step-${i}-circle`);
                if (circle) { // Check if element exists
                    if (i < step) {
                        circle.className = 'step-circle completed';
                        circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                    } else if (i === step) {
                        circle.className = 'step-circle active';
                        circle.innerHTML = i;
                    } else {
                        circle.className = 'step-circle inactive';
                        circle.innerHTML = i;
                    }
                }
            }
        }

        // Custom Document Modal Logic
        function openAddDocModal() {
            document.getElementById('add-doc-modal').classList.remove('hidden');
        }

        function closeAddDocModal() {
            document.getElementById('add-doc-modal').classList.add('hidden');
            // Clear inputs if needed
            document.getElementById('custom-doc-name-th').value = '';
            document.getElementById('custom-doc-name-en').value = '';
            document.getElementById('custom-doc-ver').value = '';
            document.getElementById('custom-doc-date').value = '';
            document.getElementById('custom-doc-file').value = '';
        }

        function saveOtherDoc() {
            const nameTH = document.getElementById('custom-doc-name-th').value;
            const nameEN = document.getElementById('custom-doc-name-en').value;
            const ver = document.getElementById('custom-doc-ver').value || '1.0';
            const date = document.getElementById('custom-doc-date').value;
            const fileInput = document.getElementById('custom-doc-file');

            if (!nameTH || !fileInput.files[0]) {
                alert('กรุณาระบุชื่อเอกสารและเลือกไฟล์');
                return;
            }

            const fileName = fileInput.files[0].name;
            const tableBody = document.getElementById('other-docs-body'); // This ID is not in the provided HTML, assuming it exists elsewhere
            if (!tableBody) {
                console.error("Element with ID 'other-docs-body' not found. Cannot add custom document row.");
                alert("Error: Could not find table to add document.");
                return;
            }
            const rowCount = document.querySelectorAll('table tbody tr').length + 1; // Approx count

            const newRow = document.createElement('tr');
            newRow.className = "hover:bg-yellow-100 transition";
            newRow.innerHTML = `
                <td class="p-3 text-center"><i class="fa-solid fa-plus text-yellow-600"></i></td>
                <td class="p-3">
                    <div class="font-medium text-blue-900">${nameTH}</div>
                    <div class="text-xs text-gray-500">${nameEN}</div>
                </td>
                <td class="p-3 text-center text-xs">${ver}</td>
                <td class="p-3 text-center text-xs">${date || '-'}</td>
                <td class="p-3 text-center text-xs text-gray-400">เพิ่มเติม</td>
                <td class="p-3 text-left">
                     <span class="text-blue-600 font-medium truncate block max-w-[150px] text-xs" title="${fileName}"><i class="fa-solid fa-file-pdf mr-1"></i> ${fileName}</span>
                </td>
                <td class="p-3 text-center">
                    <button type="button" class="text-red-500 hover:text-red-700 text-xs" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i> ลบ</button>
                    <!-- Hidden input to store file data would be needed in real form submission, logic handled via FormData usually -->
                </td>
            `;
            tableBody.appendChild(newRow);
            closeAddDocModal();
        }

        // File Upload Logic
        function triggerUpload(id) {
            // Check if draft exists
            if (!currentProjectId) {
                alert('กรุณากรอกข้อมูล Step 1 และบันทึกแบบร่างก่อนอัปโหลดเอกสาร');
                return;
            }
            document.getElementById(`file-${id}`).click();
        }

        async function updateFileState(docTypeId, input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileName = file.name;

                // UI Loading State (Optional betterment)
                const cell = input.parentElement;
                const button = cell.querySelector('button');
                const originalBtnText = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
                button.disabled = true;

                // Perform Upload
                try {
                    const formData = new FormData();
                    formData.append('action', 'upload_file');
                    formData.append('project_id', currentProjectId);
                    formData.append('doc_type_id', docTypeId);
                    formData.append('file', file);

                    const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                    const result = await res.json();
                    
                    if (result.success) {
                        // Update UI Success
                        button.innerHTML = '<i class="fa-solid fa-pen"></i> แก้ไข';
                        button.classList.remove('bg-white', 'text-gray-700');
                        button.classList.add('bg-blue-50', 'text-blue-600', 'border-blue-200');
                        button.disabled = false;

                        const statusCell = cell.previousElementSibling;
                        statusCell.innerHTML = `<span class="text-blue-600 font-medium truncate block max-w-[150px]" title="${fileName}">${fileName}</span>`;
                        statusCell.classList.remove('text-gray-400');
                        
                        console.log('Uploaded:', result.file_name);
                    } else {
                        alert('Upload Failed: ' + result.message);
                        button.innerHTML = originalBtnText; // Revert
                        button.disabled = false;
                        input.value = ''; // Clear input
                    }
                } catch (e) {
                    console.error(e);
                    alert('Upload Error');
                    button.innerHTML = originalBtnText;
                    button.disabled = false;
                }
            }
        }
        
        function toggleFunderInput(select) {
            const funderGroup = document.getElementById('funder-name-group');
            if (select.value === 'personal') {
                funderGroup.classList.add('hidden');
            } else {
                funderGroup.classList.remove('hidden');
            }
        }

        
        // Modal & Team Logic
        let searchMode = ''; // 'co_researcher' or 'advisor'
        let searchTimeout = null;

        function openSearchModal(mode) {
            searchMode = mode;
            const title = mode === 'co_researcher' ? 'ค้นหาผู้ร่วมวิจัย' : 'ค้นหาอาจารย์ที่ปรึกษา';
            document.getElementById('modal-title').innerText = title;
            document.getElementById('search-modal').classList.remove('hidden');
            
            // Reset UI
            document.getElementById('search-input').value = '';
            document.getElementById('search-results').innerHTML = '<div class="text-center text-gray-400 p-4">พิมพ์ 2 ตัวอักษรขึ้นไปเพื่อค้นหา...</div>';
            setTimeout(() => document.getElementById('search-input').focus(), 100);
        }

        function closeSearchModal() {
            document.getElementById('search-modal').classList.add('hidden');
        }

        /* Replaced by new logic below */
        function selectUser_OLD(name, email) {
            const tableId = currentSearchType === 'co_researcher' ? 'co-researcher-list' : 'advisor-list';
            const tableBody = document.getElementById(tableId);
            const noDataMsg = document.getElementById(currentSearchType === 'co_researcher' ? 'no-co-researcher' : 'no-advisor');
            
            if (noDataMsg) noDataMsg.classList.add('hidden');

            const roleLabel = currentSearchType === 'co_researcher' ? 'ผู้ร่วมวิจัย' : 'ที่ปรึกษา';
            
            const newRow = document.createElement('tr');
            newRow.className = "hover:bg-gray-50 transition";
            newRow.innerHTML = `
                <td class="p-3">
                    <div class="font-bold text-gray-800">${name}</div>
                    <div class="text-xs text-gray-500">${email}</div>
                </td>
                <td class="p-3">${roleLabel}</td>
                <td class="p-3 text-center"><span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-xs">รอส่งคำเชิญ</span></td>
                <td class="p-3 text-center text-gray-400">-</td>
                <td class="p-3 text-center">
                    <button type="button" class="text-red-500 hover:text-red-700" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tableBody.appendChild(newRow);
            closeSearchModal();
        }

        function prepareSummary() {
            const form = document.getElementById('submissionForm');
            const formData = new FormData(form);
            const summaryDiv = document.getElementById('summary-content');

            // Helper to get selected text
            const getSelectedText = (name) => {
                const el = document.querySelector(`select[name="${name}"]`);
                return el && el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '-';
            };

            // Funding Logic
            const sourceFundVal = formData.get('source_funds');
            let sourceFundText = getSelectedText('source_funds');
            if (sourceFundVal === 'external' || sourceFundVal === 'personal') {
               const funderName = formData.get('funder_name');
               if (funderName) sourceFundText += ` (${funderName})`;
            }

            // Team Collection
            const getTeamNames = (listId) => {
                const rows = document.querySelectorAll(`#${listId} tr td:first-child div.font-bold`);
                if (rows.length === 0) return '-';
                return Array.from(rows).map(r => r.innerText).join(', ');
            };
            
            let summaryHTML = `
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-3"><i class="fa-solid fa-info-circle text-blue-600 mr-2"></i> ข้อมูลโครงการ</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><span class="text-gray-500 block">ประเภทโครงการ:</span> <span class="font-medium text-gray-900">${getSelectedText('research_type')}</span></div>
                            <div><span class="text-gray-500 block">แหล่งทุน:</span> <span class="font-medium text-gray-900">${sourceFundText}</span></div>
                            <div class="md:col-span-2"><span class="text-gray-500 block">ชื่อโครงการ (TH):</span> <span class="font-medium text-gray-900">${formData.get('title_th') || '-'}</span></div>
                            <div class="md:col-span-2"><span class="text-gray-500 block">ชื่อโครงการ (EN):</span> <span class="font-medium text-gray-900">${formData.get('title_en') || '-'}</span></div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-3"><i class="fa-solid fa-users text-blue-600 mr-2"></i> ทีมวิจัย</h3>
                        <div class="grid grid-cols-1 gap-4 text-sm">
                            <div><span class="text-gray-500 block">หัวหน้าโครงการ (ผู้ยื่นเสนอ):</span> <div class="font-medium text-emerald-700 mt-1 pl-4 border-l-2 border-emerald-500"><?php echo htmlspecialchars($userName); ?></div></div>
                            <div><span class="text-gray-500 block">ผู้ร่วมวิจัย:</span> <div class="font-medium text-gray-900 mt-1 pl-4 border-l-2 border-gray-100">${getTeamNames('co-researcher-list')}</div></div>
                            <div><span class="text-gray-500 block">ที่ปรึกษา:</span> <div class="font-medium text-gray-900 mt-1 pl-4 border-l-2 border-gray-100">${getTeamNames('advisor-list')}</div></div>
                        </div>
                    </div>
                </div>
            `;
            
            summaryDiv.innerHTML = summaryHTML;
            nextStep(5); // Ensure this function exists or just switch tab
        }

        async function submitProject() {
            if(!confirm('ยืนยันการส่งโครงการ? ข้อมูลจะถูกบันทึกและเข้าสู่กระบวนการพิจารณา')) return;
            
            const btn = document.querySelector('button[onclick="submitProject()"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> กำลังบันทึก...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'submit_project');
                formData.append('project_id', currentProjectId);

                const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                const result = await res.json();

                if (result.success) {
                   alert('บันทึกโครงการเรียบร้อยแล้ว!');
                   window.location.href = 'dashboard.php';
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> ยืนยันและส่งโครงการ';
                }
            } catch (e) {
                alert('Connection Error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> ยืนยันและส่งโครงการ';
            }
        }

        // ==========================================
        // NEW TEAM LOGIC (Search & Invite & Reload)
        // ==========================================

        // Search Input Listener
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const keyword = e.target.value;
            if (keyword.length < 2) return;

            searchTimeout = setTimeout(async () => {
                const formData = new FormData();
                formData.append('action', 'search_users');
                formData.append('keyword', keyword);
                
                try {
                    const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                    const result = await res.json();
                    
                    const resultsDiv = document.getElementById('search-results');
                    resultsDiv.innerHTML = '';
                    
                    if (result.users && result.users.length > 0) {
                        result.users.forEach(u => {
                            const initial = u.firstname_th.charAt(0);
                            resultsDiv.innerHTML += `
                                <div class="p-3 border rounded-lg hover:bg-blue-50 cursor-pointer flex justify-between items-center group transition" onclick="selectUser('${u.firstname_th} ${u.lastname_th}', '${u.email}')">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">${initial}</div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">${u.firstname_th} ${u.lastname_th}</p>
                                            <p class="text-xs text-gray-500">${u.email}</p>
                                        </div>
                                    </div>
                                    <button class="text-blue-600 opacity-0 group-hover:opacity-100 text-sm font-semibold transition">เลือก</button>
                                </div>
                            `;
                        });
                    } else {
                        resultsDiv.innerHTML = '<div class="text-center text-gray-400 p-4">ไม่พบข้อมูล</div>';
                    }
                } catch(e) { console.error(e); }
            }, 500);
        });

        async function selectUser(name, email) {
            if (!confirm(`ยืนยันการเพิ่ม "${name}" เข้าร่วมโครงการ?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'invite_team');
            formData.append('project_id', currentProjectId);
            formData.append('name', name);
            formData.append('email', email);
            formData.append('role', searchMode); 
            
            try {
                const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.success) {
                    alert('ส่งคำเชิญเรียบร้อยแล้ว!');
                    closeSearchModal();
                    loadTeamData();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch(e) { alert('Connection Error'); }
        }

        async function loadTeamData() {
            if (!currentProjectId) {
                console.log("No Project ID");
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'get_team_status');
            formData.append('project_id', currentProjectId);
            
            const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
            const result = await res.json();
            
            if (result.success) {
                const coList = document.getElementById('co-researcher-list');
                const adList = document.getElementById('advisor-list');
                
                // Clear Dynamic Rows
                document.querySelectorAll('.team-dynamic-row').forEach(e => e.remove());

                result.team.forEach(m => {
                    const targetList = m.role === 'co_researcher' ? coList : (m.role === 'advisor' ? adList : null);
                    if (!targetList) return;

                    // Skip if duplicate (Optional, but simple check is good)
                    
                    const statusBadge = m.response_status === 'accepted' 
                        ? '<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-bold">ตอบรับแล้ว</span>'
                        : (m.response_status === 'rejected' ? '<span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-bold">ปฏิเสธ</span>' 
                        : '<span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-xs">รอการตอบรับ</span>');

                    const newRow = document.createElement('tr');
                    newRow.className = "hover:bg-gray-50 transition team-dynamic-row";
                    newRow.setAttribute('data-status', m.response_status); // Add status for validation
                    newRow.innerHTML = `
                        <td class="p-3">
                            <div class="font-bold text-gray-800">${m.firstname}</div>
                            <!-- <div class="text-xs text-gray-500">${m.email || ''}</div> -->
                        </td>
                        <td class="p-3 text-gray-600 capitalize">${m.role}</td>
                        <td class="p-3 text-center"><span class="text-xs text-gray-400">ส่งแล้ว</span></td>
                        <td class="p-3 text-center">${statusBadge}</td>
                        <td class="p-3 text-center text-xs text-gray-500">${m.response_date ? new Date(m.response_date).toLocaleString('th-TH') : '-'}</td>
                        <td class="p-3 text-center">
                            <button type="button" class="text-red-500 hover:text-red-700" onclick="deleteTeamMember(${m.id})"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    `;
                    targetList.appendChild(newRow);
                });
                
                document.getElementById('no-co-researcher').classList.toggle('hidden', document.querySelectorAll('#co-researcher-list tr').length > 1);
                document.getElementById('no-advisor').classList.toggle('hidden', document.querySelectorAll('#advisor-list tr').length > 0);
            }
        }

        async function deleteTeamMember(id) {
            if(!confirm('ต้องการลบรายชื่อนี้หรือไม่?')) return;
            const formData = new FormData();
            formData.append('action', 'delete_team_member');
            formData.append('member_id', id);
            await fetch('api/submission_handler.php', { method: 'POST', body: formData });
            loadTeamData();
        }

        // ==========================================
        // INITIALIZATION (Load Draft)
        // ==========================================
        document.addEventListener('DOMContentLoaded', async () => {
             const urlParams = new URLSearchParams(window.location.search);
             const urlId = urlParams.get('id');
             
             if (urlId) {
                 console.log("Loading project:", urlId);
                 currentProjectId = urlId;
                 
                 // Fetch Data
                 const formData = new FormData();
                 formData.append('action', 'get_project');
                 formData.append('project_id', urlId);
                 
                 try {
                     const res = await fetch('api/submission_handler.php', { method: 'POST', body: formData });
                     const result = await res.json();
                     
                     if (result.success) {
                         const p = result.project;
                         
                         // 1. Populate Step 1 (General Info)
                         const form = document.getElementById('submissionForm');
                         if(p.research_type) form.research_type.value = p.research_type;
                         if(p.title_th) form.title_th.value = p.title_th;
                         if(p.title_en) form.title_en.value = p.title_en;
                         if(p.source_funds) {
                             form.source_funds.value = p.source_funds;
                             toggleFunderInput(form.source_funds);
                         }
                         if(p.funder_name) form.funder_name.value = p.funder_name;
                         
                         // Radio buttons
                         if(p.volunteers_under_18 == 1) document.querySelector('input[name="volunteers_under_18"][value="1"]').checked = true;
                         else document.querySelector('input[name="volunteers_under_18"][value="0"]').checked = true;

                         // 2. Populate Step 2 (Documents)
                         // We need to map doc_type (id) to file name
                         if (result.documents) {
                             result.documents.forEach(d => {
                                 // Look for the file input wrapper for this doc_type
                                 // Currently logic is based on `updateFileState` which takes (input, id)
                                 // We need to find the DOM element.
                                 // doc_type in DB is 1,2,3... matches ID in HTML?
                                 // My HTML IDs are like `doc-1`, `file-name-1`.
                                 
                                 const typeId = d.doc_type;
                                 const fileNameDisplay = document.getElementById(`file-name-${typeId}`);
                                 const btn = document.getElementById(`btn-upload-${typeId}`);
                                 const status = document.getElementById(`status-${typeId}`);
                                 
                                 if (fileNameDisplay && btn && status) {
                                     // Simulate upload completion UI
                                     fileNameDisplay.innerHTML = `<span class="text-blue-600 font-medium truncate block max-w-[150px]" title="${d.file_path}">${d.file_path}</span>`;
                                     fileNameDisplay.classList.remove('text-gray-400');
                                     
                                     btn.innerHTML = '<i class="fa-solid fa-pen"></i> แก้ไข';
                                     btn.className = "bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg text-xs transition font-semibold";
                                     
                                     status.innerHTML = '<i class="fa-solid fa-check-circle text-green-500 text-lg"></i>';
                                 }
                             });
                         }
                         
                         // 3. Populate Team (Step 3 & 4)
                         // Just call loadTeamData()!
                         loadTeamData();
                         
                     } else {
                         console.error("Failed to load project", result.message);
                     }
                 } catch(e) { console.error(e); }
             }
        });

    </script>
</body>
</html>
