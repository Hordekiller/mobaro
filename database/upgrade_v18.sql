-- =============================================
-- Mobaro Upgrade Script v1.8.0 (Update 26)
-- درگاه پرداخت زرین‌پال + صفحات شرایط/حریم خصوصی
-- برای نصب‌های موجود روی هاست cPanel
-- کاملاً Safe: بدون DROP، TRUNCATE یا DELETE
-- اجرای چندباره: دارد — هر بخش شرطی/idempotent است
-- به جدول settings دست نمی‌زند (مقادیر برند و تماس حفظ می‌شوند)
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- 1. جدول لاگ پرداخت (فقط اگر وجود نداشته باشد)
-- =============================================

CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    order_id INT DEFAULT NULL,
    gateway VARCHAR(50) NOT NULL DEFAULT 'zarinpal',
    action VARCHAR(50) NOT NULL DEFAULT 'verify',
    amount DECIMAL(15,0) DEFAULT 0,
    authority VARCHAR(255) DEFAULT NULL,
    ref_id VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT NULL,
    request_data TEXT DEFAULT NULL,
    response_data TEXT DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_order (order_id),
    INDEX idx_authority (authority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. ستون‌های جدول orders (فقط اگر وجود نداشته باشند)
-- =============================================

SET @has_auth = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'authority');
SET @sql_auth = IF(@has_auth = 0,
    'ALTER TABLE orders ADD COLUMN authority VARCHAR(255) DEFAULT NULL AFTER payment_id', 'SELECT 1');
PREPARE stmt_auth FROM @sql_auth; EXECUTE stmt_auth; DEALLOCATE PREPARE stmt_auth;

SET @has_ref = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'ref_id');
SET @sql_ref = IF(@has_ref = 0,
    'ALTER TABLE orders ADD COLUMN ref_id VARCHAR(255) DEFAULT NULL AFTER authority', 'SELECT 1');
PREPARE stmt_ref FROM @sql_ref; EXECUTE stmt_ref; DEALLOCATE PREPARE stmt_ref;

-- =============================================
-- 3. متای صفحات «شرایط استفاده» و «حریم خصوصی» (فقط اگر جدول seo_meta موجود باشد)
--    upsert — افزودنی، هیچ ردیفی حذف نمی‌شود
-- =============================================

SET @has_seo = (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seo_meta');
SET @sql_seo = IF(@has_seo = 0, 'SELECT 1', '
INSERT INTO seo_meta
    (page_slug, meta_title, meta_description, canonical_url,
     og_title, og_description, robots)
VALUES
    ("terms",
     "شرایط استفاده | موبارو",
     "شرایط استفاده از خدمات موبارو؛ قوانین رزرو نوبت، خرید از فروشگاه، دوره‌های آموزشی، پرداخت، کیف پول و مسئولیت‌های کاربران را مطالعه کنید.",
     NULL,
     "شرایط استفاده | موبارو",
     "شرایط استفاده از خدمات موبارو",
     "index,follow"),
    ("privacy",
     "حریم خصوصی | موبارو",
     "سیاست حریم خصوصی موبارو؛ چه اطلاعاتی از شما جمع‌آوری می‌شود، چگونه محافظت و استفاده می‌شود و چه حقوقی دارید.",
     NULL,
     "حریم خصوصی | موبارو",
     "حریم خصوصی کاربران موبارو",
     "index,follow")
ON DUPLICATE KEY UPDATE
    meta_title = VALUES(meta_title),
    meta_description = VALUES(meta_description),
    og_title = VALUES(og_title),
    og_description = VALUES(og_description),
    robots = VALUES(robots)');
PREPARE stmt_seo FROM @sql_seo; EXECUTE stmt_seo; DEALLOCATE PREPARE stmt_seo;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- Done — جمع‌بندی
-- =============================================
SELECT CONCAT(
    'Upgrade v1.8.0 complete.',
    ' payment_logs: ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_logs'),
    ' orders.authority: ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'authority'),
    ' orders.ref_id: ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'ref_id'),
    ' seo_meta(terms): ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM seo_meta WHERE page_slug = 'terms'),
    ' seo_meta(privacy): ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM seo_meta WHERE page_slug = 'privacy')
) AS result;
