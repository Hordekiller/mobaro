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
  <img src="https://img.shields.io/badge/version-1.1.15-27ae60?style=flat-square" alt="Version 1.1.15"/>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php" alt="PHP Version"/>
  <img src="https://img.shields.io/badge/license-MIT-blue?style=flat-square" alt="License"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/RTL-Persian-27ae60?style=flat-square" alt="RTL Persian"/>
  <img src="https://img.shields.io/badge/phpcs-PSR12-8892BF?style=flat-square" alt="PHPCS PSR-12"/>
</p>

<p align="center">
  <a href="https://github.com/Hordekiller/mobaro/actions/workflows/ci.yml"><img src="https://github.com/Hordekiller/mobaro/actions/workflows/ci.yml/badge.svg" alt="CI"/></a>
  <a href="https://github.com/Hordekiller/mobaro/actions/workflows/codeql.yml"><img src="https://github.com/Hordekiller/mobaro/actions/workflows/codeql.yml/badge.svg" alt="CodeQL"/></a>
  <a href="https://github.com/Hordekiller/mobaro/actions/workflows/lighthouse.yml"><img src="https://github.com/Hordekiller/mobaro/actions/workflows/lighthouse.yml/badge.svg" alt="Lighthouse CI"/></a>
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
│   ├── Services/           منطق کسب‌وکار (ZarinPal, SmsService, OrderEffects)
│   ├── Middleware/         اعتبارسنجی و احراز هویت
│   ├── views/              قالب‌های PHP (داشبورد، فروشگاه، ادمین)
│   ├── bootstrap.php       راه‌اندازی، هدرهای امنیتی، مدیریت خطا
│   ├── routes.php          تعریف مسیرها
│   ├── helpers.php         توابع کمکی سراسری
│   ├── Auth.php            مدیریت احراز هویت
│   ├── Router.php          مسیریاب اختصاصی
│   ├── Database.php        کلاس PDO wrapper
│   ├── Cache.php           سیستم کش فایل با برچسب
│   ├── Config.php          مدیریت تنظیمات
│   ├── SEOService.php      سئو، structured data و متا
│   └── StructuredData.php  JSON-LD (سازمان، FAQ، مقاله و …)
├── public/                 پوشه ریشه وب سرور
│   ├── index.php           Front Controller
│   ├── .htaccess           قوانین بازنویسی Apache
│   └── assets/             فایل‌های CSS, JS, تصاویر
├── storage/                ذخیره‌ساز فایل، کش، session، لاگ
├── database/               schema.sql، seed.sql و اسکریپت‌های آپدیت
├── tests/                  تست‌های PHPUnit
├── .github/workflows/      CI (PHPCS, PHPStan, PHPUnit, CodeQL, Lighthouse)
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

آخرین نسخه را از بخش [Releases](https://github.com/Hordekiller/mobaro/releases) گیت‌هاب دانلود کنید. پروژه با یک اسکریپت نصبدار ارائه نمی‌شود؛ دیتابیس را با اسکریپت‌های `database/` آماده کنید.

### ۲. استخراج و نصب وابستگی‌ها

```bash
tar xzf mobaro-v1.1.15.tar.gz -d /public_html/
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

دیتابیس MySQL خود را بسازید، سپس import کنید (`schema.sql` جداول و `seed.sql` داده‌های پیش‌فرض را می‌سازد):

```bash
mysql -u USER -p DB_NAME < database/schema.sql
mysql -u USER -p DB_NAME < database/seed.sql
```

### ۵. تنظیم مجوزها

```bash
chmod -R 755 storage/
chmod 777 storage/logs/ storage/cache/ storage/sessions/
chmod -R 755 public/uploads/
```

### ۶. اجرا

مستقیماً روی Apache قرار دهید — فایل `.htaccess` مسیرها را به `public/` هدایت می‌کند؛ یا با سرور داخلی PHP در توسعه:

```bash
php -d variables_order=EGPCS -S 127.0.0.1:8080 -t public
```

> نکته: برای کارکرد صحیح متغیرهای محیطی در سرور داخلی PHP، اجرا با `-d variables_order=EGPCS` ضروری است.

## توسعه

```bash
# نصب وابستگی‌های توسعه
composer install

# اجرای lint (PHP_CodeSniffer PSR-12)
composer lint

# اصلاح خودکار lint
composer lint:fix

# اجرای آنالیز استاتیک (PHPStan)
composer analyse

# اجرای تست‌ها
composer test
```

تست‌ها به دیتابیس وصل می‌شوند؛ محیط GitHub Actions دیتابیس MySQL را با `database/schema.sql` و `database/seed.sql` آماده می‌کند. برای اجرای محلی تست‌ها ابتدا همان importها را روی دیتابیس خودتان انجام دهید.

## راه‌اندازی درگاه پرداخت زرین‌پال

تنظیمات درگاه از پنل مدیریت (`تنظیمات ← پرداخت`) انجام می‌شود و روی تنظیمات `.env` اولویت دارد:

1. **کد مرچنت**: از پنل زرین‌پال (`تنظیمات ← درگاه پرداخت`) کد مرچنت را در پنل ادمین ثبت کنید. می‌توانید آن را در `.env` هم با `ZARINPAL_MERCHANT_ID` قرار دهید.
2. **حالت تست**: برای توسعه، گزینه «حالت تست (Sandbox)» را فعال کنید. کد مرچنت تست: `10000000-0000-0000-0000-000000000000`.
3. **آدرس بازگشت (Callback)**: آدرس نمایش‌داده‌شده در بخش «وضعیت اتصال» را در پنل زرین‌پال ثبت کنید. دامنه باید با دامنه‌ی تأییدشده در زرین‌پال یکسان باشد (`APP_URL` در `.env`).
4. **مبلغ به ریال**: درگاه زرین‌پال مبلغ را به ریال دریافت می‌کند؛ سیستم به‌صورت خودکار مبلغ تومان را ×۱۰ می‌کند.
5. **لاگ تراکنش‌ها**: تمام درخواست‌ها، تأییدها و بازگشت‌ها در جدول `payment_logs` ثبت و در پنل ادمین (تب «پرداخت») قابل مشاهده است.

> توجه: برای فعال‌سازی درگاه واقعی، کد مرچنت رسمی، گواهی SSL معتبر و تأیید مدارک توسط زرین‌پال لازم است (معمولاً ۲۴ تا ۷۲ ساعت).

## امنیت

- تمام query‌های دیتابیس با Prepared Statements
- توکن CSRF روی تمام فرم‌ها
- Rate Limiting روی نقاط لاگین و ثبت‌نام (با پاک‌سازی خودکار رکوردهای قدیمی)
- اعتبارسنجی و پالایش ورودی‌ها
- جلوگیری از directory listing و اجرای PHP در پوشه آپلود
- session امن با HttpOnly, SameSite=Lax و تشخیص صحیح HTTPS پشت proxy (`X-Forwarded-Proto`)
- هدرهای امنیتی: `Content-Security-Policy`، `X-Frame-Options: DENY`، `X-Content-Type-Options: nosniff`، `Referrer-Policy`، `Strict-Transport-Security`
- لاگ مرکزی خطاها در `storage/logs/app.log` با تبدیل خطاها به پاسخ ۵۰۰
- تشخیص HTTPS فعال/غیرفعال و ریدایرکت خودکار به `https` در محیط واقعی

## CI

پروژه در GitHub Actions به‌صورت خودکار بررسی می‌شود:

| Workflow | وظیفه |
|----------|-------|
| **CI** | PHPCS (PSR-12)، PHPStan و PHPUnit روی PHP 8.2 و 8.3 |
| **Lighthouse CI** | سنجش عملکرد و دسترس‌پذیری صفحات `/` و `/shop` + smoke test مسیرهای عمومی |
| **CodeQL** | آنالیز استاتیک JavaScript و GitHub Actions |

## مشارکت

1. پروژه را Fork کنید
2. برنچ بسازید (`git checkout -b feature/amazing`)
3. lint، آنالیز و تست را اجرا کنید (`composer lint && composer analyse && composer test`)
4. Commit کنید (`git commit -m 'Add amazing feature'`)
5. Push کنید (`git push origin feature/amazing`)
6. Pull Request باز کنید

## مجوز

این پروژه تحت مجوز MIT منتشر شده است — برای جزئیات بیشتر فایل [LICENSE](LICENSE) را ببینید.

## نسخه

نسخه جاری پروژه در فایل [VERSION](VERSION) نگهداری می‌شود. هر آپدیت در پوشه `deploy/` دارای اسکریپت و راهنمای جداگانه است.

---

<p align="center">
  <strong>موبارو</strong> — ساخته شده با ❤️ در ایران<br>
  © 2026 Mobaro. تمامی حقوق محفوظ است.
</p>
