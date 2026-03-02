<?php
session_start();
require_once '../api/db.php';

// Check Admin Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$userName = $_SESSION['fullname'];

// Auto-create table and initial data if not exists (helpful for remote domains)
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS na_publication_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(255) NOT NULL,
            value INT DEFAULT 0,
            color_start VARCHAR(20),
            color_end VARCHAR(20)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $countCategories = $conn->query("SELECT COUNT(*) FROM na_publication_stats")->fetchColumn();
    if ($countCategories == 0) {
        $conn->exec("
            INSERT INTO na_publication_stats (category, value, color_start, color_end) VALUES 
            ('ตีพิมพ์ในฐานข้อมูลระดับนานาชาติ', 0, '#22d3ee', '#3b82f6'),
            ('ตีพิมพ์ในฐานข้อมูล TCI 1', 0, '#a855f7', '#ec4899'),
            ('ตีพิมพ์ในฐานข้อมูล TCI 2', 0, '#f97316', '#eab308'),
            ('นำเสนอผลงานวิจัยวิชาการในที่ประชุมวิชาการระดับนานาชาติ', 0, '#10b981', '#14b8a6'),
            ('นำเสนอผลงานวิชาการในที่ประชุมวิชาการระดับชาติ', 0, '#f43f5e', '#ef4444');
        ");
    }

    $conn->exec("
        CREATE TABLE IF NOT EXISTS na_publications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            published_date DATE NOT NULL,
            category_id INT NOT NULL,
            link TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES na_publication_stats(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Auto-heal existing tables that might have been created with VARCHAR limits
    $conn->exec("ALTER TABLE na_publications MODIFY title TEXT NOT NULL");
    $conn->exec("ALTER TABLE na_publications MODIFY author TEXT NOT NULL");
    try {
        $conn->exec("ALTER TABLE na_publications ADD COLUMN link TEXT NULL AFTER category_id");
    } catch (Exception $e) { }
} catch (Exception $e) {
    // Silently ignore creation errors
}

// Fetch Categories for Dropdown
$categories = [];
try {
    $stmt = $conn->query("SELECT id, category FROM na_publication_stats ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load categories: " . $e->getMessage();
}

// Fetch existing publication records
$records = [];
try {
    $query = "
        SELECT p.*, s.category as category_name 
        FROM na_publications p 
        LEFT JOIN na_publication_stats s ON p.category_id = s.id 
        ORDER BY p.published_date DESC, p.id DESC
    ";
    $stmt = $conn->query($query);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load records: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Publication Records - Admin Panel</title>
    <link rel="stylesheet" href="../css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
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
                <i class="fa-solid fa-chart-pie w-6"></i>
                <span>ภาพรวมระบบ</span>
            </a>
            <a href="admin_users.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-users-gear w-6"></i>
                <span>จัดการผู้ใช้งาน</span>
            </a>
            <a href="admin_projects.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-folder-tree w-6"></i>
                <span>จัดการโครงการวิจัย</span>
            </a>
            
            <a href="admin_publication_stats.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg transition shadow-md">
                <i class="fa-solid fa-file-contract w-6"></i>
                <span>บันทึกผลงานตีพิมพ์</span>
            </a>
            <a href="admin_pr_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-bullhorn w-6"></i>
                <span>ข่าวประชาสัมพันธ์</span>
            </a>
            <a href="admin_activities.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-calendar-days w-6"></i>
                <span>กิจกรรมฝ่ายฯ</span>
            </a>
            <a href="admin_academic_news.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-handshake-angle w-6"></i>
                <span>ข่าวบริการวิชาการ</span>
            </a>

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Access Modes</div>
            <a href="../officer/dashboard.php" target="_blank" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-user-shield w-6"></i>
                <span>เข้าสู่โหมดเจ้าหน้าที่</span>
            </a>

            <div class="mt-8 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</div>
            <a href="../select_role.php" class="flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                <i class="fa-solid fa-exchange-alt w-6"></i>
                <span>เปลี่ยนบทบาท</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
             <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                    <?php echo (function_exists('mb_substr')) ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1); ?>
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
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 shadow-sm shrink-0">
            <h2 class="text-xl font-bold text-gray-800">บันทึกผลงานการตีพิมพ์และรายงาน (Publications)</h2>
            <div class="text-sm text-gray-500">Updated: <?php echo date('d/m/Y H:i'); ?></div>
        </header>

        <!-- Content -->
        <div class="flex-grow overflow-y-auto p-8">
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left Column: Add Form -->
                <div class="lg:col-span-1 border border-gray-200 bg-white rounded-xl shadow-sm sticky top-0">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="font-bold text-gray-700 text-lg flex items-center gap-2">
                            <i class="fa-solid fa-plus-circle text-blue-600"></i> เพิ่มรายการผลงานวิจัยใหม่
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">ข้อมูลในระบบจะถูกสรุปเป็นกราฟแบบอัตโนมัติ</p>
                    </div>
                    
                    <div class="p-6">
                        <form id="addRecordForm" class="space-y-4">
                            <input type="hidden" id="record_id" name="id" value="0">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อผลงานวิชาการ</label>
                                <textarea id="title" name="title" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" required placeholder="กรอกชื่อผลงานจัดเต็ม..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้วิจัย / ผู้นำเสนอ</label>
                                <input type="text" id="author" name="author" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" required placeholder="อ.จอห์น โด">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">วันที่ตีพิมพ์ / นำเสนอ</label>
                                <input type="date" id="published_date" name="published_date" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์ผลงาน (ถ้ามี)</label>
                                <input type="url" id="link" name="link" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" placeholder="https://doi.org/...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่ผลงาน</label>
                                <select id="category_id" name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border bg-white" required>
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="pt-4 flex gap-2">
                                <button type="submit" id="submitBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-md transition duration-150 ease-in-out">
                                    บันทึกผลงานเข้าระบบ
                                </button>
                                <button type="button" id="cancelEditBtn" onclick="cancelEdit()" class="hidden bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 px-4 rounded-md transition duration-150 ease-in-out">
                                    ยกเลิก
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Data Table -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <h3 class="font-bold text-gray-700">ฐานข้อมูลประวัติผลงานวิจัยทั้งหมด</h3>
                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-blue-800 bg-blue-100 rounded-full">
                            รวม <?php echo count($records); ?> รายการ
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                                        ชื่อผลงาน / ผู้วิจัย
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        หมวดหมู่
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        วันที่
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ดำเนินการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="recordsTableBody">
                                <?php if (empty($records)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fa-solid fa-folder-open text-gray-300 text-4xl mb-3 block"></i>
                                            ยังไม่มีข้อมูลผลงานในระบบ 
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($records as $record): ?>
                                        <tr id="row_<?php echo $record['id']; ?>" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-gray-900 line-clamp-2" title="<?php echo htmlspecialchars($record['title']); ?>">
                                                    <?php echo htmlspecialchars($record['title']); ?>
                                                </div>
                                                <div class="text-gray-500 mt-1 flex items-center gap-1 text-xs">
                                                    <i class="fa-solid fa-user-pen"></i> <?php echo htmlspecialchars($record['author']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 line-clamp-2 max-w-[200px]" title="<?php echo htmlspecialchars($record['category_name']); ?>">
                                                    <?php echo htmlspecialchars($record['category_name']); ?>
                                                </span>
                                                <?php if(!empty($record['link'])): ?>
                                                    <a href="<?php echo htmlspecialchars($record['link']); ?>" target="_blank" class="mt-2 inline-flex items-center text-xs text-blue-500 hover:text-blue-700 transition">
                                                        <i class="fa-solid fa-link mr-1"></i> ลิงก์เอกสาร
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                                <?php echo date('d/m/Y', strtotime($record['published_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                                <button onclick='editRecord(<?php echo json_encode($record, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded transition mr-1" title="แก้ไข">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button onclick="deleteRecord(<?php echo $record['id']; ?>)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="ลบ">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Toast Notification -->
            <div id="toast" class="fixed bottom-5 right-5 transform transition-all duration-300 translate-y-20 opacity-0 z-50">
                <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3">
                    <i id="toastIcon" class="fa-solid fa-circle-check text-green-400"></i>
                    <span id="toastMessage" class="font-medium">แจ้งเตือน</span>
                </div>
            </div>

        </div>
    </main>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            
            toastMessage.textContent = message;
            
            if (type === 'success') {
                toastIcon.className = 'fa-solid fa-circle-check text-green-400';
            } else {
                toastIcon.className = 'fa-solid fa-circle-exclamation text-red-400';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Add Record Handler
        document.getElementById('addRecordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';
            btn.disabled = true;

            const formData = new FormData(this);

            try {
                const response = await fetch('../api/add_publication_record.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showToast(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1000); // Reload to show new table data
                } else {
                    showToast(result.message || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        // Delete Record Handler
        async function deleteRecord(id) {
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผลงานนี้? การลบนี้ไม่สามารถเรียกคืนได้')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await fetch('../api/delete_publication_record.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showToast(result.message, 'success');
                    document.getElementById('row_' + id).remove();
                    
                    // Note: In a fully optimal SPAs, we'd minus the total counts HTML block, but reload is safer here.
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(result.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        }

        // Edit Record Handler
        function editRecord(record) {
            document.getElementById('record_id').value = record.id;
            document.getElementById('title').value = record.title;
            document.getElementById('author').value = record.author;
            document.getElementById('published_date').value = record.published_date;
            document.getElementById('category_id').value = record.category_id;
            document.getElementById('link').value = record.link || '';
            
            document.getElementById('submitBtn').innerHTML = '<i class="fa-solid fa-save"></i> อัปเดตข้อมูล';
            document.getElementById('submitBtn').className = 'flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-md transition duration-150 ease-in-out';
            document.getElementById('cancelEditBtn').classList.remove('hidden');
            
            // Scroll to form if on mobile
            window.scrollTo({ top: 0, behavior: 'smooth' });
            showToast('ดึงข้อมูลสำหรับแก้ไขแล้ว', 'success');
        }

        function cancelEdit() {
            document.getElementById('addRecordForm').reset();
            document.getElementById('record_id').value = '0';
            
            document.getElementById('submitBtn').innerHTML = 'บันทึกผลงานเข้าระบบ';
            document.getElementById('submitBtn').className = 'flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-md transition duration-150 ease-in-out';
            document.getElementById('cancelEditBtn').classList.add('hidden');
        }
    </script>
</body>

</html>
