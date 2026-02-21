-- ====================================================
-- Inventory Asset Module - Database Schema
-- Table: inventory_assets
-- ====================================================

USE `maintenance_platform`;

CREATE TABLE IF NOT EXISTS `inventory_assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_type` ENUM('fixed_asset', 'container', 'computer') NOT NULL COMMENT 'ประเภท: สินทรัพย์ถาวร, ภาชนะถาวร, คอมพิวเตอร์',
    
    -- Common fields
    `asset_code` VARCHAR(100) NOT NULL COMMENT 'รหัสสินทรัพย์ / รหัสคอมพิวเตอร์',
    `asset_name` VARCHAR(255) NOT NULL COMMENT 'รายการสินทรัพย์ / รายการอุปกรณ์',
    `quantity` INT NOT NULL DEFAULT 1 COMMENT 'จำนวนคงเหลือ',
    `unit` VARCHAR(50) NOT NULL COMMENT 'หน่วยนับ',
    `asset_value` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'มูลค่าสินทรัพย์ (ซื้อ/ประเมิน)',
    `acquisition_date` DATE DEFAULT NULL COMMENT 'วันที่ได้มา / วันที่เริ่มสัญญาซื้อ',
    
    `responsible_dept` VARCHAR(150) DEFAULT NULL COMMENT 'หน่วยงานผู้รับผิดชอบ',
    `install_department` VARCHAR(150) DEFAULT NULL COMMENT 'จุดติดตั้ง: หน่วยงาน (สำหรับ Filter)',
    `install_section` VARCHAR(150) DEFAULT NULL COMMENT 'จุดติดตั้ง: แผนก (สำหรับ Filter)',
    
    `remarks` TEXT DEFAULT NULL COMMENT 'หมายเหตุ',
    `image_path` VARCHAR(255) DEFAULT NULL COMMENT 'พาร์ทรูปภาพ',
    
    -- Computer specific fields (nullable)
    `computer_type` ENUM('own', 'rent') DEFAULT NULL COMMENT 'ประเภท: เครื่อง งทป., เครื่องเช่า',
    `brand` VARCHAR(100) DEFAULT NULL COMMENT 'ยี่ห้อ',
    `model` VARCHAR(100) DEFAULT NULL COMMENT 'รุ่น',
    `serial_number` VARCHAR(100) DEFAULT NULL COMMENT 'Serial No',
    `warranty_years` INT DEFAULT NULL COMMENT 'รับประกันความชำรุด (ปี)',
    
    -- System fields
    `status` ENUM('active', 'broken', 'written_off') NOT NULL DEFAULT 'active' COMMENT 'สถานะ: ใช้งาน, ชำรุด, แทงจำหน่าย',
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_asset_type` (`asset_type`),
    INDEX `idx_asset_code` (`asset_code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_install_dept` (`install_department`),
    INDEX `idx_install_sec` (`install_section`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
