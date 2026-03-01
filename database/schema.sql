-- สร้างฐานข้อมูล (ถ้ายังไม่มี)
CREATE DATABASE IF NOT EXISTS research_portal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE research_portal_db;

-- 1. ตารางข้อมูลผู้ใช้งาน (Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- ข้อมูลเข้าระบบ
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- เก็บแบบ Hash ห้ามเก็บ Plain text
    email VARCHAR(100) NOT NULL UNIQUE,
    is_verified BOOLEAN DEFAULT FALSE, -- สถานะยืนยันอีเมล
    
    -- ข้อมูลส่วนตัว
    id_card_number VARCHAR(13) NOT NULL UNIQUE, -- เลขบัตรประชาชน
    prefix_th VARCHAR(20),
    firstname_th VARCHAR(100) NOT NULL,
    lastname_th VARCHAR(100) NOT NULL,
    prefix_eng VARCHAR(20),
    firstname_eng VARCHAR(100),
    lastname_eng VARCHAR(100),
    
    -- การติดต่อ
    phone_office VARCHAR(20),
    mobile_phone VARCHAR(20),
    
    -- หน่วยงานและบทบาท
    faculty VARCHAR(100), -- คณะ (ถ้าเลือกอื่นๆ ให้เก็บค่าที่กรอกมา)
    is_external BOOLEAN DEFAULT FALSE, -- บุคลากรภายนอกหรือไม่
    
    -- Checkbox คุณสมบัติ (เก็บเป็น JSON หรือแยก Column ก็ได้ แต่แยก Column จะ Query ง่ายกว่า)
    qual_health_personnel BOOLEAN DEFAULT FALSE, -- บุคลากรด้านสุขภาพ
    qual_social_scientist BOOLEAN DEFAULT FALSE, -- นักสังคมศาสตร์
    qual_non_medical BOOLEAN DEFAULT FALSE, -- ผู้ที่ไม่ได้ประกอบวิชาชีพแพทย์
    qual_community_rep BOOLEAN DEFAULT FALSE, -- ตัวแทนชุมชน
    qual_lawyer BOOLEAN DEFAULT FALSE, -- นักกฎหมาย
    
    -- Roles (เลือกได้มากกว่า 1)
    role_researcher BOOLEAN DEFAULT FALSE, -- นักวิจัย
    role_coordinator BOOLEAN DEFAULT FALSE, -- ผู้ประสานงาน
    role_admin BOOLEAN DEFAULT FALSE, -- แอดมิน (Set ทีหลัง)
    role_staff BOOLEAN DEFAULT FALSE, -- เจ้าหน้าที่
    
    cv_file_path VARCHAR(255), -- ที่เก็บไฟล์ประวัติผู้วิจัย
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. ตารางประวัติการอบรม (Training Records) - One-to-Many กับ Users
CREATE TABLE IF NOT EXISTS user_trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    course_name_th VARCHAR(255) NOT NULL,
    course_name_eng VARCHAR(255),
    training_date DATE,
    details TEXT,
    certificate_file_path VARCHAR(255), -- ที่เก็บไฟล์ใบเซอร์
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. (แถม) ตารางโครงการวิจัย (Projects) - เผื่อหน้า Dashboard
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    researcher_id INT NOT NULL, -- เจ้าของโครงการ
    
    project_name_th VARCHAR(255) NOT NULL,
    project_name_eng VARCHAR(255),
    status ENUM('draft', 'submitted', 'reviewing', 'approved', 'rejected') DEFAULT 'draft',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (researcher_id) REFERENCES users(id)
);