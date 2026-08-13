-- ============================================================
-- Mobaro (mobaro.ir) — Upgrade v35 (update 35, v1.1.0)
-- Additive-only, idempotent database update.
--
-- Covers every DB requirement of update 35 in one file, so it is
-- safe to run on a host that has NOT applied any of the previous
-- updates (v14 / v18 / SMS / SEO) as well as on a fully-updated one:
--   1) blog_posts   — SEO / OG / image_alt columns
--   2) users        — phone_verified column
--   3) sms_* tables — sms_logs, sms_templates, sms_credits,
--                     verification_codes + default notification templates
--   4) sms_templates— slug column + unique index + backfill
--   5) seo_meta     — table + default rows
--   6) seo_meta     — robots column
--   6a) blog_categories — table + backfill from blog_posts
--   6b) orders      — idempotency_key column + index
--   6c) order_items — item_type column (from update v18)
--   7) appointments — slot_artist column, idx_slot index,
--                     uk_slot unique key + dedupe (anti double-booking)
--
-- Safe to run in phpMyAdmin (MySQL 5.7+ / MariaDB 10.2+).
-- NEVER deletes or overwrites existing data. Idempotent: safe to run
-- multiple times.
-- ============================================================

-- Force a utf8mb4 connection for the whole file. Without this, the
-- connection's default collation (e.g. latin1_swedish_ci on many shared
-- hosts) mixes with the utf8mb4 literals inside slugify_fa() and the
-- INSERT ... SELECT below, failing with: "Illegal mix of collations".
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @dbname = DATABASE();
SET @current_db = DATABASE();
SET @dbname = DATABASE();
SET @current_db = DATABASE();

-- ------------------------------------------------------------
-- Helper procedure: add a column only if it does not exist
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE add_column_if_missing(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_ddl TEXT
)
BEGIN
    DECLARE done INT DEFAULT 0;
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @current_db
          AND TABLE_NAME = p_table
          AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ', p_ddl);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- 1) blog_posts — SEO / OG / image_alt columns (fixes the 404
--    when saving a blog post: "Unknown column 'robots'")
-- ------------------------------------------------------------
CALL add_column_if_missing('blog_posts', 'image_alt',        'ADD COLUMN `image_alt` VARCHAR(500) DEFAULT NULL AFTER `image`');
CALL add_column_if_missing('blog_posts', 'meta_title',       'ADD COLUMN `meta_title` VARCHAR(255) DEFAULT NULL AFTER `image_alt`');
CALL add_column_if_missing('blog_posts', 'meta_description', 'ADD COLUMN `meta_description` TEXT DEFAULT NULL AFTER `meta_title`');
CALL add_column_if_missing('blog_posts', 'canonical_url',    'ADD COLUMN `canonical_url` VARCHAR(500) DEFAULT NULL AFTER `meta_description`');
CALL add_column_if_missing('blog_posts', 'og_title',         'ADD COLUMN `og_title` VARCHAR(255) DEFAULT NULL AFTER `canonical_url`');
CALL add_column_if_missing('blog_posts', 'og_description',   'ADD COLUMN `og_description` TEXT DEFAULT NULL AFTER `og_title`');
CALL add_column_if_missing('blog_posts', 'og_image',         'ADD COLUMN `og_image` VARCHAR(500) DEFAULT NULL AFTER `og_description`');
CALL add_column_if_missing('blog_posts', 'robots',           'ADD COLUMN `robots` VARCHAR(255) DEFAULT NULL AFTER `og_image`');

-- ------------------------------------------------------------
-- 2) users — phone_verified column
-- ------------------------------------------------------------
CALL add_column_if_missing('users', 'phone_verified', 'ADD COLUMN `phone_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`');

-- ------------------------------------------------------------
-- 3) Missing SMS tables (created only if absent)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    template_id INT DEFAULT NULL,
    type ENUM('verify', 'bulk', 'notification') NOT NULL DEFAULT 'bulk',
    status ENUM('sent', 'delivered', 'failed') NOT NULL DEFAULT 'sent',
    credits DECIMAL(10,2) DEFAULT 0,
    api_message_id VARCHAR(100) DEFAULT NULL,
    api_response TEXT DEFAULT NULL,
    sent_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sms_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) DEFAULT NULL,
    body TEXT NOT NULL,
    variables JSON DEFAULT NULL,
    sms_type ENUM('bulk', 'notification') NOT NULL DEFAULT 'bulk',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sms_templates_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sms_credits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount INT NOT NULL,
    cost DECIMAL(15,0) DEFAULT 0,
    description VARCHAR(255) DEFAULT '',
    payment_id VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(10) NOT NULL,
    purpose ENUM('register', 'login', 'reset_password') NOT NULL DEFAULT 'register',
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_purpose (phone, purpose),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed SMS notification templates only if their slug is missing
INSERT INTO sms_templates (name, slug, body, variables, sms_type, is_active)
SELECT * FROM (SELECT 'نوبت جدید', 'booking_new', 'نوبت جدید ثبت شد: {Service} | تاریخ: {Date} | ساعت: {Time} | مشتری: {Name} ({Phone})', '["Service","Date","Time","Name","Phone"]', 'notification', 1) t
WHERE NOT EXISTS (SELECT 1 FROM sms_templates WHERE slug = 'booking_new');

INSERT INTO sms_templates (name, slug, body, variables, sms_type, is_active)
SELECT * FROM (SELECT 'رسید سفارش', 'order_receipt', 'سفارش {Code} شما با موفقیت ثبت و پرداخت شد. مبلغ: {Total}', '["Code","Total"]', 'notification', 1) t
WHERE NOT EXISTS (SELECT 1 FROM sms_templates WHERE slug = 'order_receipt');

INSERT INTO sms_templates (name, slug, body, variables, sms_type, is_active)
SELECT * FROM (SELECT 'سفارش جدید', 'order_new', 'سفارش جدید {Code} به مبلغ {Total} توسط {Name} ثبت شد.', '["Code","Total","Name"]', 'notification', 1) t
WHERE NOT EXISTS (SELECT 1 FROM sms_templates WHERE slug = 'order_new');

INSERT INTO sms_templates (name, slug, body, variables, sms_type, is_active)
SELECT * FROM (SELECT 'وضعیت سفارش', 'order_status', 'وضعیت سفارش {Code} شما: {Status}', '["Code","Status"]', 'notification', 1) t
WHERE NOT EXISTS (SELECT 1 FROM sms_templates WHERE slug = 'order_status');

INSERT INTO sms_templates (name, slug, body, variables, sms_type, is_active)
SELECT * FROM (SELECT 'وضعیت نوبت', 'booking_status', 'وضعیت نوبت شما در تاریخ {Date} ساعت {Time}: {Status}', '["Date","Time","Status"]', 'notification', 1) t
WHERE NOT EXISTS (SELECT 1 FROM sms_templates WHERE slug = 'booking_status');

-- ------------------------------------------------------------
-- 4) sms_templates — slug column + unique index
--    (runs after the tables above are created, so it also works
--    from scratch on a fresh install)
-- ------------------------------------------------------------
CALL add_column_if_missing('sms_templates', 'slug', 'ADD COLUMN `slug` VARCHAR(120) DEFAULT NULL AFTER `name`');

SET @db = @current_db;
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sms_templates' AND INDEX_NAME = 'uq_sms_templates_slug');
SET @sql = IF(@idx_exists = 0,
              'ALTER TABLE `sms_templates` ADD UNIQUE KEY `uq_sms_templates_slug` (`slug`)',
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill slugs for existing templates (does not overwrite existing slugs)
UPDATE sms_templates SET slug = CONCAT('tpl-', id) WHERE slug IS NULL OR TRIM(slug) = '';

-- ------------------------------------------------------------
-- 5) seo_meta table (created only if absent) + default rows
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS seo_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(100) NOT NULL UNIQUE,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    og_title VARCHAR(255) DEFAULT NULL,
    og_description TEXT DEFAULT NULL,
    og_image VARCHAR(500) DEFAULT NULL,
    robots VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO seo_meta (page_slug) VALUES ('home'), ('shop'), ('blog'), ('contact'), ('about'), ('academy'), ('faq');

-- ------------------------------------------------------------
-- 5a) faqs table (created only if absent) + seed rows when empty
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active_sort (is_active, sort_order),
    KEY idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO faqs (question, answer, category, sort_order, is_active)
SELECT * FROM (
    SELECT 'چگونه میتوانم در دورههای آکادمی ثبتنام کنم؟' AS question,
           'برای ثبتنام در دورههای رایگان کافی است وارد حساب کاربری خود شوید و دکمه «ثبتنام رایگان» را بزنید. دورههای پولی از طریق افزودن به سبد خرید و پرداخت آنلاین فعال میشوند.' AS answer,
           'آکادمی' AS category, 1 AS sort_order, 1 AS is_active
    UNION ALL
    SELECT 'آیا پس از اتمام دوره گواهی دریافت میکنم؟',
           'بله، پس از تکمیل ۱۰۰٪ محتوای دوره، گواهی معتبر آکادمی موبارو برای شما صادر و در بخش دورههای من در دسترس قرار میگیرد.',
           'آکادمی', 2, 1
    UNION ALL
    SELECT 'چگونه میتوانم به صورت آنلاین نوبت رزرو کنم؟',
           'از صفحه رزرو نوبت، خدمت، آرایشگر و تاریخ موردنظر خود را انتخاب کرده و درخواست نوبت را ثبت کنید. پس از تأیید، نوبت شما در داشبورد و از طریق پیامک اطلاعرسانی میشود.',
           'نوبتدهی', 1, 1
    UNION ALL
    SELECT 'چه روشهای پرداختی در دسترس است؟',
           'پرداخت آنلاین از طریق درگاه زرینپال برای سفارشات فروشگاه، دورههای آموزشی و نوبتهای رزرو شده در دسترس است.',
           'پرداخت', 1, 1
    UNION ALL
    SELECT 'آیا امکان بازگشت وجه وجود دارد؟',
           'بله، تا ۳۰ روز پس از خرید در صورت عدم رضایت، امکان درخواست بازگشت وجه وجود دارد. برای این کار با پشتیبانی در تماس باشید.',
           'پرداخت', 2, 1
    UNION ALL
    SELECT 'چگونه میتوانم با پشتیبانی در تماس باشم؟',
           'از طریق فرم صفحه «تماس با ما» یا شماره تماس درجشده در وبسایت میتوانید سؤال یا مشکل خود را مطرح کنید. پاسخگویی حداکثر در کمتر از ۲۴ ساعت انجام میشود.',
           'پشتیبانی', 1, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM faqs LIMIT 1);

-- ------------------------------------------------------------
-- 5b) academy support settings (insert-ignore — never overwrite)
-- ------------------------------------------------------------
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('academy_support_days', 'پشتیبانی ۳۰ روزه'),
    ('academy_support_guarantee', 'ضمانت بازگشت وجه تا ۳۰ روز'),
    ('academy_support_feature_1', 'پاسخ به سؤالات در کمتر از ۲۴ ساعت'),
    ('academy_support_feature_2', 'منابع تکمیلی قابل دانلود'),
    ('academy_support_feature_3', 'مشاوره رایگان شغلی');

-- ------------------------------------------------------------
-- 5c) normalize free courses (price <= 0 → is_free = 1)
-- ------------------------------------------------------------
UPDATE courses SET is_free = 1 WHERE price <= 0;

-- ------------------------------------------------------------
-- 5d) homepage section settings (education + blog) — insert-ignore
--     کاملاً داینامیک: عنوان، متن دکمه‌ها، تعداد نمایش و نمایش/مخفی
-- ------------------------------------------------------------
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('home_show_education', '1'),
    ('home_education_count', '4'),
    ('home_education_title', 'آموزش‌های رایگان زیبایی'),
    ('home_education_readmore', 'مشاهده تمام دوره‌ها در آکادمی'),
    ('home_show_blog', '1'),
    ('home_blog_count', '3'),
    ('home_blog_title', 'جدیدترین مقالات مجله زیبایی'),
    ('home_blog_readmore', 'بیشتر بخوانید'),
    ('home_blog_all_text', 'مشاهده همه مطالب');

-- ------------------------------------------------------------
-- 6) seo_meta — robots column
-- ------------------------------------------------------------
CALL add_column_if_missing('seo_meta', 'robots', 'ADD COLUMN `robots` VARCHAR(255) DEFAULT NULL AFTER `og_image`');

-- ------------------------------------------------------------
-- 6a) blog_categories table (created only if absent) + backfill
--     from existing blog posts.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    post_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name),
    UNIQUE KEY unique_slug (slug),
    INDEX idx_sort (sort_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP FUNCTION IF EXISTS slugify_fa;
DELIMITER $$
CREATE FUNCTION slugify_fa(p_name VARCHAR(255) CHARACTER SET utf8mb4)
RETURNS VARCHAR(255) CHARACTER SET utf8mb4 DETERMINISTIC
BEGIN
    DECLARE res VARCHAR(255) CHARACTER SET utf8mb4;
    SET res = LOWER(CONVERT(p_name USING utf8mb4));
    SET res = REPLACE(res, _utf8mb4'آ', _utf8mb4'a');
    SET res = REPLACE(res, _utf8mb4'ا', _utf8mb4'a');
    SET res = REPLACE(res, _utf8mb4'ب', _utf8mb4'b');
    SET res = REPLACE(res, _utf8mb4'پ', _utf8mb4'p');
    SET res = REPLACE(res, _utf8mb4'ت', _utf8mb4't');
    SET res = REPLACE(res, _utf8mb4'ث', _utf8mb4's');
    SET res = REPLACE(res, _utf8mb4'ج', _utf8mb4'j');
    SET res = REPLACE(res, _utf8mb4'چ', _utf8mb4'ch');
    SET res = REPLACE(res, _utf8mb4'ح', _utf8mb4'h');
    SET res = REPLACE(res, _utf8mb4'خ', _utf8mb4'kh');
    SET res = REPLACE(res, _utf8mb4'د', _utf8mb4'd');
    SET res = REPLACE(res, _utf8mb4'ذ', _utf8mb4'z');
    SET res = REPLACE(res, _utf8mb4'ر', _utf8mb4'r');
    SET res = REPLACE(res, _utf8mb4'ز', _utf8mb4'z');
    SET res = REPLACE(res, _utf8mb4'ژ', _utf8mb4'zh');
    SET res = REPLACE(res, _utf8mb4'س', _utf8mb4's');
    SET res = REPLACE(res, _utf8mb4'ش', _utf8mb4'sh');
    SET res = REPLACE(res, _utf8mb4'ص', _utf8mb4's');
    SET res = REPLACE(res, _utf8mb4'ض', _utf8mb4'z');
    SET res = REPLACE(res, _utf8mb4'ط', _utf8mb4't');
    SET res = REPLACE(res, _utf8mb4'ظ', _utf8mb4'z');
    SET res = REPLACE(res, _utf8mb4'ع', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4'غ', _utf8mb4'gh');
    SET res = REPLACE(res, _utf8mb4'ف', _utf8mb4'f');
    SET res = REPLACE(res, _utf8mb4'ق', _utf8mb4'gh');
    SET res = REPLACE(res, _utf8mb4'ک', _utf8mb4'k');
    SET res = REPLACE(res, _utf8mb4'گ', _utf8mb4'g');
    SET res = REPLACE(res, _utf8mb4'ل', _utf8mb4'l');
    SET res = REPLACE(res, _utf8mb4'م', _utf8mb4'm');
    SET res = REPLACE(res, _utf8mb4'ن', _utf8mb4'n');
    SET res = REPLACE(res, _utf8mb4'و', _utf8mb4'v');
    SET res = REPLACE(res, _utf8mb4'ه', _utf8mb4'h');
    SET res = REPLACE(res, _utf8mb4'ی', _utf8mb4'y');
    SET res = REPLACE(res, _utf8mb4'ئ', _utf8mb4'e');
    SET res = REPLACE(res, _utf8mb4'ء', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4'ـ', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4' ', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'٫', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'،', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4',', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'.', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'/', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'-', _utf8mb4'-');
    SET res = REPLACE(res, _utf8mb4'؟', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4'?', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4'!', _utf8mb4'');
    SET res = REPLACE(res, _utf8mb4'،', _utf8mb4'-');
    RETURN res;
END$$
DELIMITER ;

-- Fix slugs that were previously written with a broken collation
-- (e.g. 'tرند-ها2026'): rebuild them from the category name. Existing
-- ASCII slugs are left untouched.
UPDATE blog_categories SET slug = slugify_fa(name) WHERE slug REGEXP '[^a-z0-9-]';

INSERT IGNORE INTO blog_categories (name, slug, sort_order, is_active, post_count)
SELECT
    p.category,
    slugify_fa(p.category),
    ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) - 1,
    1,
    COUNT(*)
FROM blog_posts p
WHERE p.category IS NOT NULL AND TRIM(p.category) != ''
  AND NOT EXISTS (SELECT 1 FROM blog_categories c WHERE c.name = p.category)
GROUP BY p.category
ORDER BY COUNT(*) DESC;

UPDATE blog_categories bc
SET post_count = (SELECT COUNT(*) FROM blog_posts bp WHERE bp.category = bc.name);

-- ------------------------------------------------------------
-- 6b) orders — idempotency_key column + index (prevents duplicate
--     order submissions; only added if missing).
-- ------------------------------------------------------------
CALL add_column_if_missing('orders', 'idempotency_key', 'ADD COLUMN `idempotency_key` VARCHAR(64) DEFAULT NULL AFTER `coupon_discount`');

SET @db = @current_db;
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders' AND INDEX_NAME = 'idx_orders_idempotency');
SET @sql = IF(@idx_exists = 0,
              'ALTER TABLE `orders` ADD INDEX `idx_orders_idempotency` (`idempotency_key`)',
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 6c) order_items — item_type column (from update v18; added with
--     DEFAULT NULL so legacy rows keep using the code fallback).
-- ------------------------------------------------------------
CALL add_column_if_missing('order_items', 'item_type', 'ADD COLUMN `item_type` VARCHAR(20) DEFAULT NULL AFTER `quantity`');

-- ------------------------------------------------------------
-- 7) appointments — anti double-booking: slot_artist column,
--    idx_slot index and uk_slot unique key (idempotent).
--    Duplicate active bookings for the same slot are removed
--    keeping the earliest row only (prevents double booking).
--    slot_artist is a plain column (NOT a generated column):
--    MySQL/MariaDB reject a STORED generated column that
--    references a column having a foreign key constraint.
-- ------------------------------------------------------------
SET @has_slot_artist = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'slot_artist'
);

SET @sql = IF(@has_slot_artist = 0,
    'ALTER TABLE `appointments` ADD COLUMN `slot_artist` INT DEFAULT NULL AFTER `status`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill slot_artist for existing active rows (idempotent: rows whose
-- slot_artist is already set or that are cancelled are left untouched).
UPDATE `appointments`
SET `slot_artist` = `artist_id`
WHERE `slot_artist` IS NULL AND `status` != 'cancelled';

-- Remove duplicate *active* bookings for the same slot (keep the earliest id)
SET @slot_key_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'appointments' AND INDEX_NAME = 'uk_slot'
);
SET @dedupe = IF(@slot_key_exists = 0,
    'DELETE a FROM appointments a INNER JOIN appointments b
       ON a.appointment_date = b.appointment_date
      AND a.appointment_time = b.appointment_time
      AND a.slot_artist <=> b.slot_artist
      AND a.slot_artist IS NOT NULL
      AND a.status != ''cancelled''
      AND a.id > b.id',
    'SELECT 1');
PREPARE stmt FROM @dedupe;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_slot = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'appointments' AND INDEX_NAME = 'idx_slot'
);
SET @sql = IF(@idx_slot = 0,
    'ALTER TABLE `appointments` ADD INDEX `idx_slot` (`appointment_date`, `appointment_time`, `artist_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(@slot_key_exists = 0,
    'ALTER TABLE `appointments` ADD UNIQUE KEY `uk_slot` (`appointment_date`, `appointment_time`, `slot_artist`)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Cleanup helper procedure / function
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS add_column_if_missing;
DROP FUNCTION IF EXISTS slugify_fa;

-- ============================================================
-- Done.
-- ============================================================
