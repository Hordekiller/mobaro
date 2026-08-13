-- =============================================
-- Mobaro Upgrade Script v1.1.10 (Update 46)
-- سیستم سؤالات متداول سراسری (FAQ) + فیکس‌های آکادمی
-- برای نصب‌های موجود روی هاست cPanel
-- کاملاً Safe: بدون DROP، TRUNCATE یا DELETE
-- اجرای چندباره: دارد — هر بخش شرطی/idempotent است
-- به مقادیر موجود جدول faqs دست نمی‌زند (فقط در صورت خالی بودن، نمونه اضافه می‌شود)
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- 1. جدول faqs (فقط اگر وجود نداشته باشد)
-- =============================================

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

-- =============================================
-- 2. ردیف‌های نمونه FAQ (فقط اگر جدول خالی باشد)
--    افزودنی — چیزی حذف یا بازنویسی نمی‌شود
-- =============================================

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

-- =============================================
-- 3. متای صفحه «سؤالات متداول» (فقط اگر جدول seo_meta موجود باشد)
--    upsert — افزودنی، هیچ ردیفی حذف نمی‌شود
-- =============================================

SET @has_seo = (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seo_meta');
SET @sql_seo = IF(@has_seo = 0, 'SELECT 1', '
INSERT INTO seo_meta
    (page_slug, meta_title, meta_description, canonical_url,
     og_title, og_description, robots)
VALUES
    ("faq",
     "سؤالات متداول | موبارو",
     "پاسخ سوالات متداول کاربران موبارو درباره رزرو نوبت، دورههای آکادمی، خرید از فروشگاه، روشهای پرداخت و بازگشت وجه.",
     NULL,
     "سؤالات متداول | موبارو",
     "پاسخ سوالات متداول کاربران موبارو",
     "index,follow")
ON DUPLICATE KEY UPDATE
    meta_title = VALUES(meta_title),
    meta_description = VALUES(meta_description),
    og_title = VALUES(og_title),
    og_description = VALUES(og_description),
    robots = VALUES(robots)');
PREPARE stmt_seo FROM @sql_seo; EXECUTE stmt_seo; DEALLOCATE PREPARE stmt_seo;

-- =============================================
-- 3.5 کلیدهای تنظیمات «پشتیبانی دوره» آکادمی
--     INSERT IGNORE — مقادیر موجود بازنویسی نمی‌شوند
-- =============================================

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('academy_support_days', 'پشتیبانی ۳۰ روزه'),
    ('academy_support_guarantee', 'ضمانت بازگشت وجه تا ۳۰ روز'),
    ('academy_support_feature_1', 'پاسخ به سؤالات در کمتر از ۲۴ ساعت'),
    ('academy_support_feature_2', 'منابع تکمیلی قابل دانلود'),
    ('academy_support_feature_3', 'مشاوره رایگان شغلی');

-- =============================================
-- 3.6 نرمال‌سازی دوره‌های رایگان
--     هر دوره بدون قیمت (price <= 0) رایگان (is_free = 1) می‌شود
--     تا دکمه «ثبت‌نام رایگان» و مسیر /enroll درست کار کنند
--     Idempotent — در هر بار اجرا بی‌اثر است
-- =============================================

UPDATE courses SET is_free = 1 WHERE price <= 0;

-- =============================================
-- 3.7 کلیدهای تنظیمات بخش‌های صفحه اصلی (آموزش + وبلاگ)
--     کاملاً داینامیک: عنوان، متن دکمه‌ها، تعداد نمایش و نمایش/مخفی
--     INSERT IGNORE — مقادیر موجود بازنویسی نمی‌شوند
-- =============================================

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

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- Done — جمع‌بندی
-- =============================================
SELECT CONCAT(
    'Upgrade v1.1.10 complete.',
    ' faqs table: ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'faqs'),
    ' faqs rows: ', (SELECT COUNT(*) FROM faqs),
    ' seo_meta(faq): ', (SELECT IF(COUNT(*) > 0, 'yes', 'no') FROM seo_meta WHERE page_slug = 'faq'),
    ' support settings: ', (SELECT COUNT(*) FROM settings WHERE setting_key IN ('academy_support_days','academy_support_guarantee','academy_support_feature_1','academy_support_feature_2','academy_support_feature_3')),
    ' homepage settings: ', (SELECT COUNT(*) FROM settings WHERE setting_key IN ('home_show_education','home_education_count','home_education_title','home_education_readmore','home_show_blog','home_blog_count','home_blog_title','home_blog_readmore','home_blog_all_text'))
) AS result;
