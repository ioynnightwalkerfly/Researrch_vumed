<?php
// migration_settings.php
require_once 'api/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Insert default values if not exists
    INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('meeting_system_enabled', '1');
    ";
    
    $conn->exec($sql);
    echo "Settings migration successful. Table created and seeded.";
} catch(PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
