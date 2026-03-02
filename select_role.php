<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$username = $_SESSION['fullname'];
$roles = $_SESSION['user_roles'] ?? ['researcher' => true, 'coordinator'=>false, 'admin'=>false, 'officer'=>false, 'secretary'=>false];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกบทบาท - Research Portal</title>
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; background: #DAE4F5; }</style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl p-10 mx-4">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ยินดีต้อนรับ, <?php echo htmlspecialchars($username); ?></h1>
            <p class="text-gray-500">กรุณาเลือกบทบาทที่ต้องการเข้าใช้งาน</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- 1. Researcher (ทุกคนมี) -->
            <button onclick="selectRole('researcher')" class="group relative bg-white border-2 border-gray-100 hover:border-blue-500 rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-600 transition">
                    <i class="fa-solid fa-flask text-2xl text-blue-600 group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">นักวิจัย</h3>
                <p class="text-xs text-gray-500 mt-2">ยื่นข้อเสนอโครงการ และติดตามสถานะ</p>
            </button>

            <!-- 2. Coordinator -->
            <?php if ($roles['coordinator']): ?>
            <button onclick="selectRole('coordinator')" class="group relative bg-white border-2 border-gray-100 hover:border-purple-500 rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-600 transition">
                    <i class="fa-solid fa-user-tie text-2xl text-purple-600 group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">ผู้ประสานงาน</h3>
                <p class="text-xs text-gray-500 mt-2">ตรวจสอบและจัดการโครงการวิจัย</p>
            </button>
            <?php endif; ?>

            <!-- 3. Officer -->
            <?php if ($roles['officer']): ?>
            <button onclick="selectRole('officer')" class="group relative bg-white border-2 border-gray-100 hover:border-green-500 rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-600 transition">
                    <i class="fa-solid fa-user-shield text-2xl text-green-600 group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">เจ้าหน้าที่</h3>
                <p class="text-xs text-gray-500 mt-2">ตรวจสอบเอกสารเบื้องต้น</p>
            </button>
            <?php endif; ?>

            <!-- 4. Secretary -->
            <?php if ($roles['secretary']): ?>
            <button onclick="selectRole('secretary')" class="group relative bg-white border-2 border-gray-100 hover:border-indigo-500 rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-600 transition">
                    <i class="fa-solid fa-file-contract text-2xl text-indigo-600 group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">เลขานุการ</h3>
                <p class="text-xs text-gray-500 mt-2">ตรวจสอบความถูกต้อง & อนุมัติแทนประธาน</p>
            </button>
            <?php endif; ?>

            <!-- 5. Admin -->
            <?php if ($roles['admin']): ?>
            <button onclick="selectRole('admin')" class="group relative bg-white border-2 border-gray-100 hover:border-red-500 rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-600 transition">
                    <i class="fa-solid fa-shield-halved text-2xl text-red-600 group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">ผู้ดูแลระบบ</h3>
                <p class="text-xs text-gray-500 mt-2">จัดการผู้ใช้งานและการตั้งค่าระบบ</p>
            </button>
            <?php endif; ?>

        </div>

        <div class="mt-10 text-center">
             <a href="login.html" class="text-gray-400 text-sm hover:text-gray-600">กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>

    <script>
        async function selectRole(role) {
            try {
                const formData = new FormData();
                formData.append('role', role);

                const res = await fetch('api/set_role.php', { method: 'POST', body: formData });
                const result = await res.json();

                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                console.error(e);
                alert('Connection Error');
            }
        }
    </script>
</body>
</html>
