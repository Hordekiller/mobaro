<p align="center">
  <img src="https://mobaro.ir/assets/images/logo.png" alt="Mobaro Logo" width="200"/>
</p>

<h1 align="center">موبارو — سامانه مدیریت آرایشگاه و سالن زیبایی</h1>

<p align="center">
  <strong>پلتفرم کامل PHP MVC برای مدیریت سالن‌های زیبایی و آرایشگاهی</strong>
  <br>
  رزرو آنلاین · فروشگاه اینترنتی · پنل کاربری · پنل ادمین · وبلاگ
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php" alt="PHP Version"/>
  <img src="https://img.shields.io/badge/license-MIT-blue?style=flat-square" alt="License"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/RTL-Persian-27ae60?style=flat-square" alt="RTL Persian"/>
  <img src="https://img.shields.io/badge/phpcs-PSR12-8892BF?style=flat-square" alt="PHPCS PSR-12"/>
</p>

---

## ویژگی‌ها

| ماژول | توضیحات |
|-------|---------|
| **📅 رزرو آنلاین** | زمان‌بندی نوبت، انتخاب سرویس و پرسنل، یادآوری خودکار |
| **🛍️ فروشگاه** | کاتالوگ محصولات، سبد خرید، درگاه پرداخت زرین‌پال، پیگیری سفارش |
| **👤 پنل کاربری** | مدیریت پروفایل، تاریخچه نوبت‌ها و سفارش‌ها، علاقه‌مندی‌ها، کیف پول |
| **⚙️ پنل ادمین** | مدیریت سرویس‌ها، محصولات، پست‌های وبلاگ، نوبت‌ها، سفارش‌ها، کاربران، گالری |
| **📝 وبلاگ** | وبلاگ فارسی با دسته‌بندی، نظرات، جستجو، اشتراک‌گذاری |
| **🖼️ گالری رسانه** | آپلود تصویر و ویدئو، استریم، پشتیبانی از چند منبع |
| **🎓 آکادمی** | دوره‌های آموزشی، ویدئوهای آموزشی، گواهینامه پایان دوره |
| **💳 پرداخت** | درگاه پرداخت زرین‌پال با تأیید و بازگشت خودکار |
| **🔒 امنیت** | CSRF token، Rate Limiting، Prepared Statements، اعتبارسنجی ورودی |
| **📱 واکنش‌گرا** | رابط کاربری Bootstrap 5.3 با پشتیبانی کامل از RTL فارسی |
| **🚀 عملکرد** | سیستم کش فایل با invalidate برچسبی، صفحه‌بندی، بهینه‌سازی assets |

## معماری

```
├── app/
│   ├── Controllers/        کنترلرهای برنامه
│   ├── Models/             مدل‌های Active Record
│   ├── Services/           منطق کسب‌وکار (ZarinPal, FileUploader)
│   ├── Middleware/         اعتبارسنجی و احراز هویت
│   ├── views/              قالب‌های PHP (داشبورد، فروشگاه، ادمین)
│   ├── helpers.php         توابع کمکی سراسری
│   ├── Auth.php            مدیریت احراز هویت
│   ├── Router.php          مسیریاب اختصاصی
│   ├── Database.php        کلاس PDO wrapper
│   ├── Cache.php           سیستم کش فایل با برچسب
│   └── Config.php          مدیریت تنظیمات
├── public/                 پوشه ریشه وب سرور
│   ├── index.php           Front Controller
│   ├── .htaccess           قوانین بازنویسی Apache
│   └── assets/             فایل‌های CSS, JS, تصاویر
├── storage/                ذخیره‌ساز فایل، کش، session
├── vendor/                 وابستگی‌های Composer
├── .env.example            الگوی فایل تنظیمات محیطی
├── composer.json           وابستگی‌های PHP
└── composer.lock           قفل نسخه وابستگی‌ها
```

## پیش‌نیازها

- PHP 8.1 یا بالاتر
- MySQL 8.0+
- Apache با `mod_rewrite` فعال
- Composer

## نصب و استقرار

### ۱. دریافت فایل انتشار

آخرین نسخه را از بخش [Releases](https://github.com/Hordekiller/mobaro/releases) گیت‌هاب دانلود کنید.

### ۲. استخراج و نصب وابستگی‌ها

```bash
tar xzf rozhingit-release-v1.0.tar.gz -d /public_html/
cd /public_html/
composer install --no-dev --optimize-autoloader
```

### ۳. تنظیمات محیطی

```bash
cp .env.example .env
```

فایل `.env` را ویرایش کنید:

| متغیر | توضیح |
|-------|-------|
| `APP_URL` | آدرس دامنه (مثلاً `https://mobaro.ir`) |
| `DB_HOST` | میزبان دیتابیس (معمولاً `localhost`) |
| `DB_NAME` | نام دیتابیس |
| `DB_USER` | نام کاربری دیتابیس |
| `DB_PASS` | رمز عبور دیتابیس |
| `ZARINPAL_MERCHANT_ID` | شناسه درگاه زرین‌پال |
| `ZARINPAL_SANDBOX` | `true` برای تست، `false` برای محیط واقعی |

### ۴. تنظیم دیتابیس

دیتابیس MySQL خود را بسازید، سپس import کنید:

```bash
mysql -u USER -p DB_NAME < database/schema.sql
```

### ۵. تنظیم مجوزها

```bash
chmod -R 755 storage/
chmod 777 storage/logs/ storage/cache/ storage/sessions/
chmod -R 755 public/uploads/
```

### ۶. اجرا

مستقیماً روی Apache قرار دهید — فایل `.htaccess` مسیرها را به `public/` هدایت می‌کند.

## توسعه

```bash
# نصب وابستگی‌های توسعه
composer install

# اجرای lint (PHP_CodeSniffer PSR-12)
composer lint

# اصلاح خودکار lint
composer lint:fix

# اجرای تست‌ها
composer test
```

## امنیت

- تمام query‌های دیتابیس با Prepared Statements
- توکن CSRF روی تمام فرم‌ها
- Rate Limiting روی نقاط لاگین و ثبت‌نام
- اعتبارسنجی و پالایش ورودی‌ها
- جلوگیری از directory listing
- جلوگیری از اجرای PHP در پوشه آپلود
- session امن با HttpOnly, SameSite=Lax
- هدرهای امنیتی (X-Frame-Options, X-Content-Type-Options, Referrer-Policy)

## مشارکت

1. پروژه را Fork کنید
2. برنچ بسازید (`git checkout -b feature/amazing`)
3. lint و تست را اجرا کنید (`composer lint && composer test`)
4. Commit کنید (`git commit -m 'Add amazing feature'`)
5. Push کنید (`git push origin feature/amazing`)
6. Pull Request باز کنید

## مجوز

این پروژه تحت مجوز MIT منتشر شده است — برای جزئیات بیشتر فایل [LICENSE](LICENSE) را ببینید.

---

<p align="center">
  <strong>موبارو</strong> — ساخته شده با ❤️ در ایران<br>
  © 2026 Mobaro. تمامی حقوق محفوظ است.
</p>
