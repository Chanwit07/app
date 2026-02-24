-- ====================================================
-- Database Schema for Blog & Article System
-- ====================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ตารางเก็บ Tags/หมวดหมู่บทความ
CREATE TABLE IF NOT EXISTS `blog_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อ Tag',
    `color_code` VARCHAR(50) NOT NULL DEFAULT 'primary' COMMENT 'CSS Class color (e.g. primary, success, warning)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ตารางเก็บบทความ
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL Slug ของบทความ',
    `title` VARCHAR(255) NOT NULL COMMENT 'หัวข้อบทความ',
    `excerpt` TEXT COMMENT 'เนื้อหาย่อ',
    `content` LONGTEXT NOT NULL COMMENT 'เนื้อหาบทความ (Markdown)',
    `cover_image` VARCHAR(255) DEFAULT NULL COMMENT 'รูปภาพปก',
    `author_id` INT(11) NOT NULL COMMENT 'ID ผู้เขียน (users.id)',
    `views` INT UNSIGNED DEFAULT 0 COMMENT 'จำนวนคนเข้าชม',
    `read_time` INT UNSIGNED DEFAULT 1 COMMENT 'เวลาอ่านโดยประมาณ (นาที)',
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft' COMMENT 'สถานะบทความ',
    `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่เผยแพร่',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางความสัมพันธ์ระหว่างบทความและ Tag (Many-to-Many)
CREATE TABLE IF NOT EXISTS `blog_post_tags` (
    `post_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `blog_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Seed Data
-- ====================================================

-- Insert sample tags
INSERT IGNORE INTO `blog_tags` (`id`, `name`, `color_code`) VALUES
(1, 'เทคโนโลยี', 'primary'),
(2, 'การบำรุงรักษา', 'success'),
(3, 'คู่มือการใช้งาน', 'info'),
(4, 'ข่าวสารองค์กร', 'warning'),
(5, 'ความปลอดภัย', 'danger');

-- Insert sample blog posts
-- Note: Assuming author_id = 1 (admin user) exists.
INSERT IGNORE INTO `blog_posts` (`id`, `slug`, `title`, `excerpt`, `content`, `cover_image`, `author_id`, `read_time`, `status`, `published_at`) VALUES
(1, 'markdown-guide-for-articles', 'คู่มือการเขียนบทความด้วย Markdown', 'การเขียนบทความโดยใช้ Markdown ช่วยให้การจัดรูปแบบข้อความทำได้ง่ายและรวดเร็ว รองรับทั้งหัวข้อ ตัวหนา ลิสต์ และโค้ด', 
'# วิธีใช้งาน Markdown เบื้องต้น\r\n\r\nการเขียนบทความด้วย Markdown ทำให้อ่านและเขียนได้ง่ายขึ้น นี่คือตัวอย่างการใช้งาน:\r\n\r\n## 1. หัวข้อ (Headings)\r\nใช้เครื่องหมาย `#` สำหรับหัวข้อขนาดต่างๆ\r\n\r\n## 2. ตัวหนา ตัวเอียง\r\n**ตัวหนา** และ *ตัวเอียง*\r\n\r\n## 3. รายการ (Lists)\r\n- รายการที่ 1\r\n- รายการที่ 2\r\n  - รายการย่อย\r\n\r\n## 4. โค้ด (Code Blocks)\r\n```php\r\necho \"Hello World\";\r\n```\r\n\r\n## 5. การอ้างอิง (Blockquotes)\r\n> นวัตกรรมคือสิ่งที่แยกผู้นำออกจากผู้ตาม - Steve Jobs\r\n',
NULL, 1, 2, 'published', '2026-02-24 08:00:00'),

(2, 'preventive-maintenance-tips', 'ทริคการบำรุงรักษาเชิงป้องกัน (Preventive Maintenance)', 'การบำรุงรักษาเชิงป้องกันคือกุญแจสำคัญในการลดความเสี่ยงที่เครื่องจักรจะหยุดทำงานกะทันหัน',
'# การบำรุงรักษาเชิงป้องกัน (PM)\r\n\r\nการบำรุงรักษาเครื่องจักรหรืออุปกรณ์เชิงป้องกัน ช่วยลดต้นทุนระยะยาวได้อย่างมาก...\r\n\r\n## ทำไมต้องทำ PM?\r\n1. ลดช่วงเวลา Downtime\r\n2. ยืดอายุการใช้งานอุปกรณ์\r\n3. เพิ่มความปลอดภัยให้บุคลากร\r\n\r\n## ขั้นตอนการทำ PM ที่ดี\r\n- การตรวจสอบรูทีน (Routine checks)\r\n- การวิเคราะห์การสั่นสะเทือน\r\n- การหล่อลื่นชิ้นส่วนเคลื่อนไหว',
NULL, 1, 3, 'published', '2026-02-20 10:30:00');

-- Insert post-tag relationships
INSERT IGNORE INTO `blog_post_tags` (`post_id`, `tag_id`) VALUES
(1, 1),
(1, 3),
(2, 2),
(2, 5);

SET FOREIGN_KEY_CHECKS = 1;
