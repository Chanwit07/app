-- ============================================
-- Landing Page CMS Tables
-- ============================================

-- ตาราง landing_news (ข่าวประชาสัมพันธ์)
CREATE TABLE IF NOT EXISTS `landing_news` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL COMMENT 'หัวข้อข่าว',
    `category` VARCHAR(100) NOT NULL DEFAULT 'ข่าวองค์กร' COMMENT 'หมวดหมู่',
    `excerpt` TEXT NOT NULL COMMENT 'เนื้อหาย่อ',
    `publish_date` DATE NOT NULL COMMENT 'วันที่ประกาศ',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'สถานะ',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง landing_divisions (หน่วยงานในสังกัด)
CREATE TABLE IF NOT EXISTS `landing_divisions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL COMMENT 'ชื่อหน่วยงาน',
    `location` VARCHAR(255) NOT NULL COMMENT 'สถานที่ตั้ง',
    `description` TEXT NOT NULL COMMENT 'คำอธิบาย',
    `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-building' COMMENT 'FontAwesome Icon',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'ลำดับ',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'สถานะ',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Seed Data — ข่าวประชาสัมพันธ์
-- ============================================
INSERT INTO `landing_news` (`title`, `category`, `excerpt`, `publish_date`, `status`) VALUES
('บอร์ด รฟท. อนุมัติจัดหารถจักรบำรุงทางชุดใหม่', 'ข่าวองค์กร', 'อนุมัติงบฯ จัดหารถจักรบำรุงทางทดแทนรถเดิมที่มีสภาพเสื่อมโทรม เพื่อเพิ่มประสิทธิภาพงานบำรุงทาง...', '2026-02-19', 'active'),
('แผนซ่อมบำรุงวาระหนักประจำปี 2569', 'แผนงาน', 'กำหนดการซ่อมบำรุงวาระหนักรถจักรดีเซลและรถดีเซลราง ประจำปีงบประมาณ 2569...', '2026-02-01', 'active'),
('เปิดอบรมหลักสูตรช่างเทคนิคระบบรางรุ่นใหม่', 'ฝึกอบรม', 'ฝ่ายการช่างกลเปิดรับสมัครช่างเทคนิคเข้าอบรมหลักสูตรใหม่ เน้นเทคโนโลยีระบบรางสมัยใหม่...', '2026-01-15', 'active');

-- ============================================
-- Seed Data — หน่วยงานในสังกัด
-- ============================================
INSERT INTO `landing_divisions` (`name`, `location`, `description`, `icon`, `sort_order`, `status`) VALUES
('ศูนย์ลากเลื่อน', 'บางซื่อ กรุงเทพฯ', 'ศูนย์กลางซ่อมบำรุงรถจักรและล้อเลื่อนหลัก', 'fa-warehouse', 1, 'active'),
('กองปฏิบัติการลากเลื่อน', 'กรุงเทพฯ', 'ควบคุมการปฏิบัติงานขบวนรถทั่วประเทศ', 'fa-gears', 2, 'active'),
('กองลากเลื่อนเขตนครราชสีมา', 'นครราชสีมา', 'ดูแลรถจักรและล้อเลื่อนภาคตะวันออกเฉียงเหนือ', 'fa-map-location-dot', 3, 'active'),
('งานรถจักร', 'Locomotive Workshop', 'ซ่อมบำรุงรถจักรดีเซลไฟฟ้าทุกรุ่น', 'fa-train', 4, 'active'),
('งานรถดีเซลราง', 'Diesel Railcar', 'ดูแลซ่อมบำรุงรถดีเซลรางทุกซีรีส์', 'fa-train-tram', 5, 'active'),
('งานซ่อมบำรุงตู้โดยสาร', 'Coach Maintenance', 'ซ่อมบำรุงตู้โดยสารให้พร้อมใช้งาน', 'fa-couch', 6, 'active');
