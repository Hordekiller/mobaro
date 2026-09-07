# گزارش نواقص احتمالی روی هاست (Production Readiness Audit)

تاریخ: ۲۰۲۶-۰۷-۱۰ (آخرین به‌روزرسانی: ۲۰۲۶-۰۹-۰۷)

---

## ✅ موارد حل‌شده

این موارد در نسخه‌های پس از گزارش اصلاح شده‌اند و دیگر مشکل ندارند:

### 1. session cookie secure flag پیش پشت proxy
- **فایل:** `app/Auth.php`
- **وضعیت:** حل شد — تشخیص HTTPS علاوه بر `$_SERVER['HTTPS']` از `HTTP_X_FORWARDED_PROTO` هم انجام می‌شود (پشت Cloudflare و reverse proxy کار می‌کند).

### 2. storage/ بدون محافظت
- **مسیر:** `storage/`
- **وضعیت:** حل شد — فایل `.htaccess` با `Require all denied` (و fallback برای Apache 2.2) داخل `storage/` قرار دارد.

### 3. ZarinPal json_encode بدون UNICODE
- **فایل:** `app/Services/ZarinPal.php`
- **وضعیت:** حل شد — پرچم `JSON_UNESCAPED_UNICODE` به ارسال درخواست اضافه شده است.

### 4. RateLimiter پاک نمی‌شد
- **فایل:** `app/RateLimiter.php`
- **وضعیت:** حل شد — `cleanup()` در `bootstrap.php`، `AuthController`، `AdminController` و `SmsService` صدا زده می‌شود و رکوردهای قدیمی در زمان لاگین پاک می‌شوند.

### 5. خطاها ذخیره نمی‌شدند
- **فایل:** `app/bootstrap.php`
- **وضعیت:** حل شد — `log_errors` فعال، مسیر `storage/logs/app.log` و `set_error_handler` اضافه شده؛ خطاها ثبت و به پاسخ ۵۰۰ تبدیل می‌شوند.

### 6. composer.lock
- **فایل:** `composer.lock`
- **وضعیت:** حل شد — فایل لاک در مخزن حاضر است و در CI با `composer install` استفاده می‌شود.

### 7. mb_internal_encoding
- **فایل:** `app/bootstrap.php`
- **وضعیت:** حل شد — `mb_internal_encoding('UTF-8')` به bootstrap اضافه شده است.

### 8. هدرهای امنیتی
- **فایل:** `app/bootstrap.php`
- **وضعیت:** حل شد — `Content-Security-Policy`، `X-Frame-Options: DENY`، `X-Content-Type-Options: nosniff`، `Referrer-Policy` و `Strict-Transport-Security: max-age=31536000` تنظیم شده‌اند.

### 9. فایل‌های session موروثی در مخزن
- **مسیر:** `storage/sessions/`
- **وضعیت:** حل شد — این پوشه به `.gitignore` اضافه شده و فایل‌های session در مخزن نگهداری نمی‌شوند.

### 10. collation `utf8mb4_0900_ai_ci` در schema
- **فایل:** `database/schema.sql`
- **وضعیت:** حل شد — schema جاری از `utf8mb4_unicode_ci` استفاده می‌کند (سازگار با MySQL 5.7 و MariaDB 10.3+).

---

## 🚨 بحرانی (سایت کار نمی‌کنه یا کرش می‌کنه)

### 1. نسخه PHP فعلی مخزن
- **فایل:** کل پروژه
- **مشکل:** کد از `match()` (PHP 8.0)، `mixed` type hint (PHP 8.0)، `str_contains()` (PHP 8.0)، `callable|array` union type (PHP 8.1) و `readonly`/arrow functions استفاده می‌کند
- **راهکار:** هاست باید PHP 8.1+ داشته باشد (CI روی PHP 8.2 و 8.3 اجرا می‌شود؛ تولید پیشنهادی PHP 8.2+)

### 2. دامپ قدیمی `database/mobaro.sql`
- **فایل:** `database/mobaro.sql`
- **مشکل:** این دامپ قدیمی هنوز از `utf8mb4_0900_ai_ci` استفاده می‌کند (فقط MySQL 8)
- **راهکار:** برای نصب جدید از `database/schema.sql` استفاده کنید (سازگار با MySQL 5.7+/MariaDB). فایل `mobaro.sql` فقط برای ارجاع است و نگهداری نمی‌شود.

---

## 🔴 امنیتی

### 1. تنظیمات `.env` در سیستم شخصی
- **فایل:** `.env` (محلی)
- **مشکل:** در سیستم شخصی هنوز `DB_USER=root` با `DB_PASS=` خالی است
- **راهکار:** روی هاست `.env` را با اطلاعات واقعی دیتابیس و `APP_URL` دامنه واقعی پر کنید. `.env` در مخزن نگهداری نمی‌شود (`gitignore` شده).

---

## 🟡 باگ‌های ریز

### 1. `extract()` در BaseController
- **فایل:** `app/Controllers/BaseController.php` (خطوط ~۸۰ و ~۱۶۹)
- **مشکل:** `extract($data)` می‌تواند متغیرهای محلی مثل `$view` و `$data` را overwrite کند (فعلاً با پیش‌فیلتر `$safe` محدود شده)
- **راهکار:** در نسخه‌های آینده به مرجع مستقیم `$data['key']` در viewها مهاجرت شود

---

## 🟢 پیشنهادی (بهبود کیفیت)

### 1. Document Root
- **مشکل:** بعضی هاست‌های ساده اجازه تنظیم Document Root روی `public/` را نمی‌دهند
- **راهکار:** اگه هاست اجازه نداد، فایل `index.php` و `.htaccess` از `public/` به روت منتقل شوند (یا از `public/.htaccess` از پیش‌فرض استفاده شود)