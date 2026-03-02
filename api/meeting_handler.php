<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userRole = $_SESSION['role'] ?? '';
// Handle case where role might be a single string or an array
$isOfficerOrAdmin = false;
if (is_array($userRole)) {
    $isOfficerOrAdmin = in_array('admin', $userRole) || in_array('staff', $userRole) || in_array('secretary', $userRole);
} else {
    $isOfficerOrAdmin = ($userRole === 'admin' || $userRole === 'staff' || $userRole === 'secretary');
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$userId = $_SESSION['user_id'];

// Normally, you would check real roles from DB. For now, we trust the session or verify if needed.
// This is a simplified check based on 'is_admin' or similar if they exist. We assume admins/staff can manage meetings.

try {
    if ($action === 'get_meetings') {
        // Fetch all meetings
        $stmt = $conn->prepare("SELECT * FROM meetings ORDER BY meeting_date ASC, start_time ASC");
        $stmt->execute();
        $meetings = $stmt->fetchAll();

        // Format for FullCalendar
        $events = [];
        foreach ($meetings as $m) {
            $events[] = [
                'id' => $m['id'],
                'title' => $m['title'],
                'start' => $m['meeting_date'] . 'T' . $m['start_time'],
                'end' => $m['meeting_date'] . 'T' . $m['end_time'],
                'extendedProps' => [
                    'location' => $m['location'],
                    'round' => $m['meeting_round'],
                    'status' => $m['status']
                ]
            ];
        }
        echo json_encode($events);
    } 
    elseif ($action === 'create_meeting') {
        if (!$isOfficerOrAdmin) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }

        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        $location = $_POST['location'] ?? '';
        $round = intval($_POST['round'] ?? 1);

        if (!$title || !$date || !$start || !$end) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO meetings (title, meeting_date, start_time, end_time, location, meeting_round, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $date, $start, $end, $location, $round, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Meeting created', 'id' => $conn->lastInsertId()]);
    }
    elseif ($action === 'delete_meeting') {
        if (!$isOfficerOrAdmin) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }

        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM meetings WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Meeting deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
