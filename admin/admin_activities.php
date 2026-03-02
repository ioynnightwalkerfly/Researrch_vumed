<?php
session_start();
require_once '../api/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];

// Auto-create table
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS na_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_path VARCHAR(255),
            activity_date DATE NOT NULL,
            link_url TEXT,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) { }

$message = '';
$messageType = '';
$editData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $imagePath = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/activities/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'act_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                if ($imagePath && file_exists('../' . $imagePath)) {
                    unlink('../' . $imagePath);
                }
                $imagePath = 'uploads/activities/' . $filename;
            }
        } else {
            $message = 'ไฟล์ภาพไม่ถูกต้อง (รองรับ jpg, png, gif, webp)';
            $messageType = 'error';
        }
    }

    if ($messageType !== 'error') {
        try {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO na_activities (title, description, image_path, activity_date, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['title'], $_POST['description'], $imagePath, $_POST['activity_date'], $_POST['link_url'] ?? '', $_POST['sort_order'] ?? 0]);
                $message = 'เพิ่มกิจกรรมสำเร็จ!';
                $messageType = 'success';
            } elseif ($action === 'edit') {
                $stmt = $conn->prepare("UPDATE na_activities SET title=?, description=?, image_path=?, activity_date=?, link_url=?, sort_order=? WHERE id=?");
                $stmt->execute([$_POST['title'], $_POST['description'], $imagePath, $_POST['activity_date'], $_POST['link_url'] ?? '', $_POST['sort_order'] ?? 0, $_POST['id']]);
                $message = 'แก้ไขกิจกรรมสำเร็จ!';
                $messageType = 'success';
            } elseif ($action === 'delete') {
                $stmt = $conn->prepare("SELECT image_path FROM na_activities WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $row = $stmt->fetch();
                if ($row && $row['image_path'] && file_exists('../' . $row['image_path'])) {
                    unlink('../' . $row['image_path']);
                }
                $stmt = $conn->prepare("DELETE FROM na_activities WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'ลบกิจกรรมสำเร็จ!';
                $messageType = 'success';
            } elseif ($action === 'toggle') {
                $stmt = $conn->prepare("UPDATE na_activities SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'เปลี่ยนสถานะสำเร็จ!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM na_activities WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

$items = [];
try {
    $stmt = $conn->query("SELECT * FROM na_activities ORDER BY sort_order ASC, activity_date DESC");
    $items = $stmt->fetchAll();
} catch (Exception $e) { }

$thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

function formatThaiDateAct($date, $months) {
    $d = new DateTime($date);
    return $d->format('j') . ' ' . $months[(int)$d->format('n')] . ' ' . ((int)$d->format('Y') + 543);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการกิจกรรมฝ่ายฯ - Admin Panel</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Noto Sans Thai', sans-serif; }
        .img-preview { max-height: 200px; object-fit: cover; border-radius: 8px; }
    </style>
</head>

<body class="bg-gray-50 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 text-center border-b border-gray-800">
            <h1 class="text-xl font-bold tracking-wider text-blue-400">ADMIN PANEL</h1>
            <p class="text-xs text-gray-500 mt-1">System Control Center</p>
        </div>
        <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
            <a href="admin_dashboard.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-chart-pie w-6"></i><span>ภาพรวมระบบ</span>
            </a>
            <a href="admin_users.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-users-gear w-6"></i><span>จัดการผู้ใช้งาน</span>
            </a>
            <a href="admin_projects.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-folder-tree w-6"></i><span>จัดการโครงการวิจัย</span>
            </a>
            <a href="admin_publication_stats.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-file-contract w-6"></i><span>บันทึกผลงานตีพิมพ์</span>
            </a>
            <a href="admin_pr_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-bullhorn w-6"></i><span>ข่าวประชาสัมพันธ์</span>
            </a>
            <a href="admin_activities.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-calendar-days w-6"></i><span>กิจกรรมฝ่ายฯ</span>
            </a>
            <a href="admin_academic_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-handshake-angle w-6"></i><span>ข่าวบริการวิชาการ</span>
            </a>

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Access Modes</div>
            <a href="../officer/dashboard.php" target="_blank" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-user-shield w-6"></i><span>เข้าสู่โหมดเจ้าหน้าที่</span>
            </a>
            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="admin_settings.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-cogs w-6"></i>
                <span>ตั้งค่าระบบ</span>
            </a>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i><span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                    <?php
                    // Fallback for environments without mbstring
                    echo isset($userName) && $userName
                        ? preg_replace('/^(.).*$/us', '$1', $userName)
                        : '?';
                    ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-medium"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="text-xs text-green-400">Administrator</div>
                </div>
            </div>
            <a href="../api/logout.php" class="block w-full text-center py-2 rounded border border-gray-700 hover:bg-red-600 hover:border-red-600 hover:text-white transition text-gray-400 text-sm">
                <i class="fa-solid fa-sign-out-alt mr-2"></i> ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden bg-gray-50">
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 shadow-sm shrink-0">
            <h2 class="text-xl font-bold text-gray-800">จัดการกิจกรรมฝ่ายฯ</h2>
            <div class="text-sm text-gray-500">Updated: <?php echo date('d/m/Y H:i'); ?></div>
        </header>

        <div class="flex-grow overflow-y-auto p-8">

            <?php if ($message): ?>
                <div class="mb-4 px-4 py-3 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                    <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Form -->
                <div class="lg:col-span-1 border border-gray-200 bg-white rounded-xl shadow-sm sticky top-0">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="font-bold text-gray-700">
                            <i class="fa-solid <?php echo $editData ? 'fa-pen' : 'fa-plus'; ?> mr-2 text-blue-500"></i>
                            <?php echo $editData ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรมใหม่'; ?>
                        </h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="<?php echo $editData ? 'edit' : 'add'; ?>">
                            <?php if ($editData): ?>
                                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editData['image_path']); ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">ชื่อกิจกรรม <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required value="<?php echo $editData ? htmlspecialchars($editData['title']) : ''; ?>"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" placeholder="เช่น อบรมเชิงปฏิบัติการ...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">รายละเอียด</label>
                                <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none"><?php echo $editData ? htmlspecialchars($editData['description']) : ''; ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">รูปภาพ</label>
                                <?php if ($editData && $editData['image_path']): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($editData['image_path']); ?>" class="img-preview w-full">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:bg-blue-50 file:text-blue-600">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-bold text-gray-600 mb-1">วันที่จัดกิจกรรม <span class="text-red-500">*</span></label>
                                    <input type="date" name="activity_date" required value="<?php echo $editData ? $editData['activity_date'] : date('Y-m-d'); ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600 mb-1">ลำดับ</label>
                                    <input type="number" name="sort_order" value="<?php echo $editData ? $editData['sort_order'] : 0; ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" min="0">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">ลิงก์เพิ่มเติม</label>
                                <input type="url" name="link_url" value="<?php echo $editData ? htmlspecialchars($editData['link_url']) : ''; ?>"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" placeholder="https://...">
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="flex-grow bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 transition font-bold text-sm shadow">
                                    <i class="fa-solid <?php echo $editData ? 'fa-save' : 'fa-plus'; ?> mr-1"></i>
                                    <?php echo $editData ? 'บันทึกการแก้ไข' : 'เพิ่มกิจกรรม'; ?>
                                </button>
                                <?php if ($editData): ?>
                                    <a href="admin_activities.php" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-bold">ยกเลิก</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right: List -->
                <div class="lg:col-span-2">
                    <div class="border border-gray-200 bg-white rounded-xl shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                            <h3 class="font-bold text-gray-700"><i class="fa-solid fa-list mr-2 text-blue-500"></i>กิจกรรมทั้งหมด</h3>
                            <p class="text-xs text-gray-500 mt-1">ทั้งหมด <?php echo count($items); ?> รายการ</p>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php if (empty($items)): ?>
                                <div class="p-12 text-center text-gray-400">
                                    <i class="fa-solid fa-inbox text-4xl mb-3 block"></i>ยังไม่มีกิจกรรม — เพิ่มกิจกรรมแรกได้เลย!
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="p-4 hover:bg-gray-50 transition flex gap-4 items-start <?php echo !$item['is_active'] ? 'opacity-50' : ''; ?>">
                                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                            <?php if ($item['image_path']): ?>
                                                <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fa-solid fa-image text-2xl"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h4 class="font-bold text-gray-800 text-sm truncate"><?php echo htmlspecialchars($item['title']); ?></h4>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                                <span><i class="fa-solid fa-calendar mr-1"></i><?php echo formatThaiDateAct($item['activity_date'], $thaiMonths); ?></span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $item['is_active'] ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-500'; ?>">
                                                    <?php echo $item['is_active'] ? 'แสดง' : 'ซ่อน'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <a href="?edit=<?php echo $item['id']; ?>" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition"><i class="fa-solid fa-pen text-sm"></i></a>
                                            <form method="POST" class="inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="p-2 <?php echo $item['is_active'] ? 'text-yellow-500 hover:bg-yellow-50' : 'text-green-500 hover:bg-green-50'; ?> rounded-lg transition">
                                                    <i class="fa-solid <?php echo $item['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?> text-sm"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline" onsubmit="return confirm('ยืนยันลบกิจกรรมนี้?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition"><i class="fa-solid fa-trash text-sm"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>
