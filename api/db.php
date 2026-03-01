<?php
// api/db.php
$host = '127.0.0.1';
$dbname = 'md_job';
$username = 'root';
$password = '';

try {
    // เพิ่ม options ให้รองรับภาษาไทยและจัดการ Error ได้ดีขึ้น
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // ห้าม echo หรือ die ตรงนี้ เพราะจะทำให้ JSON พัง
    // เราจะปล่อยให้ error ส่งต่อไปที่ register_action.php เอง
    throw new Exception("Database Connection Failed: " . $e->getMessage());
}
?>