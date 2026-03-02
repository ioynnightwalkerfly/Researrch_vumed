<?php
// migration_meetings.php
require_once 'api/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS meetings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        meeting_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        location VARCHAR(255),
        meeting_round INT DEFAULT 1,
        status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS meeting_projects (
        meeting_id INT NOT NULL,
        project_id INT NOT NULL,
        PRIMARY KEY(meeting_id, project_id)
    );
    ";
    
    $conn->exec($sql);
    echo "Migration successful. Tables created.";
} catch(PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
