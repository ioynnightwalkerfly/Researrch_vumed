<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

try {
    // Auto-create table if not exists
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

    $stmt = $conn->query("
        SELECT id, title, description, image_path, activity_date, link_url
        FROM na_activities
        WHERE is_active = 1
        ORDER BY sort_order ASC, activity_date DESC
        LIMIT 20
    ");
    $activities = $stmt->fetchAll();

    $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    foreach ($activities as &$item) {
        $d = new DateTime($item['activity_date']);
        $day = $d->format('j');
        $month = $thaiMonths[(int)$d->format('n')];
        $year = (int)$d->format('Y') + 543;
        $item['activity_date_thai'] = "$day $month $year";
    }

    echo json_encode(['success' => true, 'data' => $activities], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
