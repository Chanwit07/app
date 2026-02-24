-- ====================================================
-- Maintenance Insight Platform - Reports (Power BI / URL Dashboard)
-- Database: maintenance_platform
-- ====================================================

USE `maintenance_platform`;

-- ====================================================
-- Table: dashboard_reports
-- ====================================================
CREATE TABLE IF NOT EXISTS `dashboard_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL COMMENT 'ชื่อรายงาน',
    `description` TEXT DEFAULT NULL COMMENT 'คำอธิบายรายงาน',
    `embed_url` TEXT NOT NULL COMMENT 'URL สำหรับ Embed (Power BI / Website)',
    `category` VARCHAR(100) DEFAULT 'general' COMMENT 'หมวดหมู่',
    `icon` VARCHAR(50) DEFAULT 'fa-chart-bar' COMMENT 'Font Awesome icon class',
    `color_from` VARCHAR(20) DEFAULT '#667eea' COMMENT 'Gradient color start',
    `color_to` VARCHAR(20) DEFAULT '#764ba2' COMMENT 'Gradient color end',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'สถานะการแสดงผล',
    `sort_order` INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`),
    INDEX `idx_sort` (`sort_order`),
    INDEX `idx_category` (`category`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Sample Data (Optional - can be removed)
-- ====================================================
INSERT INTO `dashboard_reports` (`title`, `description`, `embed_url`, `category`, `icon`, `color_from`, `color_to`, `sort_order`) VALUES
('ตัวอย่าง Power BI Report', 'ตัวอย่าง Dashboard สำหรับทดสอบการแสดงผล Power BI', 'https://app.powerbi.com/view?r=eyJrIjoiYjFiMjk5YjQtMjNhZS00OTBiLThmNjMtOWZlNzY0MmU1YjU1IiwidCI6ImRmODY3OWNkLWE4MGUtNDVkOC05OWFjLWM4M2VkN2ZmOTVhMCJ9', 'general', 'fa-chart-pie', '#667eea', '#764ba2', 1);
