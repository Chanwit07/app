-- ====================================================
-- Notification System - Database Migration
-- Run this SQL in phpMyAdmin or MySQL CLI
-- ====================================================

USE `maintenance_platform`;

-- ====================================================
-- Table: notifications
-- Stores in-app bell notifications for admins
-- ====================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL COMMENT 'Target user (NULL = all admins)',
    `type` VARCHAR(50) NOT NULL DEFAULT 'new_request' COMMENT 'new_request, status_update, system',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) NULL COMMENT 'URL to redirect when clicked',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_read` (`user_id`, `is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Table: app_settings
-- Stores application settings (Telegram, etc.)
-- ====================================================
CREATE TABLE IF NOT EXISTS `app_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `updated_by` INT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default Telegram settings
INSERT IGNORE INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('telegram_bot_token', ''),
('telegram_chat_id', ''),
('telegram_enabled', '0');
