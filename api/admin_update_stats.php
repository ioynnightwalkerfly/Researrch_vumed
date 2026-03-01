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
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $value = isset($_POST['value']) ? intval($_POST['value']) : 0;

    if ($id > 0) {
        try {
            $stmt = $conn->prepare("UPDATE na_publication_stats SET value = :value WHERE id = :id");
            $stmt->bindParam(':value', $value, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Stat updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update stat.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
