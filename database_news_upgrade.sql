-- ============================================
-- Upgrade landing_news: add content (Markdown) + slug + cover_image
-- ============================================
ALTER TABLE `landing_news`
    ADD COLUMN `slug` VARCHAR(255) DEFAULT NULL COMMENT 'URL Slug' AFTER `title`,
    ADD COLUMN `content` LONGTEXT DEFAULT NULL COMMENT 'เนื้อหาเต็ม (Markdown)' AFTER `excerpt`,
    ADD COLUMN `cover_image` VARCHAR(255) DEFAULT NULL COMMENT 'รูปภาพปก' AFTER `content`;

-- Add unique index on slug
ALTER TABLE `landing_news` ADD UNIQUE INDEX `idx_news_slug` (`slug`);

-- Update existing rows with auto-generated slugs
UPDATE `landing_news` SET `slug` = CONCAT('news-', id) WHERE `slug` IS NULL;
