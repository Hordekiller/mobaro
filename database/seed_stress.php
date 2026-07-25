<?php
/**
 * Database stress seeder — populates tables with realistic Persian data.
 *
 * Usage:
 *   php database/seed_stress.php
 *   php database/seed_stress.php --products=200 --blogs=100 --users=500
 *   php database/seed_stress.php --clean   (wipe seeded data first)
 */

require_once __DIR__ . '/../app/bootstrap.php';

/* ── Parse CLI args ─────────────────────────────────────── */

$args = [
    'users'    => 500,
    'products' => 200,
    'blogs'    => 100,
    'reviews'  => 500,
    'orders'   => 200,
    'comments' => 300,
    'clean'    => false,
];

foreach ($argv as $arg) {
    if ($arg === '--clean') {
        $args['clean'] = true;
    } elseif (preg_match('/^--(\w+)=(\d+)$/', $arg, $m) && array_key_exists($m[1], $args)) {
        $args[$m[1]] = (int) $m[2];
    }
}

echo "=== Mobaro Stress Seeder ===\n";
echo "Products: {$args['products']} | Blogs: {$args['blogs']} | Users: {$args['users']}\n";
echo "Reviews: {$args['reviews']} | Orders: {$args['orders']} | Comments: {$args['comments']}\n\n";

/* ── Persian data pools ─────────────────────────────────── */

$firstNames = [
    'علی', 'محمد', 'رضا', 'امیر', 'حسین', 'سعید', 'مسعود', 'فرهاد', 'بهنام', 'آرش',
    'کامران', 'داریوش', 'پوریا', 'سامان', 'مهدی', 'میلاد', 'عرفان', 'پویا', 'آرمین', 'نیما',
    'سارا', 'مریم', 'زهرا', 'فاطمه', 'نسرین', 'الناز', 'مینا', 'لیلا', 'مهسا', 'سمیرا',
    'یلدا', 'ریحانه', 'ترانه', 'نیلوفر', 'بهار', 'آزاده', 'شیرین', 'گلناز', 'پریسا', 'مهناز',
    'نگار', 'باران', 'روشا', 'دنا', 'کیانا', 'آتنا', 'سانا', 'آوین', 'هانیه', 'کیمیا',
];

$lastNames = [
    'احمدی', 'رضایی', 'محمدی', 'کریمی', 'حیدری', 'فاطمی', 'موسوی', 'جعفری', ' Abbasi',
    'شریفی', 'نوری', 'قاسمی', 'هاشمی', 'بهشتی', 'معروفی', ' طاهری', 'سادات', 'پورمحمد',
    'قادری', 'رنجبر', 'فرزین', 'خسروی', 'تقوی', 'بهرامی', 'ศรี', 'یزدانی', 'صفایی',
    'عطارد', 'گودرزی', 'پارسا', 'سلیمانی', 'بیگی', 'olt', 'نیک‌خواه', 'رضوانی',
    'خلیلی', 'فروغی', 'آصف', 'مازیار', 'شهابی', 'گلچین', 'خانی', 'آدینه', 'پردیس',
    'صوفی', 'کمانگر', 'نوبخت', 'فیروزی', 'حکیمی', 'olt',
];

$productCategories = [
    'شامپو', 'ماسک مو', 'سرم مو', 'رنگ مو', 'کراتینه', 'روغن مو',
    'واکس مو', 'ژل مو', 'اسپری مو', 'بالم لب',
];

$productBrands = [
    'لورال', 'کلیر', 'پنتن', 'داو', 'گارنیه', 'نیوآ', 'شوارتسکف', 'دیپ‌تیکس', 'سینره', 'کامان',
];

$blogCategories = ['مو', 'پوست', 'آرایش', 'سلامت', 'تغذیه', 'سبک زندگی'];

$blogTitleParts = [
    ['راهنمای جامع', 'نکات طلایی', '۱۰ روش', 'بهترین', 'رازهای', 'آموزش گام‌به‌گام', 'همه چیز درباره', 'بررسی تخصصی'],
    ['مراقبت از مو', 'رنگ کردن مو', 'صاف کردن مو', 'فر کردن مو', 'ترمیم مو', 'تقویت مو', 'پوست صورت', 'آرایش صورت', 'سبک زندگی سالم', 'تغذیه مناسب'],
];

$reviewTexts = [
    'خیلی عالی بود. مدتیه استفاده می‌کنم و نتیجه عالیه.',
    'محصول خوبیه ولی قیمتش یکم بالاست.',
    'برای اولین بار خریدم و راضی‌ام. حتماً دوباره می‌خرم.',
    'کیفیتش عالیه. به همه پیشنهاد می‌کنم.',
    'متأسفانه انتظارم رو برآورده نکرد.',
    'عالی بود ولی بسته‌بندیش می‌تونست بهتر باشه.',
    'بهترین محصولی بوده که تا حالا استفاده کردم.',
    'خیلی سریع تحویل داده شد. ممنونم.',
    'محصول اصل بود و کیفیتش عالی.',
    'نتیجه خوبی گرفتم ولی زمان می‌بره.',
    'بوی خیلی خوبی داره. ممنون از فروشگاه.',
    'قیمتش نسبت به کیفیتش مناسبه.',
    'دو ماهه استفاده می‌کنم. موهام خیلی بهتر شدن.',
    'خوبه ولی انتظار بیشتری داشتم.',
    'عالیییی! حتماً دوباره سفارش میدم.',
    'بسته‌بندیش عالی بود و سالم رسید.',
    'محصول رو برای هدیه خریدم. خیلی شیکه.',
    'کیفیتش نسبت به قیمتش عالیه.',
    'برای موهای من خیلی مناسب بود.',
    'فعلاً که خوبه. بعد از یک ماه نتیجه رو اعلام می‌کنم.',
];

$commentTexts = [
    'ممنون از مقاله خوبتون. خیلی مفید بود.',
    'لطفاً مقالات بیشتری در این مورد بنویسید.',
    'عالی بود. من هم تجربه مشابهی داشتم.',
    'آیا این روش برای موهای رنگ شده هم مناسبه؟',
    'خیلی وقته دنبال این اطلاعات بودم. ممنون.',
    'ممنون از توضیحاتتون. لطفاً ادامه بدید.',
    'متأسفانه برای من جواب نداد.',
    'خیلی خوب بود ولی عکس هم می‌ذاشتید بهتر می‌شد.',
    'من قبلاً امتحان کردم واقعاً جواب میده.',
    'ممنون از زحماتتون. سایتتون عالیه.',
];

$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$appointmentStatuses = ['confirmed', 'pending', 'done', 'cancelled'];

/* ── Helpers ────────────────────────────────────────────── */

function pick(array $arr): string
{
    return $arr[array_rand($arr)];
}

function randInt(int $min, int $max): int
{
    return random_int($min, $max);
}

function randomDate(int $daysBack = 90): string
{
    $ts = time() - random_int(0, $daysBack) * 86400;
    return date('Y-m-d H:i:s', $ts);
}

function randomDateOnly(int $daysBack = 90): string
{
    $ts = time() - random_int(0, $daysBack) * 86400;
    return date('Y-m-d', $ts);
}

function makePhone(int $n): string
{
    return '0912' . str_pad((string) $n, 8, '0', STR_PAD_LEFT);
}

function slugifyLocal(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/* ── Clean (optional) ──────────────────────────────────── */

if ($args['clean']) {
    echo "Cleaning existing data...\n";
    Database::query("SET FOREIGN_KEY_CHECKS = 0");
    foreach (['order_items', 'orders', 'reviews', 'blog_comments', 'product_images', 'wishlist', 'transactions', 'course_enrollments', 'course_lessons_completed', 'newsletter', 'contact_messages', 'testimonials', 'favorite_models', 'appointments'] as $t) {
        Database::query("TRUNCATE TABLE {$t}");
    }
    Database::query("DELETE FROM products WHERE id > 6");
    Database::query("DELETE FROM blog_posts WHERE id > 6");
    Database::query("DELETE FROM users WHERE role != 'admin'");
    Database::query("SET FOREIGN_KEY_CHECKS = 1");
    Cache::flush();
    echo "Done.\n\n";
}

$totalStart = microtime(true);

/* ── 1. Product Categories ─────────────────────────────── */

$start = microtime(true);
Database::query("DELETE FROM product_categories");
foreach ($productCategories as $cat) {
    Database::insert('product_categories', ['name' => $cat, 'is_active' => 1]);
}
foreach ($productBrands as $brand) {
    Database::insert('product_brands', ['name' => $brand, 'is_active' => 1]);
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[categories+brands] " . count($productCategories) . '+' . count($productBrands) . " rows ({$elapsed}ms)\n";

/* ── 2. Users ──────────────────────────────────────────── */

$start = microtime(true);
$passHash = password_hash('test123', PASSWORD_ARGON2ID);
$userIds = [];
$phoneOffset = 10000;
$userCount = $args['users'];
$batch = [];

for ($i = 0; $i < $userCount; $i++) {
    $phone = makePhone($phoneOffset + $i);
    $batch[] = [
        'name'      => pick($firstNames),
        'family'    => pick($lastNames),
        'phone'     => $phone,
        'password'  => $passHash,
        'email'     => 'user' . ($phoneOffset + $i) . '@test.com',
        'role'      => 'user',
        'level'     => pick(['bronze', 'silver', 'gold', 'platinum']),
        'points'    => randInt(0, 500),
        'wallet'    => randInt(0, 500000),
        'is_active' => 1,
    ];

    if (count($batch) >= 200) {
        foreach ($batch as $row) {
            $id = Database::insert('users', $row);
            $userIds[] = $id;
        }
        $batch = [];
    }
}
foreach ($batch as $row) {
    $id = Database::insert('users', $row);
    $userIds[] = $id;
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[users] {$userCount} rows ({$elapsed}ms)\n";

/* ── 3. Products ───────────────────────────────────────── */

$start = microtime(true);
$existingProductCount = (int) Database::fetch("SELECT COUNT(*) as cnt FROM products")['cnt'];
$productIds = [];
$productCount = $args['products'];
$batch = [];
$imageNames = [
    'shampoo_1.jpg', 'shampoo_2.jpg', 'mask_1.jpg', 'serum_1.jpg', 'serum_2.jpg',
    'color_1.jpg', 'keratin_1.jpg', 'oil_1.jpg', 'wax_1.jpg', 'gel_1.jpg',
    'spray_1.jpg', 'balm_1.jpg', 'product_1.jpg', 'product_2.jpg', 'product_3.jpg',
];

$productNames = [
    'شامپو تقویت‌کننده', 'ماسک بازسازی‌کننده', 'سرم ضد ریزش', 'رنگ موی دائمی',
    'کراتینه برزیلی', 'روغن آرگان خالص', 'واکس حالت‌دهنده', 'ژل حالت‌دهنده قوی',
    'اسپری دفاع حرارتی', 'بالم لب آلئوئه', 'شامپو ضد شوره', 'ماسک آبرسان',
    'سرم صاف‌کننده', 'رنگ موی موقت', 'کراتینه احیاکننده', 'روغن کرچک',
    'واکس مات', 'ژل ضد ریزش', 'اسپری براق‌کننده', 'شامپو حجم‌دهنده',
];

for ($i = 0; $i < $productCount; $i++) {
    $cat = pick($productCategories);
    $brand = pick($productBrands);
    $nameSuffix = pick(['مدل جدید', 'ویژه', 'حرفه‌ای', 'اورجینال', 'پرفروش', '']);
    $fullName = pick($productNames) . ' ' . $brand . ' ' . $nameSuffix;
    $price = randInt(5, 500) * 10000;
    $hasDiscount = random_int(0, 3) === 0;
    $oldPrice = $hasDiscount ? (int) ($price * (1 + random_int(10, 50) / 100)) : null;

    $batch[] = [
        'name'        => $fullName,
        'category'    => $cat,
        'price'       => $price,
        'old_price'   => $oldPrice,
        'image'       => pick($imageNames),
        'description' => "توضیحات {$fullName}. این محصول با کیفیت بالا و قیمت مناسب برای مراقبت از مو و پوست طراحی شده است.",
        'stock'       => randInt(0, 200),
        'brand'       => $brand,
        'is_new'      => random_int(0, 4) === 0 ? 1 : 0,
        'is_sale'     => $hasDiscount ? 1 : 0,
        'rating'      => round(random_int(25, 50) / 10, 1),
        'reviews'     => randInt(0, 100),
        'is_active'   => 1,
    ];

    if (count($batch) >= 200) {
        foreach ($batch as $row) {
            $id = Database::insert('products', $row);
            $productIds[] = $id;
        }
        $batch = [];
    }
}
foreach ($batch as $row) {
    $id = Database::insert('products', $row);
    $productIds[] = $id;
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[products] {$productCount} rows ({$elapsed}ms)\n";

/* ── 4. Product Images ─────────────────────────────────── */

$start = microtime(true);
$galleryImages = ['gallery_1.jpg', 'gallery_2.jpg', 'gallery_3.jpg', 'gallery_4.jpg', 'gallery_5.jpg'];
$imgCount = 0;
foreach ($productIds as $pid) {
    $numImages = random_int(1, 3);
    for ($s = 0; $s < $numImages; $s++) {
        Database::insert('product_images', [
            'product_id' => $pid,
            'image'      => pick($galleryImages),
            'sort_order' => $s,
        ]);
        $imgCount++;
    }
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[product_images] {$imgCount} rows ({$elapsed}ms)\n";

/* ── 5. Blog Posts ─────────────────────────────────────── */

$start = microtime(true);
$blogIds = [];
$blogCount = $args['blogs'];
$usedSlugs = [];

for ($i = 0; $i < $blogCount; $i++) {
    $cat = pick($blogCategories);
    $title = pick($blogTitleParts[0]) . ' ' . pick($blogTitleParts[1]);

    $baseSlug = slugifyLocal($title);
    $slug = $baseSlug;
    $counter = 1;
    while (in_array($slug, $usedSlugs)) {
        $slug = $baseSlug . '-' . $counter++;
    }
    $usedSlugs[] = $slug;

    $paragraphs = random_int(3, 6);
    $contentParts = [];
    for ($p = 0; $p < $paragraphs; $p++) {
        $contentParts[] = '<p>' . pick($reviewTexts) . ' ' . pick($commentTexts) . ' ' . pick($reviewTexts) . '</p>';
    }
    $content = implode("\n", $contentParts);
    $excerpt = pick($reviewTexts);

    $id = Database::insert('blog_posts', [
        'title'         => $title,
        'slug'          => $slug,
        'content'       => $content,
        'excerpt'       => $excerpt,
        'image'         => pick($imageNames),
        'category'      => $cat,
        'author'        => pick($firstNames) . ' ' . pick($lastNames),
        'tags'          => implode(',', array_slice(array_unique([pick($blogCategories), pick($blogCategories), pick($blogCategories)]), 0, 3)),
        'reading_time'  => random_int(3, 15),
        'is_published'  => 1,
        'is_featured'   => random_int(0, 8) === 0 ? 1 : 0,
        'views'         => randInt(0, 5000),
        'published_at'  => randomDateOnly(90),
    ]);
    $blogIds[] = $id;
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[blog_posts] {$blogCount} rows ({$elapsed}ms)\n";

/* ── 6. Reviews ────────────────────────────────────────── */

$start = microtime(true);
$reviewCount = $args['reviews'];
$batch = [];
$rUserIds = array_slice($userIds, 0, min(100, count($userIds)));
$usedReviewPairs = [];

for ($i = 0; $i < $reviewCount; $i++) {
    $pid = pick($productIds);
    $uid = pick($rUserIds);
    $pairKey = "{$pid}_{$uid}";
    if (isset($usedReviewPairs[$pairKey])) {
        continue;
    }
    $usedReviewPairs[$pairKey] = true;
    $userName = pick($firstNames) . ' ' . pick($lastNames);

    $batch[] = [
        'product_id' => $pid,
        'user_id'    => $uid,
        'user_name'  => $userName,
        'rating'     => random_int(10, 50) / 10,
        'text'       => pick($reviewTexts),
        'created_at' => randomDate(90),
    ];

    if (count($batch) >= 200) {
        foreach ($batch as $row) {
            Database::insert('reviews', $row);
        }
        $batch = [];
    }
}
foreach ($batch as $row) {
    Database::insert('reviews', $row);
}
$inserted = min($reviewCount, count($usedReviewPairs));
$elapsed = round((microtime(true) - $start) * 1000);
echo "[reviews] {$inserted} rows ({$elapsed}ms)\n";

/* ── 7. Orders + Order Items ───────────────────────────── */

$start = microtime(true);
$orderCount = $args['orders'];
$trackingPrefix = 'MB-' . date('Ymd') . '-';
$batch = [];

for ($i = 0; $i < $orderCount; $i++) {
    $uid = pick($userIds);
    $total = randInt(10, 2000) * 1000;
    $status = pick($statuses);
    $tracking = $trackingPrefix . str_pad((string) random_int(1000, 99999), 5, '0', STR_PAD_LEFT);

    $id = Database::insert('orders', [
        'user_id'          => $uid,
        'total'            => $total,
        'discount'         => 0,
        'status'           => $status,
        'tracking_code'    => $tracking,
        'payment_status'   => $status === 'cancelled' ? 'failed' : 'paid',
        'payment_method'   => pick(['wallet', 'zarinpal']),
        'created_at'       => randomDate(90),
    ]);

    $itemCount = random_int(1, 4);
    for ($j = 0; $j < $itemCount; $j++) {
        $pid = pick($productIds);
        Database::insert('order_items', [
            'order_id'     => $id,
            'product_id'   => $pid,
            'product_name' => pick($productNames),
            'price'        => randInt(5, 500) * 1000,
            'quantity'     => random_int(1, 3),
        ]);
    }
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[orders+items] {$orderCount} orders ({$elapsed}ms)\n";

/* ── 8. Blog Comments ──────────────────────────────────── */

$start = microtime(true);
$commentCount = $args['comments'];
$batch = [];

for ($i = 0; $i < $commentCount; $i++) {
    $pid = pick($blogIds);
    $uid = random_int(0, 3) === 0 ? null : pick($userIds);
    $batch[] = [
        'post_id'     => $pid,
        'user_id'     => $uid,
        'name'        => pick($firstNames) . ' ' . pick($lastNames),
        'text'        => pick($commentTexts),
        'is_approved' => random_int(0, 3) === 0 ? 0 : 1,
        'likes'       => randInt(0, 50),
        'created_at'  => randomDate(90),
    ];

    if (count($batch) >= 200) {
        foreach ($batch as $row) {
            Database::insert('blog_comments', $row);
        }
        $batch = [];
    }
}
foreach ($batch as $row) {
    Database::insert('blog_comments', $row);
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[blog_comments] {$commentCount} rows ({$elapsed}ms)\n";

/* ── 9. Testimonials ───────────────────────────────────── */

$start = microtime(true);
Database::query("DELETE FROM testimonials");
for ($i = 0; $i < 50; $i++) {
    Database::insert('testimonials', [
        'name'      => pick($firstNames) . ' ' . pick($lastNames),
        'role'      => pick(['مشتری', 'کاربر سایت', 'مشتری دائمی', 'عضو ویژه']),
        'text'      => pick($reviewTexts),
        'rating'    => random_int(40, 50) / 10,
        'is_active' => 1,
    ]);
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[testimonials] 50 rows ({$elapsed}ms)\n";

/* ── 10. Appointments ──────────────────────────────────── */

$start = microtime(true);
$serviceIds = array_column(Database::fetchAll("SELECT id FROM services"), 'id');
$artistIds = array_column(Database::fetchAll("SELECT id FROM artists"), 'id');
$hairLengthIds = [1, 2, 3, 4];
$apptCount = 0;

for ($i = 0; $i < 200; $i++) {
    $uid = pick($userIds);
    $sid = !empty($serviceIds) ? pick($serviceIds) : null;
    $aid = !empty($artistIds) ? pick($artistIds) : null;
    $date = randomDateOnly(60);
    $hour = random_int(9, 19);
    $minute = pick(['00', '15', '30', '45']);

    Database::insert('appointments', [
        'user_id'           => $uid,
        'service_id'        => $sid,
        'artist_id'         => $aid,
        'hair_length_id'    => pick($hairLengthIds),
        'appointment_date'  => $date,
        'appointment_time'  => "{$hour}:{$minute}",
        'price'             => randInt(2, 50) * 10000,
        'status'            => pick($appointmentStatuses),
        'created_at'        => randomDate(60),
    ]);
    $apptCount++;
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[appointments] {$apptCount} rows ({$elapsed}ms)\n";

/* ── 11. Newsletter ────────────────────────────────────── */

$start = microtime(true);
Database::query("DELETE FROM newsletter");
for ($i = 0; $i < 100; $i++) {
    Database::insert('newsletter', [
        'email'     => 'newsletter' . ($i + 1) . '@test.com',
        'is_active' => 1,
    ]);
}
$elapsed = round((microtime(true) - $start) * 1000);
echo "[newsletter] 100 rows ({$elapsed}ms)\n";

/* ── Done ──────────────────────────────────────────────── */

Cache::flush();

$totalElapsed = round((microtime(true) - $totalStart) * 1000);
echo "\n=== Seeding complete in {$totalElapsed}ms ===\n";

/* ── Summary ───────────────────────────────────────────── */

$tables = [
    'users', 'products', 'product_images', 'blog_posts', 'reviews',
    'orders', 'order_items', 'blog_comments', 'testimonials',
    'appointments', 'newsletter', 'product_categories', 'product_brands',
];

echo "\nFinal counts:\n";
foreach ($tables as $t) {
    $cnt = Database::fetch("SELECT COUNT(*) as cnt FROM {$t}")['cnt'];
    echo "  {$t}: {$cnt}\n";
}
echo "\nCache flushed.\n";
