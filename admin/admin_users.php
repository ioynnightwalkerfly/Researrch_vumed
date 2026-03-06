<?php
// admin/admin_users.php
session_start();
require_once '../api/db.php';

// Check Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}
$userName = $_SESSION['fullname'];

// Fetch All Users
$sql = "SELECT * FROM users ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <?php include 'includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center z-10">
            <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-users-gear mr-2 text-blue-600"></i>จัดการผู้ใช้งาน</h2>
            <div class="flex gap-2">
                <a href="admin_dashboard.php" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 transition">
                    <i class="fa-solid fa-home"></i> แดชบอร์ด
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-400 text-sm">ผู้ใช้งาน</span>
            </div>
        </header>

        <!-- Content Srcollable -->
        <div class="flex-grow overflow-auto p-6 scrollbar-hide">

            <!-- Search + Add -->
            <div class="flex justify-between items-center mb-6">
                <form class="flex gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="q" id="searchInput" placeholder="ค้นหาชื่อ / username / อีเมล..." value="" class="border rounded-lg pl-9 pr-3 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-300" oninput="filterTable(this.value)">
                    </div>
                </form>
                <div class="flex gap-2">
                    <span class="text-sm text-gray-500 flex items-center gap-1"><i class="fa-solid fa-users"></i> ทั้งหมด <b class="text-gray-800"><?php echo count($users); ?></b> คน</span>
                    <button onclick="openAddUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition flex items-center gap-2 text-sm font-bold">
                        <i class="fa-solid fa-user-plus"></i> เพิ่มผู้ใช้งาน
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse" id="usersTable">
                    <thead class="bg-gray-50 border-b">
                         <tr>
                            <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-16">ID</th>
                            <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">ผู้ใช้งาน</th>
                            <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">อีเมล</th>
                            <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">ยืนยัน</th>
                            <th class="p-4 font-semibold text-center text-gray-500 text-xs uppercase tracking-wider">สิทธิ์</th>
                            <th class="p-4 font-semibold text-center text-gray-500 text-xs uppercase tracking-wider w-40">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-blue-50/50 transition duration-150 user-row">
                            <td class="p-4 text-gray-400 text-xs font-mono">#<?php echo $user['id']; ?></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                        <?php echo (function_exists('mb_substr')) ? mb_substr($user['firstname_th'], 0, 1, 'UTF-8') : substr($user['firstname_th'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($user['firstname_th'] . ' ' . $user['lastname_th']); ?></div>
                                        <div class="text-xs text-gray-400">@<?php echo htmlspecialchars($user['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                             <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-4 text-center">
                                <?php if (($user['is_verified'] ?? 0) == 1): ?>
                                <span class="text-green-500" title="ยืนยันแล้ว"><i class="fa-solid fa-circle-check"></i></span>
                                <?php else: ?>
                                <span class="text-gray-300" title="ยังไม่ยืนยัน"><i class="fa-solid fa-circle-xmark"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1.5">
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" class="peer sr-only" 
                                            onchange="updateRole(<?php echo $user['id']; ?>, 'researcher', this.checked)"
                                            <?php echo ($user['role_researcher'] == 1) ? 'checked' : ''; ?>>
                                        <div class="w-7 h-7 rounded flex items-center justify-center border border-gray-200 text-gray-300 peer-checked:bg-blue-100 peer-checked:text-blue-600 peer-checked:border-blue-200 transition text-xs">
                                            <i class="fa-solid fa-flask"></i>
                                        </div>
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">นักวิจัย</span>
                                    </label>
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" class="peer sr-only" 
                                            onchange="updateRole(<?php echo $user['id']; ?>, 'coordinator', this.checked)"
                                            <?php echo ($user['role_coordinator'] == 1) ? 'checked' : ''; ?>>
                                        <div class="w-7 h-7 rounded flex items-center justify-center border border-gray-200 text-gray-300 peer-checked:bg-purple-100 peer-checked:text-purple-600 peer-checked:border-purple-200 transition text-xs">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">ผู้ประสาน</span>
                                    </label>
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" class="peer sr-only" 
                                            onchange="updateRole(<?php echo $user['id']; ?>, 'officer', this.checked)"
                                            <?php echo ($user['role_officer'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                        <div class="w-7 h-7 rounded flex items-center justify-center border border-gray-200 text-gray-300 peer-checked:bg-green-100 peer-checked:text-green-600 peer-checked:border-green-200 transition text-xs">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </div>
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">เจ้าหน้าที่</span>
                                    </label>
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" class="peer sr-only" 
                                            onchange="updateRole(<?php echo $user['id']; ?>, 'secretary', this.checked)"
                                            <?php echo ($user['role_secretary'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                        <div class="w-7 h-7 rounded flex items-center justify-center border border-gray-200 text-gray-300 peer-checked:bg-indigo-100 peer-checked:text-indigo-600 peer-checked:border-indigo-200 transition text-xs">
                                            <i class="fa-solid fa-file-contract"></i>
                                        </div>
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">เลขา</span>
                                    </label>
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" class="peer sr-only" 
                                            onchange="updateRole(<?php echo $user['id']; ?>, 'admin', this.checked)"
                                            <?php echo ($user['role_admin'] == 1) ? 'checked' : ''; ?>>
                                        <div class="w-7 h-7 rounded flex items-center justify-center border border-gray-200 text-gray-300 peer-checked:bg-red-100 peer-checked:text-red-600 peer-checked:border-red-200 transition text-xs">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">Admin</span>
                                    </label>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1">
                                     <button onclick='openDetailModal(<?php echo json_encode($user); ?>)' class="w-7 h-7 rounded bg-blue-50 hover:bg-blue-100 text-blue-500 transition text-xs" title="ดูรายละเอียด"><i class="fa-solid fa-eye"></i></button>
                                     <button onclick='openEditUserModal(<?php echo json_encode($user); ?>)' class="w-7 h-7 rounded bg-yellow-50 hover:bg-yellow-100 text-yellow-500 transition text-xs" title="แก้ไข"><i class="fa-solid fa-pen"></i></button>
                                     <button onclick='openResetPwModal(<?php echo $user["id"]; ?>, "<?php echo addslashes(htmlspecialchars($user["firstname_th"] . " " . $user["lastname_th"])); ?>")' class="w-7 h-7 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-500 transition text-xs" title="รีเซ็ตรหัสผ่าน"><i class="fa-solid fa-key"></i></button>
                                     <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="w-7 h-7 rounded bg-red-50 hover:bg-red-100 text-red-400 transition text-xs" title="ลบ"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="h-10"></div>
        </div>
    </main>

    <!-- Add User Modal -->
    <div id="add-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all">
             <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-user-plus mr-2 text-blue-600"></i>เพิ่มผู้ใช้งานใหม่</h3>
                <button onclick="closeModal('add-user-modal')" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                 <input type="text" id="new-username" placeholder="Username (สำหรับ Login)" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                 <input type="password" id="new-password" placeholder="Password" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                 <div class="grid grid-cols-2 gap-4">
                     <input type="text" id="new-fname" placeholder="ชื่อ" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                     <input type="text" id="new-lname" placeholder="นามสกุล" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                 </div>
                 <input type="email" id="new-email" placeholder="Email (Example@mail.com)" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                 
                 <button onclick="submitNewUser()" class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-bold shadow-lg shadow-blue-200 transition transform active:scale-95">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all">
             <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-user-pen mr-2 text-yellow-500"></i>แก้ไขข้อมูลผู้ใช้งาน</h3>
                <button onclick="closeModal('edit-user-modal')" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                 <input type="hidden" id="edit-userid">
                 <label class="block text-xs text-gray-400 uppercase font-bold">ข้อมูลเข้าระบบ</label>
                 <input type="text" id="edit-username" placeholder="Username" class="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-100 cursor-not-allowed">
                 <input type="password" id="edit-password" placeholder="เปลี่ยนรหัสผ่าน (เว้นว่างถ้ารหัสเดิม)" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-gray-50">
                 
                 <label class="block text-xs text-gray-400 uppercase font-bold mt-2">ข้อมูลส่วนตัว</label>
                 <div class="grid grid-cols-2 gap-4">
                     <input type="text" id="edit-fname" placeholder="ชื่อ" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-gray-50">
                     <input type="text" id="edit-lname" placeholder="นามสกุล" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-gray-50">
                 </div>
                 <input type="email" id="edit-email" placeholder="Email" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-gray-50">
                 
                 <button onclick="submitEditUser()" class="w-full bg-yellow-500 text-white py-2.5 rounded-lg hover:bg-yellow-600 font-bold shadow-lg shadow-yellow-200 transition transform active:scale-95">บันทึกการแก้ไข</button>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all max-h-[90vh] overflow-y-auto">
            <div class="p-5 border-b bg-blue-600 text-white flex justify-between items-center">
                <h3 class="font-bold"><i class="fa-solid fa-user mr-2"></i>รายละเอียดผู้ใช้งาน</h3>
                <button onclick="closeModal('detail-modal')" class="text-white/70 hover:text-white transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6" id="detailContent"></div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetpw-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-all">
            <div class="p-5 border-b bg-indigo-600 text-white flex justify-between items-center">
                <h3 class="font-bold"><i class="fa-solid fa-key mr-2"></i>รีเซ็ตรหัสผ่าน</h3>
                <button onclick="closeModal('resetpw-modal')" class="text-white/70 hover:text-white transition"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="reset-userid">
                <p class="text-sm text-gray-600">ตั้งรหัสผ่านใหม่ให้: <b id="reset-username" class="text-gray-800"></b></p>
                <div class="relative">
                    <input type="text" id="reset-newpw" placeholder="รหัสผ่านใหม่" class="w-full border border-gray-200 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 pr-20">
                    <button onclick="generatePw()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs px-2 py-1 rounded transition" title="สุ่มรหัสผ่าน"><i class="fa-solid fa-dice mr-1"></i>สุ่ม</button>
                </div>
                <button onclick="submitResetPw()" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg hover:bg-indigo-700 font-bold transition">บันทึกรหัสผ่านใหม่</button>
            </div>
        </div>
    </div>

    <script>
        // Modal Logic
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden'); 
            setTimeout(() => modal.children[0].classList.replace('scale-95', 'scale-100'), 10);
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.children[0].classList.replace('scale-100', 'scale-95');
            setTimeout(() => modal.classList.add('hidden'), 200); 
        }

        function openAddUserModal() { openModal('add-user-modal'); }

        function openEditUserModal(user) {
            document.getElementById('edit-userid').value = user.id;
            document.getElementById('edit-username').value = user.username;
            document.getElementById('edit-fname').value = user.firstname_th;
            document.getElementById('edit-lname').value = user.lastname_th;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-password').value = '';
            openModal('edit-user-modal');
        }

        // Detail Modal
        function openDetailModal(user) {
            const verified = user.is_verified == 1;
            const hashShort = user.password_hash ? user.password_hash.substring(0, 20) + '...' : '-';
            const hashFull = user.password_hash || '-';
            const roles = [];
            if (user.role_researcher == 1) roles.push('<span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-bold">นักวิจัย</span>');
            if (user.role_coordinator == 1) roles.push('<span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs font-bold">ผู้ประสาน</span>');
            if ((user.role_officer ?? 0) == 1) roles.push('<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-bold">เจ้าหน้าที่</span>');
            if ((user.role_secretary ?? 0) == 1) roles.push('<span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-bold">เลขา</span>');
            if (user.role_admin == 1) roles.push('<span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-bold">Admin</span>');

            const row = (label, value, extra = '') => `<div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500 text-sm">${label}</span><span class="text-gray-800 text-sm font-medium text-right">${value}${extra}</span></div>`;

            let html = `
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold">${(user.firstname_th || '?')[0]}</div>
                    <div>
                        <div class="text-lg font-bold text-gray-800">${user.firstname_th} ${user.lastname_th}</div>
                        <div class="text-sm text-gray-400">@${user.username} · ID #${user.id}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 space-y-0">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">ข้อมูลบัญชี</div>
                    ${row('Username', user.username)}
                    ${row('Email', user.email)}
                    ${row('ยืนยันอีเมล', verified ? '<span class="text-green-600">✓ ยืนยันแล้ว</span>' : '<span class="text-red-500">✗ ยังไม่ยืนยัน</span>')}
                    ${row('Password Hash', '<code class="text-xs bg-gray-200 px-1 rounded select-all cursor-pointer" title="คลิกเพื่อคัดลอก" onclick="navigator.clipboard.writeText(\'' + hashFull.replace(/'/g, "\\'") + '\');this.style.background=\'#bbf7d0\';setTimeout(()=>this.style.background=\'#e5e7eb\',1000)">' + hashShort + ' 📋</code>')}
                </div>
                <div class="bg-gray-50 rounded-lg p-4 space-y-0 mt-3">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">ข้อมูลส่วนตัว</div>
                    ${row('เลขบัตรประชาชน', user.id_card_number || '-')}
                    ${row('คำนำหน้า (TH)', user.prefix_th || '-')}
                    ${row('ชื่อ-สกุล (ไทย)', user.firstname_th + ' ' + user.lastname_th)}
                    ${row('คำนำหน้า (EN)', user.prefix_eng || '-')}
                    ${row('ชื่อ-สกุล (EN)', (user.firstname_eng || '-') + ' ' + (user.lastname_eng || ''))}
                </div>
                <div class="bg-gray-50 rounded-lg p-4 space-y-0 mt-3">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">การติดต่อ</div>
                    ${row('เบอร์มือถือ', user.mobile_phone || '-')}
                    ${row('เบอร์ที่ทำงาน', user.phone_office || '-')}
                    ${row('คณะ/หน่วยงาน', user.faculty || '-')}
                    ${row('บุคลากรภายนอก', user.is_external == 1 ? '<span class="text-orange-600">ใช่</span>' : 'ไม่ใช่')}
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">คุณสมบัติ</div>
                    <div class="flex flex-wrap gap-1.5">
                        ${user.qual_health_personnel == 1 ? '<span class="bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full text-xs">บุคลากรด้านสุขภาพ</span>' : ''}
                        ${user.qual_social_scientist == 1 ? '<span class="bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full text-xs">นักสังคมศาสตร์</span>' : ''}
                        ${user.qual_non_medical == 1 ? '<span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">ไม่ได้ประกอบวิชาชีพแพทย์</span>' : ''}
                        ${user.qual_community_rep == 1 ? '<span class="bg-lime-100 text-lime-700 px-2 py-0.5 rounded-full text-xs">ตัวแทนชุมชน</span>' : ''}
                        ${user.qual_lawyer == 1 ? '<span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-xs">นักกฎหมาย</span>' : ''}
                        ${!user.qual_health_personnel && !user.qual_social_scientist && !user.qual_non_medical && !user.qual_community_rep && !user.qual_lawyer ? '<span class="text-gray-400 text-sm">ไม่มีข้อมูล</span>' : ''}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">สิทธิ์การใช้งาน</div>
                    <div class="flex flex-wrap gap-2">${roles.length ? roles.join('') : '<span class="text-gray-400 text-sm">ไม่มีสิทธิ์</span>'}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">ไฟล์ & เวลา</div>
                    ${row('ไฟล์ CV', user.cv_file_path ? '<a href="../' + user.cv_file_path + '" target="_blank" class="text-blue-600 hover:underline text-xs"><i class="fa-solid fa-file-pdf mr-1"></i>ดูไฟล์</a>' : '-')}
                    ${row('สร้างเมื่อ', user.created_at || '-')}
                    ${row('อัพเดตล่าสุด', user.updated_at || '-')}
                </div>
                <div class="flex gap-2 mt-4">
                    <button onclick="closeModal('detail-modal');openEditUserModal(${JSON.stringify(user).replace(/"/g,'&quot;')})" class="flex-1 bg-yellow-500 text-white py-2 rounded-lg font-bold text-sm hover:bg-yellow-600 transition"><i class="fa-solid fa-pen mr-1"></i>แก้ไข</button>
                    <button onclick="closeModal('detail-modal');openResetPwModal(${user.id},'${(user.firstname_th + ' ' + user.lastname_th).replace(/'/g, "\\'")  }')" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 transition"><i class="fa-solid fa-key mr-1"></i>รีเซ็ตรหัสผ่าน</button>
                </div>
            `;
            document.getElementById('detailContent').innerHTML = html;
            openModal('detail-modal');
        }

        // Reset Password Modal
        function openResetPwModal(userId, name) {
            document.getElementById('reset-userid').value = userId;
            document.getElementById('reset-username').textContent = name;
            document.getElementById('reset-newpw').value = '';
            openModal('resetpw-modal');
        }
        function generatePw() {
            const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$';
            let pw = '';
            for (let i = 0; i < 10; i++) pw += chars[Math.floor(Math.random() * chars.length)];
            document.getElementById('reset-newpw').value = pw;
        }
        async function submitResetPw() {
            const userId = document.getElementById('reset-userid').value;
            const newPw = document.getElementById('reset-newpw').value.trim();
            if (!newPw || newPw.length < 4) { alert('กรุณากรอกรหัสผ่านอย่างน้อย 4 ตัว'); return; }
            await callApi({ action: 'update_user_details', user_id: userId, password: newPw, username: '', firstname: '', lastname: '', email: '' });
        }

        // Table Filter
        function filterTable(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        // Action Logic
        async function updateRole(userId, roleType, isChecked) {
            try {
                const formData = new FormData();
                formData.append('action', 'update_role');
                formData.append('user_id', userId);
                formData.append('role_type', roleType);
                formData.append('value', isChecked ? 1 : 0);
                const res = await fetch('../api/admin_user_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(!data.success) { alert('Error: ' + data.message); location.reload(); }
            } catch(e) { alert('Connection Error'); }
        }

        async function deleteUser(userId) {
            if(!confirm('ยืนยันลบผู้ใช้งานนี้?')) return;
            try {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userId);
                const res = await fetch('../api/admin_user_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) location.reload(); else alert(data.message);
            } catch(e) { alert('Connection Error'); }
        }

        async function submitNewUser() {
            const username = document.getElementById('new-username').value;
            const password = document.getElementById('new-password').value;
            const fname = document.getElementById('new-fname').value;
            const lname = document.getElementById('new-lname').value;
            const email = document.getElementById('new-email').value;
            if(!username || !password) return;
            callApi({ action: 'create_user', username, password, firstname: fname, lastname: lname, email, role_researcher: 1, role_coordinator: 0, role_admin: 0 });
        }

        async function submitEditUser() {
            const userId = document.getElementById('edit-userid').value;
            const username = document.getElementById('edit-username').value;
            const password = document.getElementById('edit-password').value;
            const fname = document.getElementById('edit-fname').value;
            const lname = document.getElementById('edit-lname').value;
            const email = document.getElementById('edit-email').value;
            callApi({ action: 'update_user_details', user_id: userId, username, password, firstname: fname, lastname: lname, email });
        }

        async function callApi(dataObj) {
            try {
                const formData = new FormData();
                for (const key in dataObj) formData.append(key, dataObj[key]);
                const res = await fetch('../api/admin_user_action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) { alert('บันทึกสำเร็จ'); location.reload(); }
                else alert('Error: ' + data.message);
            } catch(e) { alert('Connection Error: ' + e); }
        }
    </script>
</body>
</html>
