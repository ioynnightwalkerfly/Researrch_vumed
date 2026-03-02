<?php
// reset_password.php - Password reset form
require_once 'api/db.php';

$token = $_GET['token'] ?? '';
$validToken = false;
$errorMsg = '';
$userName = '';

if (empty($token)) {
    $errorMsg = 'ลิงก์ไม่ถูกต้อง';
} else {
    $stmt = $conn->prepare("SELECT id, firstname_th FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $validToken = true;
        $userName = $user['firstname_th'];
    } else {
        $errorMsg = 'ลิงก์รีเซ็ตหมดอายุหรือไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ - Research Portal</title>
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">

        <?php if (!$validToken): ?>
        <!-- Error State -->
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">ลิงก์ไม่ถูกต้อง</h2>
            <p class="text-gray-500 text-sm mb-6"><?php echo htmlspecialchars($errorMsg); ?></p>
            <div class="space-y-3">
                <a href="forgot_password.html" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition text-center">
                    <i class="fa-solid fa-key mr-1"></i>ขอลิงก์รีเซ็ตใหม่
                </a>
                <a href="login.html" class="block text-sm text-gray-500 hover:text-gray-800 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i>กลับหน้าเข้าสู่ระบบ
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- Reset Form -->
        <div class="bg-blue-600 px-8 py-5 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-full mx-auto flex items-center justify-center text-2xl mb-3 text-white">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <h2 class="text-xl font-bold text-white">ตั้งรหัสผ่านใหม่</h2>
            <p class="text-blue-200 text-sm mt-1">สวัสดีคุณ <?php echo htmlspecialchars($userName); ?></p>
        </div>

        <form id="resetForm" class="p-8 space-y-4">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">รหัสผ่านใหม่</label>
                <div class="relative">
                    <input type="password" name="new_password" id="newPassword" minlength="6" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 pr-10"
                        placeholder="อย่างน้อย 6 ตัวอักษร">
                    <button type="button" onclick="togglePassword('newPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div id="strengthBar" class="mt-2 h-1.5 rounded-full bg-gray-200 overflow-hidden">
                    <div id="strengthFill" class="h-full rounded-full transition-all duration-300" style="width:0%"></div>
                </div>
                <p id="strengthText" class="text-xs mt-1 text-gray-400"></p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirmPassword" minlength="6" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 pr-10"
                        placeholder="กรอกรหัสผ่านอีกครั้ง">
                    <button type="button" onclick="togglePassword('confirmPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p id="matchMsg" class="text-xs mt-1 hidden"></p>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow transition disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-check mr-1"></i>ตั้งรหัสผ่านใหม่
            </button>
        </form>
        <?php endif; ?>

    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">เปลี่ยนรหัสผ่านสำเร็จ!</h3>
            <p class="text-gray-500 text-sm mb-6">คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว</p>
            <a href="login.html" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">
                <i class="fa-solid fa-sign-in-alt mr-1"></i>เข้าสู่ระบบ
            </a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('newPassword')?.addEventListener('input', function() {
            const val = this.value;
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { w: '0%', c: 'bg-gray-300', t: '' },
                { w: '20%', c: 'bg-red-500', t: 'อ่อนมาก' },
                { w: '40%', c: 'bg-orange-500', t: 'อ่อน' },
                { w: '60%', c: 'bg-yellow-500', t: 'ปานกลาง' },
                { w: '80%', c: 'bg-blue-500', t: 'แข็งแรง' },
                { w: '100%', c: 'bg-green-500', t: 'แข็งแรงมาก' }
            ];
            const l = levels[score];
            fill.style.width = l.w;
            fill.className = `h-full rounded-full transition-all duration-300 ${l.c}`;
            text.textContent = l.t;
            checkMatch();
        });

        // Password match check
        document.getElementById('confirmPassword')?.addEventListener('input', checkMatch);

        function checkMatch() {
            const p1 = document.getElementById('newPassword')?.value || '';
            const p2 = document.getElementById('confirmPassword')?.value || '';
            const msg = document.getElementById('matchMsg');
            if (!msg) return;
            if (p2.length === 0) { msg.classList.add('hidden'); return; }
            msg.classList.remove('hidden');
            if (p1 === p2) {
                msg.textContent = '✓ รหัสผ่านตรงกัน';
                msg.className = 'text-xs mt-1 text-green-600';
            } else {
                msg.textContent = '✗ รหัสผ่านไม่ตรงกัน';
                msg.className = 'text-xs mt-1 text-red-500';
            }
        }

        // Form submit
        document.getElementById('resetForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const p1 = document.getElementById('newPassword').value;
            const p2 = document.getElementById('confirmPassword').value;

            if (p1.length < 6) { alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'); return; }
            if (p1 !== p2) { alert('รหัสผ่านไม่ตรงกัน'); return; }

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';
            btn.disabled = true;

            const formData = new FormData(this);

            try {
                const res = await fetch('api/reset_password_action.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.status === 'success') {
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.message);
                    btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>ตั้งรหัสผ่านใหม่';
                    btn.disabled = false;
                }
            } catch(err) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>ตั้งรหัสผ่านใหม่';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
