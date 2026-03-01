<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Auto-heal: Ensure `link` column exists even if admin page wasn't visited
    try {
        $conn->exec("ALTER TABLE na_publications ADD COLUMN link TEXT NULL AFTER category_id");
    } catch (Exception $e) { }

    // Check for limit parameter
    $limit = isset($_GET['limit']) ? $_GET['limit'] : '10';
    $limitSql = "";
    if ($limit !== 'all') {
        $limitNum = intval($limit);
        if ($limitNum > 0) {
            $limitSql = " LIMIT " . $limitNum;
        }
    }

    // Join na_publications with their respective category_id in na_publication_stats
    $sql = "
        SELECT 
            p.id, 
            p.title, 
            p.author, 
            p.published_date,
            p.link,
            s.category AS category_name, 
            s.color_start AS color_code 
        FROM na_publications p
        LEFT JOIN na_publication_stats s ON p.category_id = s.id
        ORDER BY p.published_date DESC, p.id DESC
        $limitSql
    ";
    
    $stmt = $conn->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the data, e.g., mapping Thai year if needed, or returning directly
    echo json_encode([
        'status' => 'success',
        'data' => $records
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
