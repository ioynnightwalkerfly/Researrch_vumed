<?php
require_once 'api/db.php';

// Auto-create table
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS academic_service_news (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_path VARCHAR(255),
            category ENUM('project','announce','event','mou') DEFAULT 'project',
            published_date DATE NOT NULL,
            link_url TEXT,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) { }

// Params
$category = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$where = "WHERE is_active = 1";
$params = [];
if ($category && in_array($category, ['project', 'announce', 'event', 'mou'])) {
    $where .= " AND category = ?";
    $params[] = $category;
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM academic_service_news $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT * FROM academic_service_news $where ORDER BY sort_order ASC, published_date DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$news = $stmt->fetchAll();

$thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$categories = [
    'project' => ['label' => 'โครงการ', 'icon' => 'presentation'],
    'announce' => ['label' => 'ประกาศ', 'icon' => 'megaphone'],
    'event' => ['label' => 'กิจกรรม', 'icon' => 'calendar-check'],
    'mou' => ['label' => 'MOU', 'icon' => 'handshake'],
];

function formatThaiDateNews($date, $months) {
    $d = new DateTime($date);
    return $d->format('j') . ' ' . $months[(int)$d->format('n')] . ' ' . ((int)$d->format('Y') + 543);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสารงานบริการวิชาการ - คณะแพทยศาสตร์ มหาวิทยาลัยวงษ์ชวลิตกุล</title>
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --dark-bg: #080810;
            --accent-teal: #255b5c;
            --accent-teal-light: #2dd4bf;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background: var(--dark-bg);
            color: #e2e8f0;
        }
        html { scroll-behavior: smooth; }

        .reveal {
            opacity: 0; transform: translateY(40px) scale(0.98);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active { opacity: 1; transform: translateY(0) scale(1); }

        .news-grid-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .news-grid-card:hover {
            background: rgba(37, 91, 92, 0.12);
            border-color: rgba(37, 91, 92, 0.4);
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(37, 91, 92, 0.2);
        }

        .filter-btn {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .filter-btn:hover {
            border-color: rgba(45,212,191,0.4);
            color: #2dd4bf;
            background: rgba(45,212,191,0.08);
        }
        .filter-btn.active {
            background: rgba(45,212,191,0.15);
            border-color: rgba(45,212,191,0.5);
            color: #2dd4bf;
        }

        .page-btn {
            width: 36px; height: 36px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03);
            color: rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .page-btn:hover { border-color: rgba(45,212,191,0.4); color: #2dd4bf; }
        .page-btn.active {
            background: rgba(45,212,191,0.2);
            border-color: #2dd4bf;
            color: #2dd4bf;
        }
        .page-btn.disabled { opacity: 0.3; pointer-events: none; }

        .news-tag {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
        }
        .news-tag-project { background: rgba(45,212,191,0.12); color: #2dd4bf; border: 1px solid rgba(45,212,191,0.25); }
        .news-tag-announce { background: rgba(14,165,233,0.12); color: #38bdf8; border: 1px solid rgba(14,165,233,0.25); }
        .news-tag-event { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
        .news-tag-mou { background: rgba(168,85,247,0.12); color: #c084fc; border: 1px solid rgba(168,85,247,0.25); }

        .glow-orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15;
            animation: float 8s ease-in-out infinite; pointer-events: none;
        }
        @keyframes float { 0%,100% { transform: translateY(0px) scale(1); } 50% { transform: translateY(-30px) scale(1.1); } }

        .nav-scrolled { background: rgba(8,8,16,0.95) !important; box-shadow: 0 4px 30px rgba(0,0,0,0.3); }
        #scrollProgress { position: fixed; top: 0; left: 0; height: 3px; z-index: 100; background: linear-gradient(90deg,#255b5c,#2dd4bf); width: 0%; transition: width 0.1s; }

        .gradient-project { background: linear-gradient(135deg, rgba(37,91,92,0.5), rgba(45,212,191,0.2)); }
        .gradient-announce { background: linear-gradient(135deg, rgba(14,165,233,0.4), rgba(56,189,248,0.15)); }
        .gradient-event { background: linear-gradient(135deg, rgba(245,158,11,0.4), rgba(251,191,36,0.15)); }
        .gradient-mou { background: linear-gradient(135deg, rgba(168,85,247,0.4), rgba(192,132,252,0.15)); }

        .icon-project { color: rgba(45,212,191,0.6); }
        .icon-announce { color: rgba(56,189,248,0.6); }
        .icon-event { color: rgba(251,191,36,0.6); }
        .icon-mou { color: rgba(192,132,252,0.6); }
    </style>
</head>

<body class="min-h-screen">

    <div id="scrollProgress"></div>

    <!-- Fixed Background -->
    <div class="fixed inset-0 z-[-1]">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 20% 0%, rgba(37,91,92,0.25) 0%, transparent 60%), radial-gradient(ellipse at 80% 100%, rgba(45,212,191,0.1) 0%, transparent 50%), var(--dark-bg);"></div>
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.03)_1px,transparent_1px)] [background-size:32px_32px]"></div>
        <div class="glow-orb w-[400px] h-[400px] bg-[#2dd4bf] top-[10%] left-[5%]" style="animation-delay: 0s;"></div>
        <div class="glow-orb w-[300px] h-[300px] bg-[#0ea5e9] bottom-[20%] right-[10%]" style="animation-delay: 3s;"></div>
    </div>

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 backdrop-blur-xl bg-[#080810]/80 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="academic_service_landing.html" class="flex items-center gap-3 text-white/80 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span class="text-sm font-medium">กลับหน้าบริการวิชาการ</span>
            </a>
            <span class="text-white/40 text-xs tracking-wider uppercase">Academic Service News</span>
        </div>
    </nav>

    <!-- Header -->
    <header class="pt-28 pb-8 px-6 text-center reveal">
        <div class="max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-[#255b5c]/20 border border-[#255b5c]/30 rounded-full text-[#2dd4bf] text-xs font-semibold tracking-wider uppercase mb-4">
                <i data-lucide="newspaper" class="w-3.5 h-3.5"></i>
                News & Updates
            </div>
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-3">ข่าวสารงานบริการวิชาการ</h1>
            <p class="text-white/40 text-base max-w-xl mx-auto">ติดตามข่าวสาร กิจกรรม และประกาศล่าสุดจากงานบริการวิชาการ คณะแพทยศาสตร์</p>
        </div>
    </header>

    <!-- Filters -->
    <section class="max-w-6xl mx-auto px-6 mb-8 reveal">
        <div class="flex flex-wrap justify-center gap-3">
            <a href="academic_service_news.php" class="filter-btn <?php echo !$category ? 'active' : ''; ?>">ทั้งหมด</a>
            <?php foreach ($categories as $key => $cat): ?>
                <a href="?category=<?php echo $key; ?>" class="filter-btn <?php echo $category === $key ? 'active' : ''; ?>">
                    <?php echo $cat['label']; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="text-center text-white/30 text-sm mt-4">พบ <?php echo $total; ?> รายการ</p>
    </section>

    <!-- News Grid -->
    <section class="max-w-6xl mx-auto px-6 pb-16 reveal">
        <?php if (empty($news)): ?>
            <div class="text-center py-20">
                <i data-lucide="inbox" class="w-16 h-16 text-white/15 mx-auto mb-4"></i>
                <p class="text-white/30 text-lg">ยังไม่มีข่าวสาร</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($news as $item):
                    $cat = $categories[$item['category']] ?? ['label' => $item['category'], 'icon' => 'file-text'];
                    $tagClass = 'news-tag-' . $item['category'];
                    $gradClass = 'gradient-' . $item['category'];
                    $iconClass = 'icon-' . $item['category'];
                ?>
                    <article class="news-grid-card">
                        <?php if ($item['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 <?php echo $gradClass; ?> flex items-center justify-center">
                                <i data-lucide="<?php echo $cat['icon']; ?>" style="width:48px;height:48px;" class="<?php echo $iconClass; ?>"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="news-tag <?php echo $tagClass; ?>"><?php echo $cat['label']; ?></span>
                                <span class="text-white/30 text-xs"><?php echo formatThaiDateNews($item['published_date'], $thaiMonths); ?></span>
                            </div>
                            <h3 class="text-white font-bold text-base mb-2 leading-snug line-clamp-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="text-white/45 text-sm mb-4 leading-relaxed line-clamp-3"><?php echo htmlspecialchars($item['description']); ?></p>
                            <?php if ($item['link_url']): ?>
                                <a href="<?php echo htmlspecialchars($item['link_url']); ?>" target="_blank"
                                    class="inline-flex items-center gap-1.5 text-[#2dd4bf] text-sm font-semibold hover:gap-2.5 transition-all">
                                    อ่านรายละเอียด <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 mt-12">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                       class="page-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                           class="page-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                       class="page-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-white/30 text-xs">© 2026 งานบริการวิชาการ คณะแพทยศาสตร์ มหาวิทยาลัยวงษ์ชวลิตกุล</p>
            <a href="academic_service_landing.html" class="text-[#2dd4bf] text-xs hover:underline flex items-center gap-1">
                <i data-lucide="home" class="w-3 h-3"></i> กลับหน้าบริการวิชาการ
            </a>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            document.getElementById('scrollProgress').style.width = progress + '%';
        });

        const nav = document.querySelector('nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('nav-scrolled', window.scrollY > 50);
        });
    </script>

</body>
</html>
