<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userName = $_SESSION['fullname'] ?? 'User';

require_once 'api/db.php';
$userId = $_SESSION['user_id'];
$isOfficerOrAdmin = false;

// Check Role
try {
    $stmt = $conn->prepare("SELECT role_admin, role_staff, role_coordinator FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u) {
        if ($u['role_admin'] || $u['role_staff']) {
            $isOfficerOrAdmin = true;
        }
    }
} catch (Exception $e) {}

// For dev purposes, sometimes coordinator or secretary is the one setting it up
// We'll trust $isOfficerOrAdmin boolean in the view to show/hide Add button.
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปฏิทินการประชุมกรรมการจริยธรรม - Research Portal</title>
    <link rel="stylesheet" href="css/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar JS & CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #F3F4F6; }
        .fc-event { cursor: pointer; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-indigo-700 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()" class="text-indigo-200 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold">ปฏิทินประชุมกรรมการจริยธรรม</h1>
            </div>
            <div class="text-sm px-4 py-2 bg-indigo-800 rounded-lg">
                <i class="fa-solid fa-user text-indigo-300"></i> <?php echo htmlspecialchars($userName); ?>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-4 py-8 w-full">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">ตารางการประชุม</h2>
                    <p class="text-gray-500 text-sm">ดูตารางการประชุมและนัดหมายของคณะกรรมการจริยธรรมการวิจัย</p>
                </div>
                <?php if ($isOfficerOrAdmin): ?>
                <button onclick="document.getElementById('add-meeting-modal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    <i class="fa-solid fa-plus mr-2"></i> สร้างนัดหมาย
                </button>
                <?php endif; ?>
            </div>

            <div id="calendar" class="min-h-[600px]"></div>
        </div>
    </main>

    <!-- Modal for Adding Meeting -->
    <?php if ($isOfficerOrAdmin): ?>
    <div id="add-meeting-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-lg text-gray-800"><i class="fa-solid fa-calendar-plus text-indigo-600 mr-2"></i> สร้างนัดหมายประชุม</h3>
                <button onclick="document.getElementById('add-meeting-modal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="p-6">
                <form id="meetingForm" onsubmit="saveMeeting(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1">หัวข้อการประชุม *</label>
                            <input type="text" name="title" required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="เช่น การประชุมคณะกรรมการ ครั้งที่ 1/2569">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">วันที่ *</label>
                            <input type="date" name="date" required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1">เวลาเริ่ม *</label>
                                <input type="time" name="start_time" required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">เวลาสิ้นสุด *</label>
                                <input type="time" name="end_time" required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">สถานที่ / Link ประชุม ออนไลน์</label>
                            <input type="text" name="location" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="เช่น ห้องประชุม 1 หรือ URL Zoom">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">การประชุมครั้งที่</label>
                            <input type="number" name="round" value="1" min="1" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('add-meeting-modal').classList.add('hidden')" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">ยกเลิก</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow font-bold" id="submitBtn">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal for Viewing Meeting -->
    <div id="view-meeting-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-4 border-b bg-indigo-50 flex justify-between items-center">
                <h3 class="font-bold text-lg text-indigo-900"><i class="fa-solid fa-info-circle text-indigo-600 mr-2"></i> รายละเอียดการประชุม</h3>
                <button onclick="document.getElementById('view-meeting-modal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="p-6">
                <h4 id="view-title" class="text-xl font-bold text-gray-800 mb-4 border-b pb-2"></h4>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex gap-2"><i class="fa-solid fa-calendar w-5 text-gray-400 mt-0.5"></i> <div><span class="font-bold">วันที่:</span> <span id="view-date"></span></div></div>
                    <div class="flex gap-2"><i class="fa-solid fa-clock w-5 text-gray-400 mt-0.5"></i> <div><span class="font-bold">เวลา:</span> <span id="view-time"></span></div></div>
                    <div class="flex gap-2"><i class="fa-solid fa-map-marker-alt w-5 text-gray-400 mt-0.5"></i> <div><span class="font-bold">สถานที่:</span> <span id="view-location"></span></div></div>
                    <div class="flex gap-2"><i class="fa-solid fa-hashtag w-5 text-gray-400 mt-0.5"></i> <div><span class="font-bold">ครั้งที่:</span> <span id="view-round"></span></div></div>
                </div>
                <?php if ($isOfficerOrAdmin): ?>
                <div class="mt-6 pt-4 border-t flex justify-end">
                    <button type="button" id="delete-meeting-btn" class="px-4 py-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg text-sm font-bold transition">
                        <i class="fa-solid fa-trash mr-1"></i> ลบการประชุมนี้
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let calendar;
        
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'th',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    list: 'รายการ'
                },
                themeSystem: 'standard',
                events: 'api/meeting_handler.php?action=get_meetings',
                eventClick: function(info) {
                    showMeetingDetails(info.event);
                },
                eventContent: function(arg) {
                    let italicEl = document.createElement('div');
                    italicEl.innerHTML = `<div class="p-1 text-xs shrink font-semibold bg-indigo-100 text-indigo-700 rounded border border-indigo-200 truncate" title="${arg.event.title}">
                        ${arg.timeText} ${arg.event.title}
                    </div>`;
                    let arrayOfDomNodes = [ italicEl ]
                    return { domNodes: arrayOfDomNodes }
                }
            });
            calendar.render();
        });

        // ------------------ Actions ------------------
        
        async function saveMeeting(e) {
            e.preventDefault();
            const form = document.getElementById('meetingForm');
            const formData = new FormData(form);
            formData.append('action', 'create_meeting');

            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> บันทึก...';
            btn.disabled = true;

            try {
                const res = await fetch('api/meeting_handler.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    alert('สร้างนัดหมายสำเร็จ');
                    document.getElementById('add-meeting-modal').classList.add('hidden');
                    form.reset();
                    calendar.refetchEvents();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Connection error');
            }
            btn.innerHTML = 'บันทึก';
            btn.disabled = false;
        }

        function showMeetingDetails(event) {
            document.getElementById('view-title').innerText = event.title;
            
            const start = event.start;
            const end = event.end;
            
            const dateStr = start.toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });
            const timeStr = start.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' - ' + 
                            (end ? end.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) : '');

            document.getElementById('view-date').innerText = dateStr;
            document.getElementById('view-time').innerText = timeStr;
            document.getElementById('view-location').innerText = event.extendedProps.location || '-';
            document.getElementById('view-round').innerText = event.extendedProps.round || '1';

            <?php if ($isOfficerOrAdmin): ?>
            document.getElementById('delete-meeting-btn').onclick = function() {
                if (confirm('คุณต้องการลบการประชุมนี้ใช่หรือไม่?')) {
                    deleteMeeting(event.id);
                }
            };
            <?php endif; ?>

            document.getElementById('view-meeting-modal').classList.remove('hidden');
        }

        async function deleteMeeting(id) {
            const formData = new FormData();
            formData.append('action', 'delete_meeting');
            formData.append('id', id);

            try {
                const res = await fetch('api/meeting_handler.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    alert('ลบการประชุมสำเร็จ');
                    document.getElementById('view-meeting-modal').classList.add('hidden');
                    calendar.refetchEvents();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Connection error');
            }
        }
    </script>
</body>
</html>
