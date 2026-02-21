-- ====================================================
-- Learning Materials Module - Database Schema
-- Table: learning_materials
-- ====================================================

USE `maintenance_platform`;

CREATE TABLE IF NOT EXISTS `learning_materials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL COMMENT 'ชื่อบทเรียน',
    `description` TEXT DEFAULT NULL COMMENT 'รายละเอียดบทเรียน',
    `youtube_url` VARCHAR(500) NOT NULL COMMENT 'ลิงก์ YouTube เต็ม',
    `youtube_video_id` VARCHAR(50) NOT NULL COMMENT 'รหัสวิดีโอ YouTube',
    `category` VARCHAR(100) DEFAULT NULL COMMENT 'หมวดหมู่',
    `sort_order` INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`category`),
    INDEX `idx_sort_order` (`sort_order`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
