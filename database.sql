-- ====================================================
-- Maintenance Insight Platform - Database Schema
-- Database: maintenance_platform
-- ====================================================

CREATE DATABASE IF NOT EXISTS `maintenance_platform` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `maintenance_platform`;

-- ====================================================
-- Table: users
-- ====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `fullname` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('user', 'admin', 'super_admin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Table: asset_requests (Asset/Unit Requests)
-- ====================================================
CREATE TABLE IF NOT EXISTS `asset_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `department` VARCHAR(150) NOT NULL COMMENT 'หน่วยงาน',
    `asset_id` VARCHAR(100) NOT NULL COMMENT 'เลขที่สินทรัพย์',
    `asset_group` VARCHAR(150) NOT NULL COMMENT 'กลุ่มสินทรัพย์/ยูนิต',
    `serial_number` VARCHAR(100) NOT NULL COMMENT 'Serial Number',
    `account_type` VARCHAR(100) NOT NULL COMMENT 'ประเภทบัญชี',
    `image` VARCHAR(255) DEFAULT NULL COMMENT 'Uploaded image path',
    `status` ENUM('Pending', 'Processing', 'Completed') NOT NULL DEFAULT 'Pending',
    `admin_note` TEXT DEFAULT NULL COMMENT 'บันทึกหมายเหตุ (Admin)',
    `updated_by` INT DEFAULT NULL COMMENT 'Admin who last updated',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `finished_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_asset_id` (`asset_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Table: supply_requests (Supply Code/Edit Requests)
-- ====================================================
CREATE TABLE IF NOT EXISTS `supply_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `request_type` ENUM('new_code', 'edit_detail') NOT NULL COMMENT 'ประเภทคำขอ',
    `item_number` VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่สิ่งของ (for edit)',
    `item_name` TEXT NOT NULL COMMENT 'ชื่อรายการ / ชื่อเดิม',
    `new_item_name` TEXT DEFAULT NULL COMMENT 'ชื่อใหม่ (for edit)',
    `unit` VARCHAR(50) NOT NULL COMMENT 'หน่วยนับ',
    `annual_usage` INT DEFAULT NULL COMMENT 'ปริมาณใช้ต่อปี',
    `max_min` VARCHAR(50) DEFAULT NULL COMMENT 'Max-Min',
    `image` VARCHAR(255) DEFAULT NULL COMMENT 'Uploaded image path',
    `status` ENUM('Pending', 'Processing', 'Completed') NOT NULL DEFAULT 'Pending',
    `admin_note` TEXT DEFAULT NULL COMMENT 'บันทึกหมายเหตุ (Admin)',
    `updated_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `finished_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_request_type` (`request_type`),
    INDEX `idx_item_number` (`item_number`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Table: audit_trail (Audit Log)
-- ====================================================
CREATE TABLE IF NOT EXISTS `audit_trail` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL DEFAULT 0,
    `action` VARCHAR(255) NOT NULL COMMENT 'Action description',
    `target_table` VARCHAR(50) DEFAULT NULL COMMENT 'Table affected',
    `target_id` INT DEFAULT NULL COMMENT 'Record ID',
    `details` TEXT DEFAULT NULL COMMENT 'Additional details (JSON)',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_target` (`target_table`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Seed Data: Default Super Admin Account
-- Password: admin123 (bcrypt hashed)
-- ====================================================
INSERT INTO `users` (`username`, `password_hash`, `fullname`, `email`, `department`, `role`, `status`) 
VALUES (
    'admin', 
    '$2y$10$NiKKjOBaVCg1JvQqDpZm3uw5cWpX28y7l2RITD6n7Dt63J/9zX4X2', 
    'ผู้ดูแลระบบ', 
    'admin@srt.or.th', 
    'แผนกสารสนเทศพัสดุ', 
    'super_admin', 
    'active'
);

-- Default User Account for testing
-- Password: user123
INSERT INTO `users` (`username`, `password_hash`, `fullname`, `email`, `department`, `role`, `status`) 
VALUES (
    'user01', 
    '$2y$10$Bc9AE/1ZDeQUjACRkl1Y7.tFmP9CmFX4f.qxIChobAMKvYNG5IV/e', 
    'ผู้ใช้งานทดสอบ', 
    'user@srt.or.th', 
    'แผนกบัญชี', 
    'user', 
    'active'
);

-- Default Admin Account for testing
-- Password: admin123
INSERT INTO `users` (`username`, `password_hash`, `fullname`, `email`, `department`, `role`, `status`) 
VALUES (
    'staff01', 
    '$2y$10$NiKKjOBaVCg1JvQqDpZm3uw5cWpX28y7l2RITD6n7Dt63J/9zX4X2', 
    'เจ้าหน้าที่พัสดุ', 
    'staff@srt.or.th', 
    'แผนกสารสนเทศพัสดุ', 
    'admin', 
    'active'
);
