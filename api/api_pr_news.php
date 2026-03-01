<?php
// api/api_pr_news.php — Public endpoint for fetching active PR news
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

try {
    $stmt = $conn->query("
        SELECT id, title, description, image_path, published_date, link_url
        FROM na_pr_news
        WHERE is_active = 1
        ORDER BY sort_order ASC, published_date DESC
        LIMIT 20
    ");
    $news = $stmt->fetchAll();

    // Format Thai dates and add full image URLs
    $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    foreach ($news as &$item) {
        $d = new DateTime($item['published_date']);
        $day = $d->format('j');
        $month = $thaiMonths[(int)$d->format('n')];
        $year = (int)$d->format('Y') + 543;
        $item['published_date_thai'] = "$day $month $year";
    }

    echo json_encode(['success' => true, 'data' => $news], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
