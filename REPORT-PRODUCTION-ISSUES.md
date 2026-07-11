# گزارش نواقص احتمالی روی هاست (Production Readiness Audit)

تاریخ: ۲۰۲۶-۰۷-۱۰

---

## 🚨 بحرانی (سایت کار نمی‌کنه یا کرش می‌کنه)

### 1. نسخه PHP
- **فایل:** کل پروژه
- **مشکل:** کد از `match()` (PHP 8.0)، `mixed` type hint (PHP 8.0)، `str_contains()` (PHP 8.0) و `callable|array` union type (PHP 8.1) استفاده می‌کنه
- **راهکار:** هاست باید PHP 8.1+ داشته باشه

### 2. utf8mb4_0900_ai_ci در دامپ دیتابیس
- **فایل:** `database/mobaro.sql`
- **مشکل:** این کالیشن فقط MySQL 8 پشتیبانی می‌کنه، MariaDB و MySQL 5.x ارور می‌دن
- **راهکار:** با اسکریپت جایگزین بشه با `utf8mb4_unicode_ci`

### 3. تنظیمات .env
- **فایل:** `.env`
- **مشکل:** `DB_USER=root` و `DB_PASS=` خالی — روی هاست باید اطلاعات واقعی دیتابیس ست بشه. `APP_URL` هم باید دامنه واقعی باشه
- **راهکار:** قبل از آپلود `.env` رو با اطلاعات هاست پر کنید

### 4. فایل‌های session در storage
- **مسیر:** `storage/sessions/`
- **مشکل:** فایل‌های session از لوکال توی پروژه باقی مونده (مثل `sess_a5i66d...`)
- **راهکار:** دستور `rm -f storage/sessions/sess_*` اجرا بشه

---

## 🔴 امنیتی

### 5. session cookie secure flag
- **فایل:** `app/Auth.php` خط ۱۲
- **مشکل:** از `$_SERVER['HTTPS']` استفاده می‌کنه که پشت Cloudflare یا reverse proxy ممکنه درست کار نکنه
- **راهکار:** چک کردن `HTTP_X_FORWARDED_PROTO` هم اضافه بشه

### 6. storage/ بدون محافظت
- **مسیر:** `storage/`
- **مشکل:** اگه این پوشه زیر `public_html` قرار بگیره، فایل‌های session و data قابل دانلود هستند
- **راهکار:** فایل `.htaccess` با `Deny from all` داخل `storage/` قرار داده بشه

---

## 🟡 باگ‌های ریز

### 7. ZarinPal json_encode بدون UNICODE
- **فایل:** `app/Services/ZarinPal.php`
- **مشکل:** پرچم `JSON_UNESCAPED_UNICODE` در ارسال درخواست به زرین‌پال استفاده نشده
- **راهکار:** اضافه کردن `JSON_UNESCAPED_UNICODE` به توابع json_encode

### 8. RateLimiter پاک نمی‌شه
- **فایل:** `app/RateLimiter.php`
- **مشکل:** متد `cleanup()` هیچ‌وقت صدا زده نمی‌شه — رکوردهای تلاش لاگین تا ابد توی دیتابیس می‌مونن
- **راهکار:** cron job یا حذف خودکار رکوردهای قدیمی موقع هر لاگین

### 9. خطاها ذخیره نمی‌شن
- **فایل:** `app/bootstrap.php`
- **مشکل:** توی حالت production خطاها فقط نمایش داده نمی‌شن ولی جایی ذخیره نمی‌شن
- **راهکار:** افزودن logger ساده (فایلی یا دیتابیسی)

### 10. composer.lock
- **فایل:** `composer.lock`
- **مشکل:** چک بشه که توی پروژه هست وگرنه dependencyها ممکنه ورژن عوض کنن
- **راهکار:** اگه نیست، اجرای `composer update` روی سیستم شخصی و آپلود مجدد

### 11. mb_internal_encoding
- **فایل:** `app/bootstrap.php`
- **مشکل:** بعضی هاست‌ها UTF-8 به عنوان پیش‌فرض ندارن
- **راهکار:** افزودن `mb_internal_encoding('UTF-8')` به bootstrap

---

## 🟢 پیشنهادی (بهبود کیفیت)

### 12. extract() در BaseController
- **فایل:** `app/Controllers/BaseController.php` خطوط ۷ و ۱۵
- **مشکل:** `extract($data)` می‌تونه متغیرهای محلی مثل `$view` و `$data` رو overwrite کنه
- **راهکار:** استفاده از `$data['key']` مستقیم توی viewها

### 13. هدرهای امنیتی
- **مشکل:** `Content-Security-Policy`، `X-Frame-Options` و `Strict-Transport-Security` تنظیم نشده
- **راهکار:** افزودن توی `bootstrap.php`

### 14. Document Root
- **مشکل:** بعضی هاست‌های ساده اجازه تنظیم Document Root روی `public/` رو نمی‌دن
- **راهکار:** اگه هاست اجازه نداد، فایل `index.php` و `.htaccess` از `public/` به روت منتقل بشن
