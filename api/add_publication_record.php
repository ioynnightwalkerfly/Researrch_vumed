<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check Admin Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $published_date = isset($_POST['published_date']) ? $_POST['published_date'] : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';

    if (empty($title) || empty($author) || empty($published_date) || $category_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit();
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Auto-heal: Ensure `link` column exists
    try {
        $conn->exec("ALTER TABLE na_publications ADD COLUMN link TEXT NULL AFTER category_id");
    } catch (Exception $e) { }

    try {
        if ($id > 0) {
            // UPDATE existing record
            $stmt = $conn->prepare("UPDATE na_publications SET title=:title, author=:author, published_date=:published_date, category_id=:category_id, link=:link WHERE id=:id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success_message = 'อัปเดตข้อมูลผลงานวิจัยเรียบร้อยแล้ว';
        } else {
            // INSERT new record
            $stmt = $conn->prepare("INSERT INTO na_publications (title, author, published_date, category_id, link) VALUES (:title, :author, :published_date, :category_id, :link)");
            $success_message = 'บันทึกข้อมูลผลงานวิจัยเรียบร้อยแล้ว';
        }
        
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':author', $author, PDO::PARAM_STR);
        $stmt->bindParam(':published_date', $published_date, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':link', $link, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => $success_message]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
