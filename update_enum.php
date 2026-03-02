<?php
require_once 'api/db.php';
try {
    // เปลี่ยนจาก ENUM เป็น VARCHAR เพื่อให้ยืดหยุ่นต่อการเพิ่มประเภทใหม่ๆ ในอนาคต
    $conn->exec("ALTER TABLE projects MODIFY research_type VARCHAR(100) NOT NULL");
    
    // อัปเดตข้อมูลเก่าให้ตรงกับของใหม่ (ถ้าจำเป็น)
    $conn->exec("UPDATE projects SET research_type = 'health_science' WHERE research_type = 'clinical'");
    $conn->exec("UPDATE projects SET research_type = 'social_science' WHERE research_type = 'social'");

    echo "Table projects updated successfully.";
} catch (Exception $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
