<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    // We get all categories and count how many records belong to each in na_publications
    // Using LEFT JOIN ensures that even if a category has 0 records, it still shows up in the chart
    $query = "
        SELECT 
            s.id, 
            s.category, 
            s.color_start, 
            s.color_end,
            COUNT(p.id) as value
        FROM na_publication_stats s
        LEFT JOIN na_publications p ON s.id = p.category_id
        GROUP BY s.id, s.category, s.color_start, s.color_end
        ORDER BY s.id ASC
    ";
    
    $stmt = $conn->query($query);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $stats]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
