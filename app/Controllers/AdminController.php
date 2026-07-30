<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Captcha;
use App\Settings;
use App\RateLimiter;
use App\Database;
use App\Cache;
use App\Config;
use App\FileUploader;
use App\Services\SmsService;
use Throwable;
use finfo;

class AdminController extends BaseController
{
    private const PATH_ADMIN = '/admin';
    private const PATH_ADMIN_LOGIN = '/admin/login';
    private const PATH_HAIR_PRICES = '/admin/hair-prices';
    private const PATH_SETTINGS = '/admin/settings';
    private const VIEW_ADMIN = 'admin/index';
    private const WHERE_ID = 'id = :id';
    private const WHERE_ID_PARAM = 'id = ?';
    private const PLACEHOLDER_PAIR = '(?, ?)';
    private const LABEL_IMAGE = 'تصویر';
    private const LABEL_TITLE = 'عنوان';
    private const LABEL_DESCRIPTION = 'توضیحات';
    private const LABEL_RATING = 'امتیاز';
    private const LABEL_USER = 'کاربر';
    private const LABEL_DATE = 'تاریخ';
    private const LABEL_EMAIL = 'ایمیل';
    private const LABEL_COMMENT = 'متن نظر';
    private const MIME_MOV = 'video/quicktime';
    private const MIME_MP4 = 'video/mp4';
    private const MIME_WEBM = 'video/webm';
    private const MIME_OGG = 'video/ogg';
    private const EXT_WEBM = '.webm';
    private const PATH_GALLERY = '/admin/gallery';
    private const PATH_BLOG_CATEGORIES = '/admin/blog-categories';
    private const PATH_SMS = '/admin/sms';
    private const PATH_SMS_TAB_SEND = '/admin/sms?tab=send';
    private const PATH_SMS_TAB_TEMPLATES = '/admin/sms?tab=templates';
    private const PATH_SEO = '/admin/seo';

    private function requireAdmin(): void
    {
        Auth::requireAdmin();
    }

    protected function view(string $view, array $data = []): void
    {
        $data['hideFooter'] = true;
        parent::view($view, $data);
    }

    public function loginForm(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            redirect(self::PATH_ADMIN);
        }
        $captchaEnabled = Captcha::isEnabled('admin');
        if ($captchaEnabled) {
            $_SESSION['captcha_question'] = Captcha::store();
        }
        $captchaQuestion = $_SESSION['captcha_question'] ?? '';
        $settings = Settings::all();
        $this->viewRaw('admin/login', compact('captchaQuestion', 'captchaEnabled', 'settings'));
    }

    public function doLogin(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            redirect(self::PATH_ADMIN);
        }
        $this->verifyCsrf();

        if (Captcha::isEnabled('admin') && !Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_ADMIN_LOGIN, ['admin' => 'کد امنیتی اشتباه است.']);
            return;
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_ADMIN_LOGIN, ['admin' => 'نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        RateLimiter::cleanup();
        if (RateLimiter::isLocked('admin_' . $username)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_ADMIN_LOGIN, ['rate_limit' => 'تعداد تلاش‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE name = ? AND role = 'admin'", [$username]);

        if (!$user || !Auth::verify($password, $user['password'])) {
            RateLimiter::recordAttempt('admin_' . $username, false);
            $_SESSION['captcha_question'] = Captcha::store();
            $remaining = RateLimiter::remainingAttempts('admin_' . $username);
            $msg = 'نام کاربری یا رمز عبور اشتباه است.';
            if ($remaining <= 2 && $remaining > 0) {
                $msg .= " ({$remaining} تلاش باقی‌مانده)";
            }
            $this->redirectWithErrors(self::PATH_ADMIN_LOGIN, ['admin' => $msg]);
            return;
        }

        RateLimiter::recordAttempt('admin_' . $username, true);
        Auth::login($user['id'], $user);
        flash('success', 'خوش آمدید ' . e($user['name']));
        redirect(self::PATH_ADMIN);
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $stats = Cache::remember('admin_stats', Config::get('cache.ttl.admin', 300), function () {
            return [
                'users' => Database::fetch("SELECT COUNT(*) as cnt FROM users")['cnt'],
                'appointments' => Database::fetch("SELECT COUNT(*) as cnt FROM appointments")['cnt'],
                'orders' => Database::fetch("SELECT COUNT(*) as cnt FROM orders")['cnt'],
                'revenue' => Database::fetch("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status != 'cancelled'")['total'],
            ];
        }, 'admin');

        $recentAppointments = Cache::remember('admin_recent_appointments', Config::get('cache.ttl.admin', 300), function () {
            return Database::fetchAll(
                "SELECT a.*, u.name as user_name, u.family as user_family, s.title as service_title
                 FROM appointments a
                 JOIN users u ON a.user_id = u.id
                 LEFT JOIN services s ON a.service_id = s.id
                 ORDER BY a.created_at DESC LIMIT 10"
            );
        }, 'admin');

        $recentOrders = Cache::remember('admin_recent_orders', Config::get('cache.ttl.admin', 300), function () {
            return Database::fetchAll(
                "SELECT o.*, u.name as user_name, u.family as user_family
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 ORDER BY o.created_at DESC LIMIT 5"
            );
        }, 'admin');

        $this->view(self::VIEW_ADMIN, compact('stats', 'recentAppointments', 'recentOrders') + ['section' => 'dashboard']);
    }

    public function section(string $section): void
    {
        $this->requireAdmin();
        $validSections = ['services', 'artists', 'appointments', 'products', 'users', 'courses', 'enrollments', 'testimonials', 'transactions', 'settings', 'captcha', 'hair-models', 'tutorials', 'orders', 'newsletter', 'coupons', 'contact-messages', 'blog', 'reviews', 'blog-comments', 'product-categories', 'product-brands', 'gallery', 'hair-prices', 'sms', 'blog-categories', 'seo'];

        if (!in_array($section, $validSections)) {
            redirect(self::PATH_ADMIN);
        }

        $data = ['section' => $section];
        $data['sections'] = [
            'services' => ['fa-scissors', 'خدمات'],
            'artists' => ['fa-user-tie', 'آرایشگران'],
            'appointments' => ['fa-calendar-check', 'نوبت\u200cها'],
            'products' => ['fa-box', 'محصولات'],
            'users' => ['fa-users', 'کاربران'],
            'courses' => ['fa-graduation-cap', 'دوره\u200cها'],
            'enrollments' => ['fa-user-graduate', 'ثبت\u200cنام دوره\u200cها'],
            'testimonials' => ['fa-comment', 'نظرات'],
            'transactions' => ['fa-coins', 'تراکنش\u200cها'],
            'settings' => ['fa-gear', 'تنظیمات'],
            'captcha' => ['fa-shield-halved', 'کپچا'],
            'hair-models' => ['fa-image', 'مدل مو'],
            'tutorials' => ['fa-video', 'آموزش\u200cها'],
            'orders' => ['fa-truck', 'سفارش\u200cها'],
            'newsletter' => ['fa-envelope', 'خبرنامه'],
            'coupons' => ['fa-ticket', 'تخفیف\u200cها'],
            'contact-messages' => ['fa-envelope-open', 'پیام\u200cها'],
            'blog' => ['fa-pen', 'وبلاگ'],
            'reviews' => ['fa-star', 'نظرات محصولات'],
            'blog-comments' => ['fa-comments', 'نظرات وبلاگ'],
            'blog-categories' => ['fa-folder', 'دسته‌بندی وبلاگ'],
            'product-categories' => ['fa-layer-group', 'دسته\u200cبندی محصولات'],
            'product-brands' => ['fa-tag', 'برندها'],
            'gallery' => ['fa-photo-film', 'گالری رسانه'],
            'hair-prices' => ['fa-money-bill-wave', 'قیمتهای قد مو'],
            'sms' => ['fa-comment-sms', 'مدیریت پیامک'],
            'seo' => ['fa-globe', 'مدیریت سئو'],
        ];

        $method = 'section' . str_replace('-', '', ucwords($section, '-'));

        if (method_exists($this, $method)) {
            $this->$method($data);
        } else {
            $this->genericSection($section, $data);
        }
    }

    private function genericSection(string $section, array $data): void
    {
        $tableMap = [
            'services' => 'services',
            'artists' => 'artists',
            'appointments' => 'appointments',
            'products' => 'products',
            'users' => 'users',
            'courses' => 'courses',
            'enrollments' => 'course_enrollments',
            'testimonials' => 'testimonials',
            'transactions' => 'transactions',
            'hair-models' => 'hair_models',
            'tutorials' => 'tutorials',
            'orders' => 'orders',
            'newsletter' => 'newsletter',
            'product-categories' => 'product_categories',
            'product-brands' => 'product_brands',
            'hair-prices' => 'hair_prices',
            'blog-categories' => 'blog_categories',
        ];

        $data['columns'] = $this->getColumns($section);
        array_walk($data['columns'], fn(&$col) => $col['required'] = $col['required'] ?? false);

        if ($section === 'products') {
            $this->enrichProductColumns($data['columns']);
        }

        if ($section === 'blog') {
            $this->enrichBlogColumns($data['columns']);
        }

        $table = $tableMap[$section] ?? null;
        if ($table) {
            $this->loadSectionData($table, $section, $data);
        }

        $this->loadSectionExtras($section, $data);
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function enrichProductColumns(array &$columns): void
    {
        $catOptions = Database::fetchAll("SELECT name FROM product_categories WHERE is_active = 1 ORDER BY name");
        $brandOptions = Database::fetchAll("SELECT name FROM product_brands WHERE is_active = 1 ORDER BY name");
        foreach ($columns as &$col) {
            if ($col['key'] === 'category') {
                $col['type'] = 'select';
                $col['options'] = array_column($catOptions, 'name');
            }
            if ($col['key'] === 'brand') {
                $col['type'] = 'select';
                $col['options'] = array_column($brandOptions, 'name');
            }
        }
        unset($col);
    }

    private function enrichBlogColumns(array &$columns): void
    {
        $catOptions = Database::fetchAll("SELECT name FROM blog_categories WHERE is_active = 1 ORDER BY sort_order, name");
        $adminUsers = Database::fetchAll("SELECT name, family FROM users WHERE role = 'admin' ORDER BY name");
        foreach ($columns as &$col) {
            if ($col['key'] === 'category') {
                $col['type'] = 'select';
                $col['options'] = array_column($catOptions, 'name');
            }
            if ($col['key'] === 'author') {
                $col['type'] = 'select';
                $authorOptions = [];
                foreach ($adminUsers as $u) {
                    $authorOptions[] = trim($u['name'] . ' ' . $u['family']);
                }
                $col['options'] = $authorOptions;
            }
        }
        unset($col);
    }

    private function loadSectionData(string $table, string $section, array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchable = ['name', 'title', 'email', 'phone', 'category', 'code', 'family', 'teacher', 'subject', 'message'];
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $cols = $this->getColumns($section);
            $textCols = array_filter($cols, fn($c) => in_array($c['type'], ['text', 'textarea', 'price']) && in_array($c['key'], $searchable));
            if (!empty($textCols)) {
                $conditions = [];
                foreach ($textCols as $tc) {
                    $conditions[] = "{$tc['key']} LIKE ?";
                    $searchParams[] = likePattern($search);
                }
                $searchWhere = ' WHERE ' . implode(' OR ', $conditions);
            }
        }
        $paged = $this->paginate(
            "SELECT * FROM {$table}{$searchWhere} ORDER BY id DESC",
            "SELECT COUNT(*) as cnt FROM {$table}{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
    }

    private function loadSectionExtras(string $section, array &$data): void
    {
        match ($section) {
            'products' => $this->loadProductsExtra($data),
            'artists' => $this->loadArtistsExtra($data),
            'services' => $this->loadServicesExtra($data),
            default => null,
        };
    }

    private function loadProductsExtra(array &$data): void
    {
        $productIds = array_column($data['items'] ?? [], 'id');
        if (empty($productIds)) {
            $data['productGalleryJson'] = '[]';
            return;
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $galleryRows = Database::fetchAll(
            "SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order, id",
            $productIds
        );
        $galleryData = [];
        foreach ($galleryRows as $gr) {
            $galleryData[$gr['product_id']][] = $gr;
        }
        $data['productGalleryJson'] = json_encode($galleryData, JSON_HEX_TAG);
    }

    private function loadArtistsExtra(array &$data): void
    {
        $data['allServices'] = Database::fetchAll("SELECT id, title FROM services ORDER BY id");
        $assignments = Database::fetchAll("SELECT artist_id, service_id FROM artist_services");
        $artistServices = [];
        foreach ($assignments as $as) {
            $artistServices[$as['artist_id']][] = (int) $as['service_id'];
        }
        $data['artistServicesJson'] = json_encode($artistServices, JSON_HEX_TAG);
    }

    private function loadServicesExtra(array &$data): void
    {
        $data['allHairLengths'] = Database::fetchAll("SELECT id, title, min_cm, max_cm FROM hair_lengths WHERE is_active = 1 ORDER BY sort_order");
        $allServiceIds = array_column($data['items'] ?? [], 'id');
        if (empty($allServiceIds)) {
            $data['serviceHairPricesJson'] = '[]';
            return;
        }
        $placeholders = implode(',', array_fill(0, count($allServiceIds), '?'));
        $priceRows = Database::fetchAll(
            "SELECT * FROM service_hair_prices WHERE service_id IN ($placeholders) ORDER BY hair_length_id",
            $allServiceIds
        );
        $serviceHairPrices = [];
        foreach ($priceRows as $pr) {
            $serviceHairPrices[$pr['service_id']][] = $pr;
        }
        $data['serviceHairPricesJson'] = json_encode($serviceHairPrices, JSON_HEX_TAG);
    }

    private function getColumns(string $section): array
    {
        $all = [
            'services' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'title', 'label' => self::LABEL_TITLE, 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'description', 'label' => self::LABEL_DESCRIPTION, 'type' => 'textarea'],
                ['key' => 'rating', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'artists' => [
                ['key' => 'avatar', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'specialty', 'label' => 'تخصص', 'type' => 'text'],
                ['key' => 'bio', 'label' => 'بیوگرافی', 'type' => 'textarea'],
                ['key' => 'instagram', 'label' => 'اینستاگرام', 'type' => 'text'],
                ['key' => 'working_hours', 'label' => 'ساعت کاری', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'appointments' => [
                ['key' => 'user_name', 'label' => self::LABEL_USER, 'type' => 'text'],
                ['key' => 'service_title', 'label' => 'خدمت', 'type' => 'text'],
                ['key' => 'artist_name', 'label' => 'آرایشگر', 'type' => 'text'],
                ['key' => 'appointment_date', 'label' => self::LABEL_DATE, 'type' => 'text'],
                ['key' => 'appointment_time', 'label' => 'ساعت', 'type' => 'text'],
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status', 'options' => ['pending', 'confirmed', 'done', 'cancelled']],
                ['key' => 'notes', 'label' => 'یادداشت', 'type' => 'textarea'],
            ],
            'products' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'old_price', 'label' => 'قیمت قبل', 'type' => 'price'],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'brand', 'label' => 'برند', 'type' => 'text'],
                ['key' => 'stock', 'label' => 'موجودی', 'type' => 'text'],
                ['key' => 'description', 'label' => self::LABEL_DESCRIPTION, 'type' => 'textarea'],
                ['key' => 'rating', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'is_new', 'label' => 'جدید', 'type' => 'boolean'],
                ['key' => 'is_sale', 'label' => 'تخفیف خورده', 'type' => 'boolean'],
                ['key' => 'video_type', 'label' => 'نوع ویدیو', 'type' => 'select', 'options' => ['upload', 'youtube', 'aparat']],
                ['key' => 'video_url', 'label' => 'ویدیو (آپلود یا لینک)', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'users' => [
                ['key' => 'avatar', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'family', 'label' => 'نام خانوادگی', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'email', 'label' => self::LABEL_EMAIL, 'type' => 'text'],
                ['key' => 'role', 'label' => 'نقش', 'type' => 'select', 'options' => ['user', 'admin']],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'text'],
                ['key' => 'points', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'wallet', 'label' => 'کیف پول', 'type' => 'price'],
            ],
            'courses' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'title', 'label' => self::LABEL_TITLE, 'type' => 'text', 'required' => true],
                ['key' => 'slug', 'label' => 'slug', 'type' => 'text'],
                ['key' => 'teacher', 'label' => 'مدرس', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'select', 'options' => ['online', 'offline']],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price'],
                ['key' => 'old_price', 'label' => 'قیمت قبل', 'type' => 'price'],
                ['key' => 'rating', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'students', 'label' => 'دانشجو', 'type' => 'text'],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'select', 'options' => ['مبتدی', 'متوسط', 'پیشرفته', 'همه سطوح']],
                ['key' => 'is_free', 'label' => 'رایگان', 'type' => 'boolean'],
                ['key' => 'description', 'label' => self::LABEL_DESCRIPTION, 'type' => 'textarea'],
                ['key' => 'curriculum', 'label' => 'برنامه درسی (JSON)', 'type' => 'textarea'],
                ['key' => 'audience', 'label' => 'مخاطبان (JSON)', 'type' => 'textarea'],
                ['key' => 'faqs', 'label' => 'سؤالات متداول (JSON)', 'type' => 'textarea'],
                ['key' => 'reviews', 'label' => 'نظرات (JSON)', 'type' => 'textarea'],
                ['key' => 'video_type', 'label' => 'نوع ویدیو', 'type' => 'select', 'options' => ['upload', 'youtube', 'aparat']],
                ['key' => 'video_url', 'label' => 'ویدیو (آپلود یا لینک)', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'enrollments' => [
                ['key' => 'user_name', 'label' => self::LABEL_USER, 'type' => 'text'],
                ['key' => 'course_title', 'label' => 'دوره', 'type' => 'text'],
                ['key' => 'progress', 'label' => 'پیشرفت %', 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'تاریخ ثبت‌نام', 'type' => 'text'],
            ],
            'testimonials' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'role', 'label' => self::LABEL_TITLE, 'type' => 'text'],
                ['key' => 'text', 'label' => self::LABEL_COMMENT, 'type' => 'textarea', 'required' => true],
                ['key' => 'avatar', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'rating', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'transactions' => [
                ['key' => 'user_id', 'label' => 'شناسه کاربر', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'status'],
                ['key' => 'amount', 'label' => 'مبلغ', 'type' => 'price'],
                ['key' => 'description', 'label' => self::LABEL_DESCRIPTION, 'type' => 'textarea'],
                ['key' => 'created_at', 'label' => self::LABEL_DATE, 'type' => 'text'],
            ],
            'newsletter' => [
                ['key' => 'email', 'label' => self::LABEL_EMAIL, 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'تاریخ عضویت', 'type' => 'text'],
            ],
            'hair-models' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'title', 'label' => self::LABEL_TITLE, 'type' => 'text', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'tutorials' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'title', 'label' => self::LABEL_TITLE, 'type' => 'text', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'views', 'label' => 'بازدید', 'type' => 'text'],
                ['key' => 'video_url', 'label' => 'لینک ویدیو', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'orders' => [
                ['key' => 'tracking_code', 'label' => 'کد پیگیری', 'type' => 'text'],
                ['key' => 'user_name', 'label' => self::LABEL_USER, 'type' => 'text'],
                ['key' => 'total', 'label' => 'مبلغ', 'type' => 'price'],
                ['key' => 'address', 'label' => 'آدرس', 'type' => 'text'],
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status', 'options' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled']],
                ['key' => 'created_at', 'label' => self::LABEL_DATE, 'type' => 'text'],
            ],
            'settings' => [
                ['key' => 'setting_key', 'label' => 'کلید', 'type' => 'text'],
                ['key' => 'setting_value', 'label' => 'مقدار', 'type' => 'textarea'],
            ],
            'coupons' => [
                ['key' => 'code', 'label' => 'کد', 'type' => 'text', 'required' => true],
                ['key' => 'discount_type', 'label' => 'نوع', 'type' => 'select', 'options' => ['percentage', 'fixed']],
                ['key' => 'discount_value', 'label' => 'مقدار', 'type' => 'text', 'required' => true],
                ['key' => 'min_order', 'label' => 'حداقل خرید', 'type' => 'price'],
                ['key' => 'max_uses', 'label' => 'حداکثر استفاده', 'type' => 'text'],
                ['key' => 'used_count', 'label' => 'تعداد استفاده', 'type' => 'text'],
                ['key' => 'expires_at', 'label' => 'تاریخ انقضا', 'type' => 'date'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'blog' => [
                ['key' => 'image', 'label' => self::LABEL_IMAGE, 'type' => 'image'],
                ['key' => 'title', 'label' => self::LABEL_TITLE, 'type' => 'text', 'required' => true],
                ['key' => 'slug', 'label' => 'slug', 'type' => 'text'],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'author', 'label' => 'نویسنده', 'type' => 'text'],
                ['key' => 'reading_time', 'label' => 'زمان مطالعه', 'type' => 'text'],
                ['key' => 'tags', 'label' => 'برچسب‌ها', 'type' => 'text'],
                ['key' => 'excerpt', 'label' => 'خلاصه', 'type' => 'textarea'],
                ['key' => 'content', 'label' => 'محتوا', 'type' => 'textarea'],
                ['key' => 'is_published', 'label' => 'منتشر شده', 'type' => 'boolean'],
                ['key' => 'is_featured', 'label' => 'ویژه', 'type' => 'boolean'],
                ['key' => 'views', 'label' => 'بازدید', 'type' => 'text'],
                ['key' => 'meta_title', 'label' => 'عنوان متا (SEO)', 'type' => 'text'],
                ['key' => 'meta_description', 'label' => 'توضیحات متا (SEO)', 'type' => 'text'],
                ['key' => 'canonical_url', 'label' => 'آدرس کنونیکال', 'type' => 'text'],
                ['key' => 'og_title', 'label' => 'عنوان Open Graph', 'type' => 'text'],
                ['key' => 'og_description', 'label' => 'توضیحات Open Graph', 'type' => 'text'],
                ['key' => 'og_image', 'label' => 'تصویر Open Graph', 'type' => 'image'],
                ['key' => 'image_alt', 'label' => 'متن جایگزین تصویر', 'type' => 'text'],
                ['key' => 'robots', 'label' => 'دستور روبات‌ها (Robots)', 'type' => 'text'],
            ],
            'reviews' => [
                ['key' => 'product_id', 'label' => 'شناسه محصول', 'type' => 'text'],
                ['key' => 'user_name', 'label' => self::LABEL_USER, 'type' => 'text'],
                ['key' => 'rating', 'label' => self::LABEL_RATING, 'type' => 'text'],
                ['key' => 'text', 'label' => self::LABEL_COMMENT, 'type' => 'textarea'],
                ['key' => 'created_at', 'label' => self::LABEL_DATE, 'type' => 'text'],
            ],
            'contact-messages' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'email', 'label' => self::LABEL_EMAIL, 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'subject', 'label' => 'موضوع', 'type' => 'text'],
                ['key' => 'message', 'label' => 'پیام', 'type' => 'textarea'],
                ['key' => 'is_read', 'label' => 'خوانده شده', 'type' => 'boolean'],
                ['key' => 'created_at', 'label' => self::LABEL_DATE, 'type' => 'text'],
            ],
            'blog-comments' => [
                ['key' => 'post_title', 'label' => 'پست', 'type' => 'text'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'text', 'label' => self::LABEL_COMMENT, 'type' => 'textarea'],
                ['key' => 'likes', 'label' => 'لایک', 'type' => 'text'],
                ['key' => 'is_approved', 'label' => 'تأیید شده', 'type' => 'boolean'],
                ['key' => 'created_at', 'label' => self::LABEL_DATE, 'type' => 'text'],
            ],
            'product-categories' => [
                ['key' => 'name', 'label' => 'نام دسته', 'type' => 'text', 'required' => true],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'product-brands' => [
                ['key' => 'name', 'label' => 'نام برند', 'type' => 'text', 'required' => true],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'blog-categories' => [
                ['key' => 'name', 'label' => 'نام دسته', 'type' => 'text', 'required' => true],
                ['key' => 'slug', 'label' => 'slug', 'type' => 'text'],
                ['key' => 'sort_order', 'label' => 'ترتیب', 'type' => 'text'],
                ['key' => 'post_count', 'label' => 'تعداد پست', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'hair-prices' => [
                ['key' => 'service_title', 'label' => 'خدمت', 'type' => 'text'],
                ['key' => 'hair_length_title', 'label' => 'قد مو', 'type' => 'text'],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'duration_modifier', 'label' => 'ضریب مدت', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
        ];
        return $all[$section] ?? [['key' => 'id', 'label' => 'شناسه', 'type' => 'text']];
    }

    private function paginate(string $baseQuery, string $countQuery, array $params = [], int $perPage = 20): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total = (int) Database::fetch($countQuery, $params)['cnt'];
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll($baseQuery . " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset, $params);
        return ['items' => $items, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total];
    }

    private function syncMedia(string $filepath, string $originalName, string $type, string $sourceType, ?int $sourceId): void
    {
        $filepath = ltrim($filepath, '/');

        $publicDir = realpath(__DIR__ . '/../../public');
        $fullPath = realpath($publicDir . '/' . $filepath);
        if ($fullPath === false || !str_starts_with($fullPath, $publicDir . '/') && $fullPath !== $publicDir) {
            return;
        }

        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if ($existing) {
            Database::update('media', [
                'source_id' => $sourceId,
            ], self::WHERE_ID, ['id' => $existing['id']]);
        } else {
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : '';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => $originalName,
                'type' => $type,
                'mime_type' => $mime,
                'size' => $size,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function saveServiceHairPrices(int $serviceId): void
    {
        $hairPrices = $_POST['hair_prices'] ?? [];
        $hasAnySubmittedPrice = false;

        foreach ($hairPrices as $priceData) {
            $price = (int) ($priceData['price'] ?? 0);
            if ($price > 0) {
                $hasAnySubmittedPrice = true;
                break;
            }
        }

        if ($hasAnySubmittedPrice) {
            Database::query("DELETE FROM service_hair_prices WHERE service_id = ?", [$serviceId]);

            foreach ($hairPrices as $hlId => $priceData) {
                $hlId = (int) $hlId;
                $price = (int) ($priceData['price'] ?? 0);
                $durationModifier = (float) ($priceData['duration_modifier'] ?? 1.0);
                $durationModifier = max(0.1, min(9.9, $durationModifier));
                $isActive = isset($priceData['is_active']) ? 1 : 0;

                if ($hlId > 0 && $price > 0) {
                    Database::insert('service_hair_prices', [
                        'service_id' => $serviceId,
                        'hair_length_id' => $hlId,
                        'price' => $price,
                        'duration_modifier' => $durationModifier,
                        'is_active' => $isActive,
                    ]);
                }
            }
        } else {
            $existing = Database::fetch(
                "SELECT COUNT(*) as cnt FROM service_hair_prices WHERE service_id = ?",
                [$serviceId]
            );

            if (($existing['cnt'] ?? 0) == 0) {
                $service = Database::fetch("SELECT price FROM services WHERE id = ?", [$serviceId]);
                $basePrice = (int) ($service['price'] ?? 0);
                if ($basePrice > 0) {
                    $hairLengths = Database::fetchAll("SELECT id, sort_order FROM hair_lengths WHERE is_active = 1 ORDER BY sort_order");
                    foreach ($hairLengths as $index => $hl) {
                        $price = $basePrice + ($index * 150000);
                        $durationMod = max(0.1, min(9.9, 1.0 + ($index * 0.2)));
                        Database::insert('service_hair_prices', [
                            'service_id' => $serviceId,
                            'hair_length_id' => $hl['id'],
                            'price' => $price,
                            'duration_modifier' => $durationMod,
                            'is_active' => 1,
                        ]);
                    }
                }
            }
        }

        $this->clearCache('booking');
    }

    private function clearCache(string $section): void
    {
        Settings::invalidate();

        $sectionToTags = [
            'services' => ['homepage', 'booking'],
            'artists' => ['homepage', 'booking'],
            'products' => ['products'],
            'hair-models' => ['homepage', 'models'],
            'tutorials' => ['homepage'],
            'testimonials' => ['homepage'],
            'courses' => ['academy'],
            'blog' => ['blog'],
            'settings' => ['homepage'],
            'captcha' => ['homepage'],
            'orders' => ['products', 'admin'],
            'users' => ['admin'],
            'appointments' => ['homepage', 'admin'],
            'gallery' => ['gallery'],
            'enrollments' => ['academy'],
            'coupons' => ['products'],
            'newsletter' => ['admin'],
            'contact-messages' => ['admin'],
            'transactions' => ['admin'],
            'reviews' => ['products'],
            'blog-comments' => ['blog'],
            'product-categories' => ['products'],
            'product-brands' => ['products'],
            'hair-prices' => ['booking'],
            'blog-categories' => ['blog'],
            'seo' => ['homepage', 'blog'],
        ];

        $tags = $sectionToTags[$section] ?? [$section];
        foreach ($tags as $tag) {
            Cache::flushByTag($tag);
        }

        $extraKeys = [
            'home_data', 'booking_form_data', 'booking_services',
            'admin_stats', 'admin_recent_appointments', 'admin_recent_orders',
        ];
        foreach ($extraKeys as $key) {
            Cache::forget($key);
        }

        if (in_array($section, ['products', 'services', 'blog', 'courses', 'settings', 'captcha', 'gallery', 'sms'])) {
            Cache::bumpVersion();
        }
    }

    public function save(string $section): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        match ($section) {
            'settings' => $this->updateSettings(),
            'captcha' => $this->updateCaptchaSettings(),
            'gallery' => $this->saveGallery(),
            'sms' => $this->updateSmsSettings(),
            'seo' => $this->saveSeo(),
            default => $this->saveGenericSection($section),
        };
    }

    private function saveGallery(): void
    {
        if (empty($_FILES['file']['name'])) {
            flash('error', 'فایلی انتخاب نشده است.');
            redirect(self::PATH_GALLERY);
            return;
        }

        $uploadDir = __DIR__ . '/../../public/assets/uploads/gallery';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedImageMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideoMime = [self::MIME_MP4, self::MIME_WEBM, self::MIME_OGG, self::MIME_MOV];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['file']['tmp_name']);

        if (!in_array($mimeType, $allowedImageMime) && !in_array($mimeType, $allowedVideoMime)) {
            flash('error', 'نوع فایل مجاز نیست. فقط تصاویر (jpg, png, gif, webp) و ویدیو (mp4, webm, ogg, mov)');
            redirect(self::PATH_GALLERY);
            return;
        }

        $isImage = in_array($mimeType, $allowedImageMime);
        $ext = match ($mimeType) {
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            self::MIME_MP4 => '.mp4',
            self::MIME_WEBM => self::EXT_WEBM,
            self::MIME_OGG => '.ogv',
            self::MIME_MOV => '.mov',
            default => '.bin',
        };
        $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            $filepath = 'assets/uploads/gallery/' . $filename;
            $altText = sanitize($_POST['alt_text'] ?? $_FILES['file']['name']);
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => $_FILES['file']['name'],
                'type' => $isImage ? 'image' : 'video',
                'mime_type' => $mimeType,
                'size' => filesize($dest),
                'alt_text' => $altText,
                'source_type' => 'direct',
                'uploaded_by' => Auth::id(),
            ]);
            $this->clearCache('gallery');
            flash('success', 'فایل با موفقیت آپلود شد.');
        } else {
            flash('error', 'خطا در آپلود فایل.');
        }
        redirect(self::PATH_GALLERY);
    }

    private function saveGenericSection(string $section): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $table = $this->sectionToTable($section);

        if (!$table) {
            redirect(self::PATH_ADMIN);
        }

        $data = $this->collectPostData($section);

        $handled = match ($section) {
            'orders' => $this->saveOrders($id, $data, $table),
            'blog-comments' => $this->saveBlogComments($id, $data, $table),
            'blog-categories' => $this->saveBlogCategories($id, $data, $table),
            'appointments' => $this->saveAppointments($id, $data, $table),
            'enrollments' => $this->saveEnrollments($id, $data, $table),
            'users' => $this->saveUsers($id, $data, $table),
            'artists' => $this->saveArtists($id, $data, $table),
            'hair-prices' => $this->saveHairPrices($id),
            default => false,
        };
        if ($handled) {
            return;
        }

        $this->handleFileUploads($section, $id, $table, $data);

        match ($section) {
            'products' => $this->handleProductVideo($data, $id),
            'courses' => $this->handleCourseVideo($data, $id),
            'blog' => $this->handleBlogContent($id, $data),
            default => null,
        };

        if ($id) {
            Database::update($table, $data, self::WHERE_ID, ['id' => $id]);
        } else {
            $id = Database::insert($table, $data);
        }

        $this->handlePostSave($section, $id, $data);
        $this->clearCache($section);
        flash('success', 'با موفقیت ذخیره شد.');
        redirect('/admin/' . $section);
    }

    private function collectPostData(string $section): array
    {
        $allowedFields = array_column($this->getColumns($section), 'key');
        $allowedFields[] = 'description';
        $allowedFields[] = 'bio';
        $allowedFields[] = 'text';
        $allowedFields[] = 'notes';
        $allowedFields[] = 'instagram';

        $rawFields = ['description', 'bio', 'text', 'notes', 'content'];
        $intFields = [
            'views', 'reading_time', 'sort_order', 'stock', 'students',
            'price', 'old_price', 'discount_value', 'max_uses', 'used_count',
            'amount', 'user_id', 'points', 'product_id', 'min_order',
            'post_count', 'likes',
        ];
        $floatFields = ['wallet', 'rating', 'duration_modifier'];
        $dateFields = ['expires_at', 'published_at', 'appointment_date'];
        $skipFields = ['created_at', 'updated_at'];
        $data = [];
        foreach ($allowedFields as $field) {
            if (in_array($field, $skipFields)) {
                continue;
            }
            if (isset($_POST[$field])) {
                $value = in_array($field, $rawFields) ? $_POST[$field] : sanitize($_POST[$field]);
                if (in_array($field, $intFields)) {
                    $value = (int) $value;
                } elseif (in_array($field, $floatFields)) {
                    $value = (float) $value;
                } elseif (in_array($field, $dateFields)) {
                    $value = $value !== '' && $value !== null ? $value : null;
                }
                $data[$field] = $value;
            }
        }
        return $data;
    }

    private function handleFileUploads(string $section, int $id, string $table, array &$data): void
    {
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $oldImage = $id ? (Database::fetch("SELECT image FROM {$table} WHERE id = ?", [$id])['image'] ?? null) : null;
            $uploaded = FileUploader::upload($_FILES['image'], $section, $oldImage);
            if ($uploaded) {
                $data['image'] = $uploaded;
            }
        }
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $oldAvatar = $id ? (Database::fetch("SELECT avatar FROM {$table} WHERE id = ?", [$id])['avatar'] ?? null) : null;
            $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar', $oldAvatar);
            if ($uploaded) {
                $data['avatar'] = $uploaded;
            }
        }
        if (!empty($_FILES['og_image']['name']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
            $oldOg = $id ? (Database::fetch("SELECT og_image FROM {$table} WHERE id = ?", [$id])['og_image'] ?? null) : null;
            $uploaded = FileUploader::upload($_FILES['og_image'], 'og_image', $oldOg);
            if ($uploaded) {
                $data['og_image'] = $uploaded;
            }
        }
    }

    private function handlePostSave(string $section, int $id, array $data): void
    {
        match ($section) {
            'services' => $this->saveServiceHairPrices($id),
            'courses' => $this->syncCourseMedia($data),
            'products' => $this->syncProductMedia($data, $id),
            default => null,
        };
    }

    private function syncCourseMedia(array $data): void
    {
        if (!empty($data['video_url']) && ($data['video_type'] ?? '') === 'upload') {
            $this->syncMedia($data['video_url'], $_FILES['video_url']['name'] ?? basename($data['video_url']), 'video', 'course_video', null);
        }
    }

    private function syncProductMedia(array $data, int $id): void
    {
        if (!empty($data['image'])) {
            $this->syncMedia('assets/images/' . $data['image'], $data['image'], 'image', 'product_image', $id);
        }
        if (!empty($data['video_url']) && ($data['video_type'] ?? '') === 'upload') {
            $this->syncMedia($data['video_url'], $_FILES['video_url']['name'] ?? basename($data['video_url']), 'video', 'product_video', $id);
        }
        $deleteIds = trim($_POST['delete_gallery_ids'] ?? '');
        if ($deleteIds !== '') {
            foreach (array_map('intval', explode(',', $deleteIds)) as $did) {
                $gi = Database::fetch("SELECT * FROM product_images WHERE id = ?", [$did]);
                if ($gi) {
                    $filePath = __DIR__ . '/../../public/assets/images/' . $gi['image'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    Database::delete('product_images', self::WHERE_ID_PARAM, [$did]);
                }
            }
        }
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['name'] as $i => $name) {
                if ($name === '' || $_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $file = [
                    'name' => $_FILES['gallery_images']['name'][$i],
                    'type' => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'error' => $_FILES['gallery_images']['error'][$i],
                    'size' => $_FILES['gallery_images']['size'][$i],
                ];
                $uploaded = FileUploader::upload($file, 'product_gallery');
                if ($uploaded) {
                    Database::insert('product_images', [
                        'product_id' => $id,
                        'image' => $uploaded,
                        'sort_order' => $i,
                    ]);
                    $this->syncMedia('assets/images/' . $uploaded, $file['name'], 'image', 'product_gallery', $id);
                }
            }
        }
    }

    private function handleVideoUpload(array &$data, int $id): void
    {
        $videoType = $_POST['video_type'] ?? 'upload';
        if (!empty($_FILES['video_url']['name'])) {
            $videoAllowed = [self::MIME_MP4, self::MIME_WEBM, self::MIME_OGG, self::MIME_MOV];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['video_url']['tmp_name']);
            if (in_array($mimeType, $videoAllowed)) {
                $ext = match ($mimeType) {
                    self::MIME_MP4 => '.mp4',
                    self::MIME_WEBM => self::EXT_WEBM,
                    self::MIME_OGG => '.ogv',
                    self::MIME_MOV => '.mov',
                    default => '.mp4',
                };
                $name = 'product_video_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
                $uploadDir = __DIR__ . '/../../public/assets/uploads/videos';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $dest = $uploadDir . '/' . $name;
                if (move_uploaded_file($_FILES['video_url']['tmp_name'], $dest)) {
                    $data['video_url'] = '/assets/uploads/videos/' . $name;
                    $data['video_type'] = 'upload';
                }
            }
        } elseif ($videoType !== 'upload') {
            $data['video_url'] = sanitize($_POST['video_url'] ?? '');
            $data['video_type'] = $videoType;
        } elseif ($id) {
            unset($data['video_url'], $data['video_type']);
        }
    }

    private function handleProductVideo(array &$data, int $id): void
    {
        $this->handleVideoUpload($data, $id);
    }

    private function handleCourseVideo(array &$data, int $id): void
    {
        $videoType = $_POST['video_type'] ?? 'upload';
        if (!empty($_FILES['video_url']['name'])) {
            $videoAllowed = [self::MIME_MP4, self::MIME_WEBM, self::MIME_OGG, self::MIME_MOV];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['video_url']['tmp_name']);
            if (in_array($mimeType, $videoAllowed)) {
                $ext = match ($mimeType) {
                    self::MIME_MP4 => '.mp4',
                    self::MIME_WEBM => self::EXT_WEBM,
                    self::MIME_OGG => '.ogv',
                    self::MIME_MOV => '.mov',
                    default => '.mp4',
                };
                $name = 'course_video_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
                $uploadDir = __DIR__ . '/../../public/assets/uploads/videos';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $dest = $uploadDir . '/' . $name;
                if (move_uploaded_file($_FILES['video_url']['tmp_name'], $dest)) {
                    $data['video_url'] = '/assets/uploads/videos/' . $name;
                    $data['video_type'] = 'upload';
                }
            }
        } elseif ($videoType !== 'upload') {
            $data['video_url'] = sanitize($_POST['video_url'] ?? '');
            $data['video_type'] = $videoType;
        } elseif ($id) {
            unset($data['video_url'], $data['video_type']);
        }
    }

    private function handleBlogContent(int $id, array &$data): void
    {
        $title = $data['title'] ?? '';
        if ($title === '') {
            flash('error', 'عنوان پست الزامی است.');
            redirect('/admin/blog');
            return;
        }

        if (empty($data['slug'])) {
            $baseSlug = slugify($title);
            $slug = $baseSlug;
            $counter = 1;
            while (Database::fetch("SELECT id FROM blog_posts WHERE slug = ? AND id != ?", [$slug, $id ?: 0])) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if (empty($data['slug'])) {
            $data['slug'] = 'post-' . bin2hex(random_bytes(8));
        }

        if (!$id) {
            $data['published_at'] = date('Y-m-d');
        }

        if ($id && empty($data['image'])) {
            $existing = Database::fetch("SELECT image FROM blog_posts WHERE id = ?", [$id]);
            if ($existing) {
                $data['image'] = $existing['image'];
            }
        }
        if ($id && empty($data['og_image'])) {
            $existing = Database::fetch("SELECT og_image FROM blog_posts WHERE id = ?", [$id]);
            if ($existing && !empty($existing['og_image'])) {
                $data['og_image'] = $existing['og_image'];
            }
        }
    }

    private function saveOrders(int $id, array $data, string $table): bool
    {
        if (!$id || !isset($data['status'])) {
            $this->clearCache('orders');
            redirect('/admin/orders');
            return true;
        }
        $updateData = ['status' => $data['status']];
        if (isset($data['payment_status'])) {
            $updateData['payment_status'] = $data['payment_status'];
        }
        Database::update($table, $updateData, self::WHERE_ID, ['id' => $id]);
        $this->clearCache('orders');
        flash('success', 'وضعیت سفارش به‌روزرسانی شد.');
        redirect('/admin/orders');
        return true;
    }

    private function saveBlogComments(int $id, array $data, string $table): bool
    {
        if ($id && isset($data['is_approved'])) {
            Database::update($table, ['is_approved' => (int) $data['is_approved']], self::WHERE_ID, ['id' => $id]);
            flash('success', 'وضعیت نظر به‌روزرسانی شد.');
        }
        $this->clearCache('blog-comments');
        redirect('/admin/blog-comments');
        return true;
    }

    private function saveBlogCategories(int $id, array $data, string $table): bool
    {
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            flash('error', 'نام دسته الزامی است.');
            redirect(self::PATH_BLOG_CATEGORIES);
            return true;
        }

        $slug = $data['slug'] ?? '';
        if ($slug === '') {
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name, '-')));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            if (empty($slug)) {
                $slug = 'cat-' . time();
            }
        }

        $exists = Database::fetch("SELECT id FROM blog_categories WHERE name = ? AND id != ?", [$name, $id ?: 0]);
        if ($exists) {
            flash('error', 'این نام دسته قبلاً استفاده شده است.');
            redirect(self::PATH_BLOG_CATEGORIES);
            return true;
        }

        $insertData = [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            Database::update($table, $insertData, self::WHERE_ID, ['id' => $id]);
        } else {
            Database::insert($table, $insertData);
        }

        $this->clearCache('blog-categories');
        flash('success', 'دسته با موفقیت ذخیره شد.');
        redirect(self::PATH_BLOG_CATEGORIES);
        return true;
    }

    private function deleteBlogCategories(int $id): void
    {
        $postCount = Database::fetch("SELECT COUNT(*) as cnt FROM blog_posts WHERE category = (SELECT name FROM blog_categories WHERE id = ?)", [$id]);
        if ($postCount && $postCount['cnt'] > 0) {
            flash('error', 'این دسته دارای پست است و قابل حذف نیست.');
            redirect(self::PATH_BLOG_CATEGORIES);
            return;
        }
        Database::query("DELETE FROM blog_categories WHERE id = ?", [$id]);
        $this->clearCache('blog-categories');
        flash('success', 'دسته حذف شد.');
        redirect(self::PATH_BLOG_CATEGORIES);
    }

    private function saveAppointments(int $id, array $data, string $table): bool
    {
        if ($id) {
            $updateData = [];
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }
            if (!empty($updateData)) {
                Database::update($table, $updateData, self::WHERE_ID, ['id' => $id]);
                flash('success', 'نوبت با موفقیت به‌روزرسانی شد.');
            }
        }
        $this->clearCache('appointments');
        redirect('/admin/appointments');
        return true;
    }

    private function saveEnrollments(int $id, array $data, string $table): bool
    {
        if ($id && isset($data['progress'])) {
            Database::update($table, ['progress' => (int) $data['progress']], self::WHERE_ID, ['id' => $id]);
            flash('success', 'پیشرفت دوره به‌روزرسانی شد.');
        }
        $this->clearCache('enrollments');
        redirect('/admin/enrollments');
        return true;
    }

    private function saveUsers(int $id, array $data, string $table): bool
    {
        if (!$id) {
            flash('error', 'ثبت کاربر جدید از طریق پنل ادمین پشتیبانی نمی‌شود.');
            redirect('/admin/users');
            return true;
        }
        unset($data['password'], $data['phone']);
        if (!empty($_FILES['avatar']['name'])) {
            $oldAvatar = Database::fetch("SELECT avatar FROM users WHERE id = ?", [$id])['avatar'] ?? null;
            $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar_' . $id, $oldAvatar);
            if ($uploaded) {
                $data['avatar'] = $uploaded;
            }
        }
        if (!empty($data)) {
            Database::update($table, $data, self::WHERE_ID, ['id' => $id]);
        }
        $this->clearCache('users');
        flash('success', 'کاربر با موفقیت ذخیره شد.');
        redirect('/admin/users');
        return true;
    }

    private function saveArtists(int $id, array $data, string $table): bool
    {
        Database::beginTransaction();
        try {
            if (!empty($_FILES['avatar']['name'])) {
                $oldAvatar = $id ? Database::fetch("SELECT avatar FROM artists WHERE id = ?", [$id])['avatar'] ?? null : null;
                $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar', $oldAvatar);
                if ($uploaded) {
                    $data['avatar'] = $uploaded;
                }
            }
            if ($id) {
                Database::update($table, $data, self::WHERE_ID, ['id' => $id]);
            } else {
                $id = Database::insert($table, $data);
            }
            $serviceIds = array_map('intval', $_POST['services'] ?? []);
            Database::query("DELETE FROM artist_services WHERE artist_id = ?", [$id]);
            foreach ($serviceIds as $svcId) {
                if ($svcId > 0) {
                    Database::query("INSERT INTO artist_services (artist_id, service_id) VALUES (?, ?)", [$id, $svcId]);
                }
            }
            Database::commit();
        } catch (Throwable $e) {
            Database::rollback();
            flash('error', 'خطا در ذخیره آرایشگر: ' . $e->getMessage());
            redirect('/admin/artists');
            return true;
        }
        $this->clearCache('artists');
        flash('success', 'آرایشگر با موفقیت ذخیره شد.');
        redirect('/admin/artists');
        return true;
    }

    private function saveHairPrices(int $id): bool
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $hairLengthId = (int) ($_POST['hair_length_id'] ?? 0);
        $price = (int) ($_POST['price'] ?? 0);
        $durationModifier = max(0.1, min(9.9, (float) ($_POST['duration_modifier'] ?? 1.0)));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!$serviceId || !$hairLengthId || !$price) {
            flash('error', 'لطفاً تمام فیلدهای ضروری را پر کنید.');
            redirect(self::PATH_HAIR_PRICES);
            return true;
        }

        if ($id) {
            Database::update('service_hair_prices', [
                'service_id' => $serviceId,
                'hair_length_id' => $hairLengthId,
                'price' => $price,
                'duration_modifier' => $durationModifier,
                'is_active' => $isActive,
            ], self::WHERE_ID, ['id' => $id]);
        } else {
            $exists = Database::fetch(
                "SELECT id FROM service_hair_prices WHERE service_id = ? AND hair_length_id = ?",
                [$serviceId, $hairLengthId]
            );
            if ($exists) {
                flash('error', 'این ترکیب خدمت و قد مو از قبل وجود دارد.');
                redirect(self::PATH_HAIR_PRICES);
                return true;
            }
            Database::insert('service_hair_prices', [
                'service_id' => $serviceId,
                'hair_length_id' => $hairLengthId,
                'price' => $price,
                'duration_modifier' => $durationModifier,
                'is_active' => $isActive,
            ]);
        }
        $this->clearCache('booking');
        flash('success', 'قیمت با موفقیت ذخیره شد.');
        redirect(self::PATH_HAIR_PRICES);
        return true;
    }

    public function delete(string $section, int $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        if ($section === 'hair-prices') {
            Database::query("DELETE FROM service_hair_prices WHERE id = ?", [$id]);
            $this->clearCache('booking');
            flash('success', 'قیمت با موفقیت حذف شد.');
            redirect(self::PATH_HAIR_PRICES);
            return;
        }

        $table = $this->sectionToTable($section);

        if ($section === 'products') {
            $galleryImages = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ?", [$id]);
            foreach ($galleryImages as $gi) {
                $filePath = __DIR__ . '/../../public/assets/images/' . $gi['image'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            Database::delete('product_images', 'product_id = ?', [$id]);
        }

        if ($section === 'gallery') {
            $media = Database::fetch("SELECT * FROM media WHERE id = ?", [$id]);
            if ($media) {
                $filePath = __DIR__ . '/../../public/' . $media['filepath'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                Database::delete('media', self::WHERE_ID_PARAM, [$id]);
                if (in_array($media['source_type'], ['product_image', 'product_gallery', 'product_video'])) {
                    Cache::flushByTag('products');
                }
                if (in_array($media['source_type'], ['course_video'])) {
                    Cache::flushByTag('academy');
                }
            }
        }

        if ($section === 'blog-categories') {
            $this->deleteBlogCategories($id);
            return;
        }

        if ($table) {
            Database::delete($table, self::WHERE_ID_PARAM, [$id]);
        }

        $this->clearCache($section);
        flash('success', 'با موفقیت حذف شد.');
        redirect('/admin/' . $section);
    }

    public function updateCaptchaSettings(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $captchaKeys = [
            'captcha_enabled_admin', 'captcha_enabled_booking', 'captcha_enabled_newsletter',
            'captcha_difficulty',
        ];
        for ($i = 1; $i <= 10; $i++) {
            $captchaKeys[] = 'captcha_question_' . $i;
        }

        $upserts = [];
        $upsertParams = [];
        foreach ($captchaKeys as $key) {
            if (isset($_POST[$key])) {
                $upserts[] = self::PLACEHOLDER_PAIR;
                $upsertParams[] = $key;
                $upsertParams[] = sanitize($_POST[$key]);
            }
        }
        if (!empty($upserts)) {
            $values = implode(', ', $upserts);
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES {$values} ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                $upsertParams
            );
        }

        Captcha::clearCache();
        Settings::invalidate();
        flash('success', 'تنظیمات کپچا با موفقیت ذخیره شد.');
        redirect('/admin/captcha');
    }

    public function updateSettings(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

                $htmlKeys = ['about_content', 'contact_map_location', 'privacy_content', 'terms_content', 'hero_customers_text', 'blog_sidebar_about', 'academy_instructor_bio'];
        $toggleKeys = [];

        $smsEnabled = isset($_POST['setting_sms_enabled']);
        $apiKey = trim($_POST['setting_sms_api_key'] ?? '');
        $sender = trim($_POST['setting_sms_sender'] ?? '');
        $otpTtl = $_POST['setting_sms_otp_ttl'] ?? '';
        $otpLength = $_POST['setting_sms_otp_length'] ?? '';

        if ($smsEnabled) {
            if ($apiKey === '') {
                flash('error', 'کلید API کاوه‌نگار نمی‌تواند خالی باشد.');
                redirect(self::PATH_SETTINGS);
                return;
            }
            if ($sender === '') {
                flash('error', 'شماره فرستنده نمی‌تواند خالی باشد.');
                redirect(self::PATH_SETTINGS);
                return;
            }
        }

        if ($otpTtl !== '' && (!ctype_digit($otpTtl) || (int)$otpTtl < 60 || (int)$otpTtl > 600)) {
            flash('error', 'مدت اعتبار کد تأیید باید عددی بین ۶۰ تا ۶۰۰ ثانیه باشد.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        if ($otpLength !== '' && (!ctype_digit($otpLength) || (int)$otpLength < 4 || (int)$otpLength > 6)) {
            flash('error', 'طول کد تأیید باید عددی بین ۴ تا ۶ رقم باشد.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        $upserts = [];
        $upsertParams = [];
        $imageUploadKeys = ['hero_bg_image', 'hero_model_image', 'og_image', 'about_image'];

        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $settingKey = substr($key, 8);
                if (in_array($settingKey, $imageUploadKeys)) {
                    continue;
                }
                $value = in_array($settingKey, $htmlKeys) ? $value : sanitize($value);
                $upserts[] = self::PLACEHOLDER_PAIR;
                $upsertParams[] = $settingKey;
                $upsertParams[] = $value;
            }
        }

        foreach ($imageUploadKeys as $imageKey) {
            if (isset($_POST['delete_image_' . $imageKey]) && $_POST['delete_image_' . $imageKey] === '1') {
                $old = Settings::get($imageKey);
                if ($old && is_file(__DIR__ . '/../../public/assets/images/' . basename($old))) {
                    @unlink(__DIR__ . '/../../public/assets/images/' . basename($old));
                }
                $upserts[] = self::PLACEHOLDER_PAIR;
                $upsertParams[] = $imageKey;
                $upsertParams[] = '';
                continue;
            }

            $fileKey = 'setting_file_' . $imageKey;
            if (!empty($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $old = Settings::get($imageKey);
                $uploaded = FileUploader::upload($_FILES[$fileKey], 'setting', $old);
                if ($uploaded) {
                    $upserts[] = self::PLACEHOLDER_PAIR;
                    $upsertParams[] = $imageKey;
                    $upsertParams[] = $uploaded;
                }
            }
        }

        foreach ($toggleKeys as $toggleKey) {
            if (!isset($_POST['setting_' . $toggleKey])) {
                $upserts[] = self::PLACEHOLDER_PAIR;
                $upsertParams[] = $toggleKey;
                $upsertParams[] = '0';
            }
        }

        if (!empty($upserts)) {
            $values = implode(', ', $upserts);
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES {$values} ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                $upsertParams
            );
        }

        Settings::invalidate();
        Config::reset();
        Cache::flushByTag('homepage');
        Cache::forget('home_data');
        flash('success', 'تنظیمات با موفقیت ذخیره شد.');
        redirect(self::PATH_SETTINGS);
    }

    // Specific section handlers
    private function sectionAppointments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR s.title LIKE ? OR ar.name LIKE ? OR a.status LIKE ?)';
            $searchParams = array_fill(0, 5, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT a.*, u.name as user_name, u.family as user_family, s.title as service_title, ar.name as artist_name
             FROM appointments a
             JOIN users u ON a.user_id = u.id
             LEFT JOIN services s ON a.service_id = s.id
             LEFT JOIN artists ar ON a.artist_id = ar.id
             WHERE 1=1{$searchWhere}
             ORDER BY a.appointment_date DESC",
            "SELECT COUNT(*) as cnt
             FROM appointments a
             JOIN users u ON a.user_id = u.id
             LEFT JOIN services s ON a.service_id = s.id
             LEFT JOIN artists ar ON a.artist_id = ar.id
             WHERE 1=1{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('appointments');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionUsers(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (name LIKE ? OR family LIKE ? OR phone LIKE ? OR email LIKE ?)';
            $searchParams = array_fill(0, 4, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT id, name, family, phone, email, avatar, role, is_active, level, points, wallet, created_at FROM users{$searchWhere} ORDER BY id DESC",
            "SELECT COUNT(*) as cnt FROM users{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('users');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionCaptcha(array &$data): void
    {
        $rows = Database::fetchAll("SELECT * FROM settings WHERE setting_key LIKE 'captcha_%' ORDER BY id");
        $data['captcha_settings'] = [];
        foreach ($rows as $row) {
            $data['captcha_settings'][$row['setting_key']] = $row['setting_value'];
        }
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionSettings(array &$data): void
    {
        $rows = Database::fetchAll("SELECT * FROM settings ORDER BY id");
        $data['settings'] = [];
        foreach ($rows as $row) {
            $data['settings'][$row['setting_key']] = $row['setting_value'];
        }
        $data['columns'] = $this->getColumns('settings');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionOrders(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (o.tracking_code LIKE ? OR u.name LIKE ? OR u.family LIKE ? OR o.status LIKE ? OR o.total LIKE ? OR EXISTS (SELECT 1 FROM order_items oi2 WHERE oi2.order_id = o.id AND oi2.product_name LIKE ?))';
            $searchParams = array_fill(0, 6, likePattern($search));
        }

        $today = date('Y-m-d');
        $data['orderStats'] = Database::fetch(
            "SELECT
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN DATE(o.created_at) = ? THEN o.total ELSE 0 END), 0) as today_revenue,
                COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN DATE(o.created_at) = ? THEN 1 END) as today_count
             FROM orders o",
            [$today, $today]
        );

        $paged = $this->paginate(
            "SELECT o.*, u.name as user_name, u.family as user_family
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE 1=1{$searchWhere}
             ORDER BY o.created_at DESC",
            "SELECT COUNT(*) as cnt
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE 1=1{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;

        $orderIds = array_column($data['items'], 'id');
        $data['orderItems'] = [];
        if (!empty($orderIds)) {
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $rows = Database::fetchAll(
                "SELECT oi.*, p.image as product_image
                 FROM order_items oi
                 LEFT JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id IN ({$placeholders})
                 ORDER BY oi.id",
                $orderIds
            );
            foreach ($rows as $row) {
                $data['orderItems'][$row['order_id']][] = $row;
            }
        }

        $data['columns'] = $this->getColumns('orders');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionEnrollments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR c.title LIKE ?)';
            $searchParams = array_fill(0, 3, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT ce.*, u.name as user_name, u.family as user_family, c.title as course_title
             FROM course_enrollments ce
             JOIN users u ON ce.user_id = u.id
             JOIN courses c ON ce.course_id = c.id
             WHERE 1=1{$searchWhere}
             ORDER BY ce.created_at DESC",
            "SELECT COUNT(*) as cnt
             FROM course_enrollments ce
             JOIN users u ON ce.user_id = u.id
             JOIN courses c ON ce.course_id = c.id
             WHERE 1=1{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('enrollments');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionTransactions(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR t.type LIKE ? OR t.description LIKE ?)';
            $searchParams = array_fill(0, 4, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT t.*, u.name as user_name, u.family as user_family
             FROM transactions t
             JOIN users u ON t.user_id = u.id
             WHERE 1=1{$searchWhere}
             ORDER BY t.created_at DESC",
            "SELECT COUNT(*) as cnt
             FROM transactions t
             JOIN users u ON t.user_id = u.id
             WHERE 1=1{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('transactions');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionCoupons(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE code LIKE ?';
            $searchParams = [likePattern($search)];
        }
        $paged = $this->paginate(
            "SELECT * FROM coupons{$searchWhere} ORDER BY id DESC",
            "SELECT COUNT(*) as cnt FROM coupons{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('coupons');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionBlog(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (title LIKE ? OR category LIKE ? OR author LIKE ? OR tags LIKE ?)';
            $searchParams = array_fill(0, 4, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT * FROM blog_posts{$searchWhere} ORDER BY created_at DESC",
            "SELECT COUNT(*) as cnt FROM blog_posts{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('blog');
        $this->enrichBlogColumns($data['columns']);
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionReviews(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (r.user_name LIKE ? OR p.name LIKE ?)';
            $searchParams = array_fill(0, 2, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT r.*, p.name as product_name
             FROM reviews r
             LEFT JOIN products p ON r.product_id = p.id{$searchWhere}
             ORDER BY r.id DESC",
            "SELECT COUNT(*) as cnt FROM reviews r
             LEFT JOIN products p ON r.product_id = p.id{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('reviews');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionContactMessages(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)';
            $searchParams = array_fill(0, 5, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT * FROM contact_messages{$searchWhere} ORDER BY id DESC",
            "SELECT COUNT(*) as cnt FROM contact_messages{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('contact-messages');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionBlogComments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (bc.name LIKE ? OR bc.text LIKE ? OR bp.title LIKE ?)';
            $searchParams = array_fill(0, 3, likePattern($search));
        }
        $paged = $this->paginate(
            "SELECT bc.*, bp.title as post_title
             FROM blog_comments bc
             LEFT JOIN blog_posts bp ON bc.post_id = bp.id{$searchWhere}
             ORDER BY bc.id DESC",
            "SELECT COUNT(*) as cnt
             FROM blog_comments bc
             LEFT JOIN blog_posts bp ON bc.post_id = bp.id{$searchWhere}",
            $searchParams
        );
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['columns'] = $this->getColumns('blog-comments');
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionGallery(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $filter = trim($_GET['filter'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(12, min(24, (int) ($_GET['limit'] ?? 20)));
        $cacheKey = 'gallery_page_' . $page . '_' . $limit . '_' . $filter . '_' . hash('sha256', $search);

        $paged = Cache::remember($cacheKey, Config::get('cache.ttl.admin', 300), function () use ($search, $filter, $limit) {
            $where = 'WHERE 1=1';
            $params = [];

            if ($search !== '') {
                $where .= ' AND (original_name LIKE ? OR alt_text LIKE ?)';
                $params[] = likePattern($search);
                $params[] = likePattern($search);
            }
            if ($filter === 'image') {
                $where .= " AND type = 'image'";
            } elseif ($filter === 'video') {
                $where .= " AND type = 'video'";
            }

            return $this->paginate(
                "SELECT * FROM media {$where} ORDER BY id DESC",
                "SELECT COUNT(*) as cnt FROM media {$where}",
                $params,
                $limit
            );
        }, ['gallery']);

        $items = $paged['items'];
        $publicDir = realpath(__DIR__ . '/../../public');
        foreach ($items as &$item) {
            $fullPath = realpath($publicDir . '/' . ltrim($item['filepath'], '/'));
            $item['file_exists'] = $fullPath !== false && str_starts_with($fullPath, $publicDir . '/');
        }
        unset($item);

        $data['items'] = $items;
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['filter'] = $filter;
        $data['limit'] = $limit;
        $this->view(self::VIEW_ADMIN, $data);
    }

    public function changePassword(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            flash('error', 'تمام فیلدها را پر کنید.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        if ($new !== $confirm) {
            flash('error', 'رمز عبور جدید و تکرار آن مطابقت ندارند.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        if (strlen($new) < 6) {
            flash('error', 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        $user = Database::fetch("SELECT password FROM users WHERE id = ?", [Auth::id()]);
        if (!Auth::verify($current, $user['password'])) {
            flash('error', 'رمز عبور فعلی اشتباه است.');
            redirect(self::PATH_SETTINGS);
            return;
        }

        Database::update('users', ['password' => Auth::hash($new)], self::WHERE_ID, ['id' => Auth::id()]);
        flash('success', 'رمز عبور با موفقیت تغییر یافت.');
        redirect(self::PATH_SETTINGS);
    }

    private function sectionToTable(string $section): ?string
    {
        $map = [
            'services' => 'services',
            'artists' => 'artists',
            'appointments' => 'appointments',
            'products' => 'products',
            'users' => 'users',
            'courses' => 'courses',
            'enrollments' => 'course_enrollments',
            'testimonials' => 'testimonials',
            'transactions' => 'transactions',
            'hair-models' => 'hair_models',
            'tutorials' => 'tutorials',
            'orders' => 'orders',
            'newsletter' => 'newsletter',
            'coupons' => 'coupons',
            'blog' => 'blog_posts',
            'contact-messages' => 'contact_messages',
            'reviews' => 'reviews',
            'blog-comments' => 'blog_comments',
            'product-categories' => 'product_categories',
            'product-brands' => 'product_brands',
            'hair-prices' => 'service_hair_prices',
            'blog-categories' => 'blog_categories',
        ];
        return $map[$section] ?? null;
    }

    private function sectionHairPrices(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $data['columns'] = $this->getColumns('hair-prices');

        // Get all services and hair lengths for dropdowns
        $data['allServices'] = Database::fetchAll("SELECT id, title FROM services ORDER BY title");
        $data['allHairLengths'] = Database::fetchAll("SELECT id, title FROM hair_lengths ORDER BY sort_order");

        // Base query for hair prices
        $baseQuery = "
            SELECT shp.*, s.title as service_title, hl.title as hair_length_title
            FROM service_hair_prices shp
            INNER JOIN services s ON shp.service_id = s.id
            INNER JOIN hair_lengths hl ON shp.hair_length_id = hl.id
        ";

        $countQuery = "SELECT COUNT(*) as cnt FROM service_hair_prices shp
                        INNER JOIN services s ON shp.service_id = s.id
                        INNER JOIN hair_lengths hl ON shp.hair_length_id = hl.id";

        $params = [];
        $searchWhere = '';

        if ($search !== '') {
            $searchWhere = " WHERE s.title LIKE ? OR hl.title LIKE ?";
            $params = [likePattern($search), likePattern($search)];
        }

        $countQuery .= $searchWhere;
        $total = (int) Database::fetch($countQuery, $params)['cnt'];
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll($baseQuery . $searchWhere . " ORDER BY s.title, hl.sort_order LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]));

        $data['items'] = $items;
        $data['page'] = $page;
        $data['totalPages'] = $totalPages;
        $data['total'] = $total;
        $data['search'] = $search;
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function sectionSms(array &$data): void
    {
        $smsService = new SmsService();

        $data['smsSettings'] = [];
        $smsKeys = ['sms_enabled', 'sms_api_key', 'sms_template_id', 'sms_line_number', 'sms_otp_ttl', 'sms_otp_length'];
        foreach ($smsKeys as $key) {
            $data['smsSettings'][$key] = Settings::get($key, '');
        }

        $data['smsStats'] = ['total_sent' => 0, 'today_sent' => 0, 'failed_count' => 0, 'total_credits' => 0];
        try {
            $today = date('Y-m-d');
            $stats = Database::fetch(
                "SELECT
                    COUNT(*) as total_sent,
                    COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END), 0) as today_sent,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
                    COALESCE(SUM(credits), 0) as total_credits
                 FROM sms_logs",
                [$today]
            );
            if ($stats) {
                $data['smsStats'] = $stats;
            }
        } catch (Throwable $e) {
            error_log("SMS stats query failed: " . $e->getMessage());
        }

        $data['smsCredit'] = ['total_purchased' => 0, 'total_used' => 0];
        try {
            $purchased = Database::fetch("SELECT COALESCE(SUM(amount), 0) as total FROM sms_credits WHERE status = 'completed'");
            $used = Database::fetch("SELECT COALESCE(SUM(credits), 0) as total FROM sms_logs WHERE status != 'failed'");
            $data['smsCredit'] = [
                'total_purchased' => $purchased['total'] ?? 0,
                'total_used' => $used['total'] ?? 0,
            ];
        } catch (Throwable $e) {
            error_log("SMS credit query failed: " . $e->getMessage());
        }

        $data['lines'] = [];
        if ($smsService->isConfigured()) {
            $result = $smsService->getLines();
            if ($result['status'] && !empty($result['data'])) {
                $data['lines'] = $result['data'];
            }
        }

        $data['templates'] = [];
        try {
            $data['templates'] = Database::fetchAll("SELECT * FROM sms_templates ORDER BY id DESC");
        } catch (Throwable $e) {
            error_log("SMS templates query failed: " . $e->getMessage());
        }

        $search = trim($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $tab = $_GET['tab'] ?? 'settings';

        $data['smsTab'] = $tab;

        if ($tab === 'logs') {
            $searchWhere = '';
            $searchParams = [];
            if ($search !== '') {
                $searchWhere = ' WHERE (phone LIKE ? OR message LIKE ?)';
                $searchParams = [likePattern($search), likePattern($search)];
            }

            try {
                $countQuery = "SELECT COUNT(*) as cnt FROM sms_logs{$searchWhere}";
                $total = (int) Database::fetch($countQuery, $searchParams)['cnt'];
                $totalPages = max(1, (int) ceil($total / $perPage));
                $page = min($page, $totalPages);
                $offset = ($page - 1) * $perPage;

                $data['items'] = Database::fetchAll(
                    "SELECT * FROM sms_logs{$searchWhere} ORDER BY id DESC LIMIT ? OFFSET ?",
                    array_merge($searchParams, [$perPage, $offset])
                );
            } catch (Throwable $e) {
                error_log("SMS logs query failed: " . $e->getMessage());
                $data['items'] = [];
                $total = 0;
                $totalPages = 1;
            }
            $data['page'] = $page;
            $data['totalPages'] = $totalPages;
            $data['total'] = $total;
            $data['search'] = $search;
        }

        $this->view(self::VIEW_ADMIN, $data);
    }

    public function updateSmsSettings(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $smsEnabled = isset($_POST['sms_enabled']) && $_POST['sms_enabled'] === '1';
        $apiKey = trim($_POST['sms_api_key'] ?? '');
        $templateId = trim($_POST['sms_template_id'] ?? '');
        $lineNumber = trim($_POST['sms_line_number'] ?? '');
        $otpTtl = $_POST['sms_otp_ttl'] ?? '';
        $otpLength = $_POST['sms_otp_length'] ?? '';

        if ($smsEnabled) {
            if ($apiKey === '') {
                flash('error', 'کلید API نمی‌تواند خالی باشد.');
                redirect(self::PATH_SMS);
                return;
            }
        }

        if ($otpTtl !== '' && (!ctype_digit($otpTtl) || (int)$otpTtl < 60 || (int)$otpTtl > 600)) {
            flash('error', 'مدت اعتبار کد تأیید باید عددی بین ۶۰ تا ۶۰۰ ثانیه باشد.');
            redirect(self::PATH_SMS);
            return;
        }

        if ($otpLength !== '' && (!ctype_digit($otpLength) || (int)$otpLength < 4 || (int)$otpLength > 6)) {
            flash('error', 'طول کد تأیید باید عددی بین ۴ تا ۶ رقم باشد.');
            redirect(self::PATH_SMS);
            return;
        }

        $settingsMap = [
            'sms_enabled' => $smsEnabled ? '1' : '0',
            'sms_api_key' => sanitize($apiKey),
            'sms_template_id' => sanitize($templateId),
            'sms_line_number' => sanitize($lineNumber),
            'sms_otp_ttl' => sanitize($otpTtl ?: '180'),
            'sms_otp_length' => sanitize($otpLength ?: '5'),
        ];

        $upserts = [];
        $upsertParams = [];
        foreach ($settingsMap as $key => $value) {
            $upserts[] = '(?, ?)';
            $upsertParams[] = $key;
            $upsertParams[] = $value;
        }

        $values = implode(', ', $upserts);
        Database::query(
            "INSERT INTO settings (setting_key, setting_value) VALUES {$values} ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            $upsertParams
        );

        Settings::invalidate();
        Config::reset();
        flash('success', 'تنظیمات پیامک با موفقیت ذخیره شد.');
        redirect(self::PATH_SMS);
    }

    public function sendBulkSms(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $phones = trim($_POST['phones'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($phones)) {
            flash('error', 'شماره تلفن‌ها را وارد کنید.');
            redirect(self::PATH_SMS_TAB_SEND);
            return;
        }

        if (empty($message)) {
            flash('error', 'متن پیام را وارد کنید.');
            redirect(self::PATH_SMS_TAB_SEND);
            return;
        }

        $phoneList = array_filter(array_map('trim', preg_split('/[\n,;]+/', $phones)));
        $phoneList = array_unique($phoneList);

        if (empty($phoneList)) {
            flash('error', 'شماره تلفن معتبری یافت نشد.');
            redirect(self::PATH_SMS_TAB_SEND);
            return;
        }

        $smsService = new SmsService();
        $result = $smsService->sendBulk($message, $phoneList);

        if ($result['status']) {
            flash('success', count($phoneList) . ' پیامک با موفقیت ارسال شد.');
        } else {
            flash('error', 'خطا در ارسال پیامک: ' . ($result['message'] ?? 'خطای ناشناخته'));
        }

        redirect(self::PATH_SMS_TAB_SEND);
    }

    public function saveSmsTemplate(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $smsType = $_POST['sms_type'] ?? 'bulk';
        $variables = trim($_POST['variables'] ?? '');

        if (empty($name) || empty($body)) {
            flash('error', 'نام و متن قالب الزامی است.');
            redirect(self::PATH_SMS_TAB_TEMPLATES);
            return;
        }

        $data = [
            'name' => sanitize($name),
            'body' => sanitize($body),
            'sms_type' => in_array($smsType, ['verify', 'bulk', 'notification']) ? $smsType : 'bulk',
            'variables' => $variables ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($id > 0) {
            Database::query(
                "UPDATE sms_templates SET name = ?, body = ?, sms_type = ?, variables = ?, is_active = ? WHERE id = ?",
                [$data['name'], $data['body'], $data['sms_type'], $data['variables'], $data['is_active'], $id]
            );
            flash('success', 'قالب با موفقیت بروزرسانی شد.');
        } else {
            Database::insert('sms_templates', $data);
            flash('success', 'قالب جدید با موفقیت ایجاد شد.');
        }

        redirect(self::PATH_SMS_TAB_TEMPLATES);
    }

    public function deleteSmsTemplate(int $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        Database::query("DELETE FROM sms_templates WHERE id = ?", [$id]);
        flash('success', 'قالب حذف شد.');
        redirect(self::PATH_SMS_TAB_TEMPLATES);
    }

    public function refreshSmsCredit(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $smsService = new SmsService();
        $result = $smsService->getCredit();

        if ($result['status']) {
            $credit = $result['data'] ?? 0;
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('sms_credit_balance', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [(string) $credit]
            );
            Settings::invalidate();
            flash('success', 'موجودی پیامک بروزرسانی شد: ' . faNum($credit) . ' پیامک');
        } else {
            flash('error', 'خطا در دریافت موجودی: ' . ($result['message'] ?? 'خطای ناشناخته'));
        }

        redirect('/admin/sms?tab=credit');
    }

    private function sectionSeo(array &$data): void
    {
        $defaultSlugs = ['home', 'shop', 'blog', 'contact', 'about', 'academy'];
        foreach ($defaultSlugs as $slug) {
            Database::query(
                "INSERT IGNORE INTO seo_meta (page_slug) VALUES (?)",
                [$slug]
            );
        }

        $data['seoPages'] = Database::fetchAll(
            "SELECT * FROM seo_meta WHERE page_slug IN ('home','shop','blog','contact','about','academy') ORDER BY FIELD(page_slug, 'home','shop','blog','contact','about','academy')"
        );
        $data['pageLabels'] = [
            'home'    => 'صفحه اصلی',
            'shop'    => 'فروشگاه',
            'blog'    => 'وبلاگ',
            'contact' => 'تماس با ما',
            'about'   => 'درباره ما',
            'academy' => 'آکادمی',
        ];
        $data['globalSeo'] = [
            'meta_title'       => Settings::get('meta_title', ''),
            'meta_description' => Settings::get('meta_description', ''),
            'og_title'         => Settings::get('og_title', ''),
            'og_description'   => Settings::get('og_description', ''),
            'og_image'         => Settings::get('og_image', ''),
            'title_prefix'     => Settings::get('title_prefix', ''),
            'title_suffix'     => Settings::get('title_suffix', ''),
            'default_robots'   => Settings::get('default_robots', 'index,follow'),
            'robots_txt'       => Settings::get('robots_txt', ''),
        ];
        $this->view(self::VIEW_ADMIN, $data);
    }

    private function saveSeo(): void
    {
        $globalSeo = $_POST['seo_global'] ?? [];
        $seoData   = $_POST['seo'] ?? [];

        // ——— Validate global SEO fields ———
        if (!empty($globalSeo['meta_title']) && mb_strlen($globalSeo['meta_title']) > 255) {
            flash('error', 'عنوان متا پیش‌فرض حداکثر ۲۵۵ کاراکتر می‌تواند باشد.');
            redirect(self::PATH_SEO);
            return;
        }
        if (!empty($globalSeo['og_title']) && mb_strlen($globalSeo['og_title']) > 255) {
            flash('error', 'عنوان Open Graph پیش‌فرض حداکثر ۲۵۵ کاراکتر می‌تواند باشد.');
            redirect(self::PATH_SEO);
            return;
        }
        if (!empty($globalSeo['title_prefix']) && mb_strlen($globalSeo['title_prefix']) > 100) {
            flash('error', 'پیشوند عنوان حداکثر ۱۰۰ کاراکتر می‌تواند باشد.');
            redirect(self::PATH_SEO);
            return;
        }
        if (!empty($globalSeo['title_suffix']) && mb_strlen($globalSeo['title_suffix']) > 100) {
            flash('error', 'پسوند عنوان حداکثر ۱۰۰ کاراکتر می‌تواند باشد.');
            redirect(self::PATH_SEO);
            return;
        }

        // ——— Save global SEO to settings table ———
        $globalFields = ['meta_title', 'meta_description', 'og_title', 'og_description',
                         'title_prefix', 'title_suffix', 'default_robots'];
        $upserts = [];
        $upsertParams = [];
        foreach ($globalFields as $field) {
            $value = isset($globalSeo[$field]) ? sanitize($globalSeo[$field]) : '';
            $upserts[] = self::PLACEHOLDER_PAIR;
            $upsertParams[] = $field;
            $upsertParams[] = $value;
        }

        if (isset($_POST['delete_seo_global_og_image']) && $_POST['delete_seo_global_og_image'] === '1') {
            $old = Settings::get('og_image');
            if ($old && is_file(__DIR__ . '/../../public/assets/images/' . basename($old))) {
                @unlink(__DIR__ . '/../../public/assets/images/' . basename($old));
            }
            $upserts[] = self::PLACEHOLDER_PAIR;
            $upsertParams[] = 'og_image';
            $upsertParams[] = '';
        } elseif (!empty($_FILES['seo_global_og_image']['name']) && $_FILES['seo_global_og_image']['error'] === UPLOAD_ERR_OK) {
            $old = Settings::get('og_image');
            $uploaded = FileUploader::upload($_FILES['seo_global_og_image'], 'setting', $old);
            if ($uploaded) {
                $upserts[] = self::PLACEHOLDER_PAIR;
                $upsertParams[] = 'og_image';
                $upsertParams[] = $uploaded;
            }
        } elseif (!empty($globalSeo['og_image'])) {
            $upserts[] = self::PLACEHOLDER_PAIR;
            $upsertParams[] = 'og_image';
            $upsertParams[] = sanitize($globalSeo['og_image']);
        }

        // ——— Save robots.txt content (separate because newlines must be preserved) ———
        if (isset($globalSeo['robots_txt'])) {
            $robotsTxt = str_replace(["<?php", "<?", "?>"], "", $globalSeo['robots_txt']);
            $robotsTxt = str_replace("\r\n", "\n", $robotsTxt);
            $robotsTxt = trim($robotsTxt);
            $robotsTxt = mb_substr($robotsTxt, 0, 8192);
            $upserts[] = self::PLACEHOLDER_PAIR;
            $upsertParams[] = 'robots_txt';
            $upsertParams[] = $robotsTxt;
        }

        if (!empty($upserts)) {
            $values = implode(', ', $upserts);
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES {$values} ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                $upsertParams
            );
            Cache::forget('robots.txt');
            Settings::invalidate();
        }

        // ——— Save page-specific SEO to seo_meta table ———
        $pageFields = ['meta_title', 'meta_description', 'canonical_url',
                       'og_title', 'og_description', 'og_image', 'robots'];
        foreach ($seoData as $pageSlug => $fields) {
            $pageSlug = sanitize($pageSlug);
            $updateData = [];
            foreach ($pageFields as $f) {
                if (isset($fields[$f])) {
                    $val = sanitize($fields[$f]);
                    if ($val !== '') {
                        $updateData[$f] = $val;
                    }
                }
            }
            if (!empty($updateData)) {
                $existing = Database::fetch("SELECT id FROM seo_meta WHERE page_slug = ?", [$pageSlug]);
                if ($existing) {
                    $sets = [];
                    $params = [];
                    foreach ($updateData as $k => $v) {
                        $sets[] = "{$k} = ?";
                        $params[] = $v;
                    }
                    $params[] = $existing['id'];
                    Database::query(
                        "UPDATE seo_meta SET " . implode(', ', $sets) . " WHERE id = ?",
                        $params
                    );
                } else {
                    $updateData['page_slug'] = $pageSlug;
                    Database::insert('seo_meta', $updateData);
                }
            }
        }

        Cache::flushByTag('homepage');
        Cache::flushByTag('blog');
        Settings::invalidate();
        flash('success', 'تنظیمات سئو با موفقیت ذخیره شد.');
        redirect(self::PATH_SEO);
    }
}
