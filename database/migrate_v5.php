<?php
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v5: Blog posts...\n";

Database::query("CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    author VARCHAR(100) DEFAULT NULL,
    tags VARCHAR(500) DEFAULT NULL,
    reading_time INT DEFAULT 5,
    is_published TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    published_at DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_published (is_published),
    INDEX idx_featured (is_featured),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  + blog_posts table\n";

$existing = Database::fetch("SELECT COUNT(*) as cnt FROM blog_posts");
if ($existing['cnt'] == 0) {
    $posts = [
        [
            'title' => '۱۰ ترند برتر آرایشی پاییز و زمستان ۱۴۰۳',
            'slug' => 'top-10-beauty-trends-fall-winter-1403',
            'content' => '<p>در این مقاله جامع، به بررسی جدیدترین سبک‌های آرایشی فصل سرد می‌پردازیم. از رنگ‌های ترند مو گرفته تا تکنیک‌های جدید میکاپ، همه چیز را اینجا پیدا کنید.</p><h2>ترندهای رنگ مو</h2><p>امسال رنگ‌های گرم و طبیعی مانند عسلی و شکلاتی بیشترین محبوبیت را دارند. بالیاژ با رنگ‌های کاراملی نیز همچنان ترند است.</p><h2>میکاپ پاییزی</h2><p>میکاپ مات با تمرکز روی چشم‌های دودی (Smokey Eyes) و لب‌های نود، ترند اصلی این فصل است.</p>',
            'excerpt' => 'جدیدترین ترندهای آرایشی پاییز و زمستان ۱۴۰۳ را بشناسید و استایل خود را به‌روز کنید.',
            'image' => 'cache/400x300_1560066984.svg',
            'category' => 'میکاپ و آرایش',
            'author' => 'سارا احمدی',
            'tags' => 'ترند,میکاپ,رنگ مو,پاییز,زمستان',
            'reading_time' => 8,
            'is_featured' => 1,
            'published_at' => '2024-12-06',
        ],
        [
            'title' => 'راهنمای کامل انتخاب رنگ مو متناسب با رنگ پوست',
            'slug' => 'complete-guide-hair-color-skin-tone',
            'content' => '<p>انتخاب رنگ مو مناسب می‌تواند تغییر چشمگیری در چهره شما ایجاد کند. در این مقاله نکات کلیدی برای انتخاب بهترین رنگ را بررسی می‌کنیم.</p><h2>رنگ پوست گرم</h2><p>اگر پوست شما ته‌رنگ گرم دارد، رنگ‌های طلایی، عسلی و مسی بهترین انتخاب هستند.</p><h2>رنگ پوست سرد</h2><p>برای پوست‌های سرد، رنگ‌های خاکستری، دودی و پلاتینه مناسب‌ترند.</p>',
            'excerpt' => 'با توجه به رنگ پوست خود، بهترین رنگ مو را انتخاب کنید و زیبایی خود را دوچندان کنید.',
            'image' => 'cache/400x300_1522337360788.svg',
            'category' => 'مراقبت مو',
            'author' => 'سارا احمدی',
            'tags' => 'رنگ مو,پوست,ترند,راهنما',
            'reading_time' => 5,
            'is_featured' => 0,
            'published_at' => '2024-12-01',
        ],
        [
            'title' => 'آموزش مرحله به مرحله میکاپ طبیعی روزانه',
            'slug' => 'step-by-step-natural-daily-makeup',
            'content' => '<p>یاد بگیرید چگونه با چند تکنیک ساده، آرایشی طبیعی و جذاب برای استفاده روزانه داشته باشید.</p><h2>مرحله اول: آماده‌سازی پوست</h2><p>شروع با پوست تمیز و مرطوب مهم‌ترین قدم است. از مرطوب‌کننده مناسب پوست خود استفاده کنید.</p><h2>مرحله دوم: کرم پودر سبک</h2><p>از کرم پودر سبک و BB Cream استفاده کنید تا پوستی طبیعی داشته باشید.</p>',
            'excerpt' => 'با این آموزش ساده، میکاپ طبیعی روزانه را یاد بگیرید و درخشان به نظر برسید.',
            'image' => 'cache/400x300_1512496015851.svg',
            'category' => 'میکاپ و آرایش',
            'author' => 'سارا احمدی',
            'tags' => 'میکاپ,آموزش,روزانه,طبیعی',
            'reading_time' => 8,
            'is_featured' => 0,
            'published_at' => '2024-11-25',
        ],
        [
            'title' => 'روتین شب: مراقبت‌های ضروری قبل از خواب',
            'slug' => 'night-routine-essential-skincare-before-sleep',
            'content' => '<p>پوست در شب بازسازی می‌شود. با این روتین ۵ مرحله‌ای، صبح‌ها با پوستی درخشان و جوان بیدار شوید.</p><h2>۱. پاکسازی عمقی</h2><p>قبل از خواب، آرایش خود را کامل پاک کنید و صورت را با شوینده مناسب بشویید.</p><h2>۲. تونر</h2><p>تونر به بستن منافذ و متعادل کردن pH پوست کمک می‌کند.</p>',
            'excerpt' => 'روتین ۵ مرحله‌ای مراقبت از پوست قبل از خواب برای داشتن پوستی شاداب و جوان.',
            'image' => 'cache/400x300_1570172619644.svg',
            'category' => 'پوست و زیبایی',
            'author' => 'سارا احمدی',
            'tags' => 'پوست,مراقبت,روتین شب,شب',
            'reading_time' => 6,
            'is_featured' => 0,
            'published_at' => '2024-11-18',
        ],
        [
            'title' => 'جدیدترین مدل‌های طراحی ناخن عروس ۱۴۰۳',
            'slug' => 'latest-bridal-nail-designs-1403',
            'content' => '<p>از ناخن‌های کلاسیک فرانسوی تا طرح‌های مینیمال و لوکس، انتخاب‌های متنوعی برای تکمیل استایل عروسی شما.</p><h2>طرح فرانسوی مدرن</h2><p>طرح فرانسوی با رنگ‌های جدید و خطوط رنگی، محبوب‌ترین انتخاب عروس‌هاست.</p><h2>طرح مینیمال</h2><p>طرح‌های ساده با نگین‌های ظریف و خطوط نازک طلایی برای عروس‌های مدرن مناسب است.</p>',
            'excerpt' => 'جدیدترین مدل‌های طراحی ناخن عروس را ببینید و برای روز خاص خود انتخاب کنید.',
            'image' => 'cache/400x300_1604654894610.svg',
            'category' => 'ناخن و طراحی',
            'author' => 'سارا احمدی',
            'tags' => 'ناخن,عروس,طراحی,مدل',
            'reading_time' => 4,
            'is_featured' => 0,
            'published_at' => '2024-11-10',
        ],
        [
            'title' => 'چگونه عطری ماندگار انتخاب کنیم؟',
            'slug' => 'how-to-choose-long-lasting-perfume',
            'content' => '<p>نکات طلایی برای انتخاب عطر متناسب با فصل و شخصیت شما که ماندگاری بالایی داشته باشد.</p><h2>شناخت نُت‌های بویایی</h2><p>عطرها از نت‌های بالایی، میانی و پایه تشکیل شده‌اند. نت‌های چوبی و شرقی ماندگاری بیشتری دارند.</p><h2>تست عطر روی پوست</h2><p>عطر را روی نقاط نبض‌دار مانند مچ دست و گردن تست کنید تا ماندگاری واقعی را ببینید.</p>',
            'excerpt' => 'با این نکات طلایی، عطری با ماندگاری بالا و مناسب شخصیت خود انتخاب کنید.',
            'image' => 'cache/400x300_1596462502278.svg',
            'category' => 'عطر و ادکلن',
            'author' => 'سارا احمدی',
            'tags' => 'عطر,ادکلن,ماندگاری,انتخاب',
            'reading_time' => 7,
            'is_featured' => 0,
            'published_at' => '2024-11-05',
        ],
    ];

    foreach ($posts as $post) {
        Database::insert('blog_posts', $post);
        echo "  + post: {$post['slug']}\n";
    }
    echo "  + 6 posts seeded\n";
} else {
    echo "  + posts already exist, skipping seed\n";
}

echo "\nMigration v5 complete!\n";
