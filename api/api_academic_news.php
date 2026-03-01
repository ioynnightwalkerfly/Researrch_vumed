<?php
// api/api_academic_news.php — Public endpoint for fetching active academic service news
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

try {
    // Auto-create table if not exists
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

    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;
    $category = $_GET['category'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $where = "WHERE is_active = 1";
    $params = [];

    if ($category && in_array($category, ['project', 'announce', 'event', 'mou'])) {
        $where .= " AND category = ?";
        $params[] = $category;
    }

    // Total count
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM academic_service_news $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Fetch data
    $stmt = $conn->prepare("
        SELECT id, title, description, image_path, category, published_date, link_url
        FROM academic_service_news
        $where
        ORDER BY sort_order ASC, published_date DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $news = $stmt->fetchAll();

    // Format Thai dates
    $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $categoryLabels = [
        'project' => 'โครงการ',
        'announce' => 'ประกาศ',
        'event' => 'กิจกรรม',
        'mou' => 'MOU'
    ];

    foreach ($news as &$item) {
        $d = new DateTime($item['published_date']);
        $day = $d->format('j');
        $month = $thaiMonths[(int)$d->format('n')];
        $year = (int)$d->format('Y') + 543;
        $item['published_date_thai'] = "$day $month $year";
        $item['category_label'] = $categoryLabels[$item['category']] ?? $item['category'];
    }

    echo json_encode([
        'success' => true,
        'data' => $news,
        'total' => (int)$total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
