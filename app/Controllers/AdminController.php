<?php

class AdminController extends BaseController
{
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
            redirect('/admin');
        }
        $captchaEnabled = Captcha::isEnabled('admin');
        if ($captchaEnabled) {
            $_SESSION['captcha_question'] = Captcha::store();
        }
        $captchaQuestion = $_SESSION['captcha_question'] ?? '';
        $this->viewRaw('admin/login', compact('captchaQuestion', 'captchaEnabled'));
    }

    public function doLogin(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            redirect('/admin');
        }
        $this->verifyCsrf();

        if (Captcha::isEnabled('admin') && !Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/admin/login', ['admin' => 'کد امنیتی اشتباه است.']);
            return;
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/admin/login', ['admin' => 'نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        RateLimiter::cleanup();
        if (RateLimiter::isLocked('admin_' . $username)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/admin/login', ['rate_limit' => 'تعداد تلاش‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
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
            $this->redirectWithErrors('/admin/login', ['admin' => $msg]);
            return;
        }

        RateLimiter::recordAttempt('admin_' . $username, true);
        Auth::login($user['id'], $user);
        flash('success', 'خوش آمدید ' . e($user['name']));
        redirect('/admin');
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

        $this->view('admin/index', compact('stats', 'recentAppointments', 'recentOrders') + ['section' => 'dashboard']);
    }

    public function section(string $section): void
    {
        $this->requireAdmin();
        $validSections = ['services', 'artists', 'appointments', 'products', 'users', 'courses', 'enrollments', 'testimonials', 'transactions', 'settings', 'captcha', 'hair-models', 'tutorials', 'orders', 'newsletter', 'coupons', 'contact-messages', 'blog', 'reviews', 'blog-comments', 'product-categories', 'product-brands', 'gallery'];

        if (!in_array($section, $validSections)) {
            redirect('/admin');
        }

        $data = ['section' => $section];
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
        ];

        $columnsMap = $this->getColumns($section);
        array_walk($columnsMap, fn(&$col) => $col['required'] = $col['required'] ?? false);
        $data['columns'] = $columnsMap;

        if ($section === 'products') {
            $catOptions = Database::fetchAll("SELECT name FROM product_categories WHERE is_active = 1 ORDER BY name");
            $brandOptions = Database::fetchAll("SELECT name FROM product_brands WHERE is_active = 1 ORDER BY name");
            foreach ($data['columns'] as &$col) {
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

        $table = $tableMap[$section] ?? null;
        if ($table) {
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
                        $searchParams[] = "%{$search}%";
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

        if ($section === 'products') {
            $productIds = array_column($data['items'] ?? [], 'id');
            $galleryData = [];
            if (!empty($productIds)) {
                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                $galleryRows = Database::fetchAll(
                    "SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order, id",
                    $productIds
                );
                foreach ($galleryRows as $gr) {
                    $galleryData[$gr['product_id']][] = $gr;
                }
            }
            $data['productGalleryJson'] = json_encode($galleryData);
        }

        if ($section === 'artists') {
            $data['allServices'] = Database::fetchAll("SELECT id, title FROM services ORDER BY id");
            $assignments = Database::fetchAll("SELECT artist_id, service_id FROM artist_services");
            $artistServices = [];
            foreach ($assignments as $as) {
                $artistServices[$as['artist_id']][] = $as['service_id'];
            }
            $data['artistServicesJson'] = json_encode($artistServices);
        }

        $this->view('admin/index', $data);
    }

    private function getColumns(string $section): array
    {
        $all = [
            'services' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'artists' => [
                ['key' => 'avatar', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'specialty', 'label' => 'تخصص', 'type' => 'text'],
                ['key' => 'bio', 'label' => 'بیوگرافی', 'type' => 'textarea'],
                ['key' => 'instagram', 'label' => 'اینستاگرام', 'type' => 'text'],
                ['key' => 'working_hours', 'label' => 'ساعت کاری', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'appointments' => [
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'service_title', 'label' => 'خدمت', 'type' => 'text'],
                ['key' => 'artist_name', 'label' => 'آرایشگر', 'type' => 'text'],
                ['key' => 'appointment_date', 'label' => 'تاریخ', 'type' => 'text'],
                ['key' => 'appointment_time', 'label' => 'ساعت', 'type' => 'text'],
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status', 'options' => ['pending', 'confirmed', 'done', 'cancelled']],
                ['key' => 'notes', 'label' => 'یادداشت', 'type' => 'textarea'],
            ],
            'products' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'old_price', 'label' => 'قیمت قبل', 'type' => 'price'],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'brand', 'label' => 'برند', 'type' => 'text'],
                ['key' => 'stock', 'label' => 'موجودی', 'type' => 'text'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'is_new', 'label' => 'جدید', 'type' => 'boolean'],
                ['key' => 'is_sale', 'label' => 'تخفیف خورده', 'type' => 'boolean'],
                ['key' => 'video_type', 'label' => 'نوع ویدیو', 'type' => 'select', 'options' => ['upload', 'youtube', 'aparat']],
                ['key' => 'video_url', 'label' => 'ویدیو (آپلود یا لینک)', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'users' => [
                ['key' => 'avatar', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'family', 'label' => 'نام خانوادگی', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'email', 'label' => 'ایمیل', 'type' => 'text'],
                ['key' => 'role', 'label' => 'نقش', 'type' => 'select', 'options' => ['user', 'admin']],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'text'],
                ['key' => 'points', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'wallet', 'label' => 'کیف پول', 'type' => 'price'],
            ],
            'courses' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'slug', 'label' => 'slug', 'type' => 'text'],
                ['key' => 'teacher', 'label' => 'مدرس', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'select', 'options' => ['online', 'offline']],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price'],
                ['key' => 'old_price', 'label' => 'قیمت قبل', 'type' => 'price'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'students', 'label' => 'دانشجو', 'type' => 'text'],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'select', 'options' => ['مبتدی', 'متوسط', 'پیشرفته', 'همه سطوح']],
                ['key' => 'is_free', 'label' => 'رایگان', 'type' => 'boolean'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'curriculum', 'label' => 'برنامه درسی (JSON)', 'type' => 'textarea'],
                ['key' => 'audience', 'label' => 'مخاطبان (JSON)', 'type' => 'textarea'],
                ['key' => 'faqs', 'label' => 'سؤالات متداول (JSON)', 'type' => 'textarea'],
                ['key' => 'reviews', 'label' => 'نظرات (JSON)', 'type' => 'textarea'],
                ['key' => 'video_type', 'label' => 'نوع ویدیو', 'type' => 'select', 'options' => ['upload', 'youtube', 'aparat']],
                ['key' => 'video_url', 'label' => 'ویدیو (آپلود یا لینک)', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'enrollments' => [
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'course_title', 'label' => 'دوره', 'type' => 'text'],
                ['key' => 'progress', 'label' => 'پیشرفت %', 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'تاریخ ثبت‌نام', 'type' => 'text'],
            ],
            'testimonials' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'role', 'label' => 'عنوان', 'type' => 'text'],
                ['key' => 'text', 'label' => 'متن نظر', 'type' => 'textarea', 'required' => true],
                ['key' => 'avatar', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'transactions' => [
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'status'],
                ['key' => 'amount', 'label' => 'مبلغ', 'type' => 'price'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
            ],
            'newsletter' => [
                ['key' => 'email', 'label' => 'ایمیل', 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'تاریخ عضویت', 'type' => 'text'],
            ],
            'hair-models' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'tutorials' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'views', 'label' => 'بازدید', 'type' => 'text'],
                ['key' => 'video_url', 'label' => 'لینک ویدیو', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'orders' => [
                ['key' => 'tracking_code', 'label' => 'کد پیگیری', 'type' => 'text'],
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'total', 'label' => 'مبلغ', 'type' => 'price'],
                ['key' => 'address', 'label' => 'آدرس', 'type' => 'text'],
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status', 'options' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled']],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
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
                ['key' => 'expires_at', 'label' => 'تاریخ انقضا', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'blog' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
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
                ['key' => 'published_at', 'label' => 'تاریخ انتشار', 'type' => 'text'],
            ],
            'reviews' => [
                ['key' => 'product_name', 'label' => 'محصول', 'type' => 'text'],
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'text', 'label' => 'متن نظر', 'type' => 'textarea'],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
            ],
            'contact-messages' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'email', 'label' => 'ایمیل', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'subject', 'label' => 'موضوع', 'type' => 'text'],
                ['key' => 'message', 'label' => 'پیام', 'type' => 'textarea'],
                ['key' => 'is_read', 'label' => 'خوانده شده', 'type' => 'boolean'],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
            ],
            'blog-comments' => [
                ['key' => 'post_title', 'label' => 'پست', 'type' => 'text'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'text', 'label' => 'متن نظر', 'type' => 'textarea'],
                ['key' => 'likes', 'label' => 'لایک', 'type' => 'text'],
                ['key' => 'is_approved', 'label' => 'تأیید شده', 'type' => 'boolean'],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
            ],
            'product-categories' => [
                ['key' => 'name', 'label' => 'نام دسته', 'type' => 'text', 'required' => true],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'product-brands' => [
                ['key' => 'name', 'label' => 'نام برند', 'type' => 'text', 'required' => true],
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
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if ($existing) {
            Database::update('media', [
                'source_id' => $sourceId,
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $fullPath = __DIR__ . '/../../public/' . $filepath;
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

    private function clearCache(string $section): void
    {
        Settings::invalidate();

        $sectionToTags = [
            'services' => ['homepage', 'booking'],
            'artists' => ['homepage', 'booking'],
            'products' => ['products'],
            'hair-models' => ['homepage'],
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

        if (in_array($section, ['products', 'services', 'blog', 'courses', 'settings', 'captcha', 'gallery'])) {
            Cache::bumpVersion();
        }
    }

    public function save(string $section): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        if ($section === 'settings') {
            $this->updateSettings();
            return;
        }

        if ($section === 'captcha') {
            $this->updateCaptchaSettings();
            return;
        }

        if ($section === 'gallery') {
            if (!empty($_FILES['file']['name'])) {
                $uploadDir = __DIR__ . '/../../public/assets/uploads/gallery';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedImageMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $allowedVideoMime = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['file']['tmp_name']);
                $isImage = in_array($mimeType, $allowedImageMime);
                $isVideo = in_array($mimeType, $allowedVideoMime);
                if (!$isImage && !$isVideo) {
                    flash('error', 'نوع فایل مجاز نیست. فقط تصاویر (jpg, png, gif, webp) و ویدیو (mp4, webm, ogg, mov)');
                    redirect('/admin/gallery');
                    return;
                }
                $ext = match ($mimeType) {
                    'image/jpeg' => '.jpg',
                    'image/png' => '.png',
                    'image/gif' => '.gif',
                    'image/webp' => '.webp',
                    'video/mp4' => '.mp4',
                    'video/webm' => '.webm',
                    'video/ogg' => '.ogv',
                    'video/quicktime' => '.mov',
                    default => '.bin',
                };
                $originalName = $_FILES['file']['name'];
                $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
                $dest = $uploadDir . '/' . $filename;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                    $size = filesize($dest);
                    $filepath = 'assets/uploads/gallery/' . $filename;
                    $altText = sanitize($_POST['alt_text'] ?? $originalName);
                    Database::insert('media', [
                        'filepath' => $filepath,
                        'original_name' => $originalName,
                        'type' => $isImage ? 'image' : 'video',
                        'mime_type' => $mimeType,
                        'size' => $size,
                        'alt_text' => $altText,
                        'source_type' => 'direct',
                        'uploaded_by' => Auth::id(),
                    ]);
                    $this->clearCache($section);
                    flash('success', 'فایل با موفقیت آپلود شد.');
                } else {
                    flash('error', 'خطا در آپلود فایل.');
                }
            } else {
                flash('error', 'فایلی انتخاب نشده است.');
            }
            redirect('/admin/gallery');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $table = $this->sectionToTable($section);

        if (!$table) {
            redirect('/admin');
        }

        $allowedFields = array_column($this->getColumns($section), 'key');
        $allowedFields[] = 'description';
        $allowedFields[] = 'bio';
        $allowedFields[] = 'text';
        $allowedFields[] = 'notes';
        $allowedFields[] = 'instagram';

        $rawFields = ['description', 'bio', 'text', 'notes', 'content'];

        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = in_array($field, $rawFields) ? $_POST[$field] : sanitize($_POST[$field]);
            }
        }

        if ($section === 'orders') {
            if ($id && isset($data['status'])) {
                Database::update($table, ['status' => $data['status']], 'id = :id', ['id' => $id]);
                flash('success', 'وضعیت سفارش به‌روزرسانی شد.');
            }
            $this->clearCache($section);
            redirect('/admin/orders');
            return;
        }

        if ($section === 'blog-comments') {
            if ($id && isset($data['is_approved'])) {
                Database::update($table, ['is_approved' => (int)$data['is_approved']], 'id = :id', ['id' => $id]);
                flash('success', 'وضعیت نظر به‌روزرسانی شد.');
            }
            $this->clearCache($section);
            redirect('/admin/blog-comments');
            return;
        }

        if ($section === 'appointments') {
            if ($id) {
                $updateData = [];
                if (isset($data['status'])) {
                    $updateData['status'] = $data['status'];
                }
                if (isset($data['notes'])) {
                    $updateData['notes'] = $data['notes'];
                }
                if (!empty($updateData)) {
                    Database::update($table, $updateData, 'id = :id', ['id' => $id]);
                    flash('success', 'نوبت با موفقیت به‌روزرسانی شد.');
                }
            }
            $this->clearCache($section);
            redirect('/admin/appointments');
            return;
        }

        if ($section === 'enrollments') {
            if ($id && isset($data['progress'])) {
                Database::update($table, ['progress' => (int)$data['progress']], 'id = :id', ['id' => $id]);
                flash('success', 'پیشرفت دوره به‌روزرسانی شد.');
            }
            $this->clearCache($section);
            redirect('/admin/enrollments');
            return;
        }

        if ($section === 'users') {
            if (!$id) {
                flash('error', 'ثبت کاربر جدید از طریق پنل ادمین پشتیبانی نمی‌شود.');
                redirect('/admin/users');
                return;
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
                Database::update($table, $data, 'id = :id', ['id' => $id]);
            }
            $this->clearCache($section);
            flash('success', 'کاربر با موفقیت ذخیره شد.');
            redirect('/admin/users');
            return;
        }

        if ($section === 'artists') {
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
                    Database::update($table, $data, 'id = :id', ['id' => $id]);
                } else {
                    $id = Database::insert($table, $data);
                }
                $serviceIds = array_map('intval', $_POST['services'] ?? []);
                Database::query("DELETE FROM artist_services WHERE artist_id = ?", [$id]);
                foreach ($serviceIds as $svcId) {
                    if ($svcId > 0) {
                        Database::query(
                            "INSERT INTO artist_services (artist_id, service_id) VALUES (?, ?)",
                            [$id, $svcId]
                        );
                    }
                }
                Database::commit();
            } catch (Throwable $e) {
                Database::rollback();
                flash('error', 'خطا در ذخیره آرایشگر: ' . $e->getMessage());
                redirect('/admin/artists');
                return;
            }
            $this->clearCache($section);
            flash('success', 'آرایشگر با موفقیت ذخیره شد.');
            redirect('/admin/artists');
            return;
        }

        if (!empty($_FILES['image']['name'])) {
            $oldImage = null;
            if ($id) {
                $row = Database::fetch("SELECT image FROM {$table} WHERE id = ?", [$id]);
                if ($row) {
                    $oldImage = $row['image'];
                }
            }
            $uploaded = FileUploader::upload($_FILES['image'], 'product', $oldImage);
            if ($uploaded) {
                $data['image'] = $uploaded;
            }
        }
        if (!empty($_FILES['avatar']['name'])) {
            $oldAvatar = null;
            if ($id) {
                $row = Database::fetch("SELECT avatar FROM {$table} WHERE id = ?", [$id]);
                if ($row) {
                    $oldAvatar = $row['avatar'];
                }
            }
            $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar', $oldAvatar);
            if ($uploaded) {
                $data['avatar'] = $uploaded;
            }
        }

        if ($section === 'products') {
            $videoType = $_POST['video_type'] ?? 'upload';
            if (!empty($_FILES['video_url']['name'])) {
                $videoAllowed = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['video_url']['tmp_name']);
                if (in_array($mimeType, $videoAllowed)) {
                    $ext = match ($mimeType) {
                        'video/mp4' => '.mp4',
                        'video/webm' => '.webm',
                        'video/ogg' => '.ogv',
                        'video/quicktime' => '.mov',
                        default => '.mp4',
                    };
                    $name = 'product_video_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
                    $uploadDir = __DIR__ . '/../../public/assets/uploads/videos';
                    $dest = $uploadDir . '/' . $name;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
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

        if ($section === 'courses') {
            $videoType = $_POST['video_type'] ?? 'upload';
            if (!empty($_FILES['video_url']['name'])) {
                $videoAllowed = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['video_url']['tmp_name']);
                if (in_array($mimeType, $videoAllowed)) {
                    $ext = match ($mimeType) {
                        'video/mp4' => '.mp4',
                        'video/webm' => '.webm',
                        'video/ogg' => '.ogv',
                        'video/quicktime' => '.mov',
                        default => '.mp4',
                    };
                    $name = 'course_video_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
                    $uploadDir = __DIR__ . '/../../public/assets/uploads/videos';
                    $dest = $uploadDir . '/' . $name;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
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

        if ($section === 'blog' && isset($_POST['content'])) {
            $data['content'] = $_POST['content'];
        }

        if ($section === 'blog' && empty($data['slug']) && !empty($data['title'])) {
            $baseSlug = slugify($data['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Database::fetch("SELECT id FROM blog_posts WHERE slug = ? AND id != ?", [$slug, $id ?? 0])) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($section === 'blog' && $id) {
            $existing = Database::fetch("SELECT image FROM blog_posts WHERE id = ?", [$id]);
            if (!empty($_FILES['image']['name'])) {
                $uploaded = FileUploader::upload($_FILES['image'], 'blog');
                if ($uploaded) {
                    $data['image'] = $uploaded;
                }
            } elseif ($existing && empty($data['image'])) {
                $data['image'] = $existing['image'];
            }
        }

        if ($id) {
            Database::update($table, $data, 'id = :id', ['id' => $id]);
        } else {
            $id = Database::insert($table, $data);
        }

        if ($section === 'courses') {
            if (!empty($data['video_url']) && $data['video_type'] === 'upload') {
                $this->syncMedia($data['video_url'], $_FILES['video_url']['name'] ?? basename($data['video_url']), 'video', 'course_video', $id);
            }
        }

        if ($section === 'products') {
            if (!empty($data['image'])) {
                $this->syncMedia('assets/images/' . $data['image'], $data['image'], 'image', 'product_image', $id);
            }
            if (!empty($data['video_url']) && $data['video_type'] === 'upload') {
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
                        Database::delete('product_images', 'id = ?', [$did]);
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

        $this->clearCache($section);
        flash('success', 'با موفقیت ذخیره شد.');
        redirect('/admin/' . $section);
    }

    public function delete(string $section, int $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
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
                Database::delete('media', 'id = ?', [$id]);
                if (in_array($media['source_type'], ['product_image', 'product_gallery', 'product_video'])) {
                    Cache::flushByTag('products');
                }
                if (in_array($media['source_type'], ['course_video'])) {
                    Cache::flushByTag('academy');
                }
            }
        }

        if ($table) {
            Database::delete($table, 'id = ?', [$id]);
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
                $upserts[] = '(?, ?)';
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

        $htmlKeys = ['about_content', 'contact_map_location'];

        $upserts = [];
        $upsertParams = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $settingKey = substr($key, 8);
                $value = in_array($settingKey, $htmlKeys) ? $value : sanitize($value);
                $upserts[] = '(?, ?)';
                $upsertParams[] = $settingKey;
                $upsertParams[] = $value;
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
        Cache::flushByTag('homepage');
        Cache::forget('home_data');
        flash('success', 'تنظیمات با موفقیت ذخیره شد.');
        redirect('/admin/settings');
    }

    // Specific section handlers
    private function sectionAppointments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR s.title LIKE ? OR ar.name LIKE ? OR a.status LIKE ?)';
            $searchParams = array_fill(0, 5, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionUsers(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (name LIKE ? OR family LIKE ? OR phone LIKE ? OR email LIKE ?)';
            $searchParams = array_fill(0, 4, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionCaptcha(array &$data): void
    {
        $rows = Database::fetchAll("SELECT * FROM settings WHERE setting_key LIKE 'captcha_%' ORDER BY id");
        $data['captcha_settings'] = [];
        foreach ($rows as $row) {
            $data['captcha_settings'][$row['setting_key']] = $row['setting_value'];
        }
        $this->view('admin/index', $data);
    }

    private function sectionSettings(array &$data): void
    {
        $rows = Database::fetchAll("SELECT * FROM settings ORDER BY id");
        $data['settings'] = [];
        foreach ($rows as $row) {
            $data['settings'][$row['setting_key']] = $row['setting_value'];
        }
        $data['columns'] = $this->getColumns('settings');
        $this->view('admin/index', $data);
    }

    private function sectionOrders(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (o.tracking_code LIKE ? OR u.name LIKE ? OR u.family LIKE ? OR o.status LIKE ? OR o.total LIKE ? OR EXISTS (SELECT 1 FROM order_items oi2 WHERE oi2.order_id = o.id AND oi2.product_name LIKE ?))';
            $searchParams = array_fill(0, 6, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionEnrollments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR c.title LIKE ?)';
            $searchParams = array_fill(0, 3, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionTransactions(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' AND (u.name LIKE ? OR u.family LIKE ? OR t.type LIKE ? OR t.description LIKE ?)';
            $searchParams = array_fill(0, 4, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionCoupons(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE code LIKE ?';
            $searchParams = ["%{$search}%"];
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
        $this->view('admin/index', $data);
    }

    private function sectionBlog(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (title LIKE ? OR category LIKE ? OR author LIKE ? OR tags LIKE ?)';
            $searchParams = array_fill(0, 4, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionReviews(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (r.user_name LIKE ? OR p.name LIKE ?)';
            $searchParams = array_fill(0, 2, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionContactMessages(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)';
            $searchParams = array_fill(0, 5, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionBlogComments(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $searchWhere = '';
        $searchParams = [];
        if ($search !== '') {
            $searchWhere = ' WHERE (bc.name LIKE ? OR bc.text LIKE ? OR bp.title LIKE ?)';
            $searchParams = array_fill(0, 3, "%{$search}%");
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
        $this->view('admin/index', $data);
    }

    private function sectionGallery(array &$data): void
    {
        $search = trim($_GET['s'] ?? '');
        $filter = trim($_GET['filter'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $cacheKey = 'gallery_page_' . $page . '_' . $filter . '_' . md5($search);

        $paged = Cache::remember($cacheKey, Config::get('cache.ttl.admin', 300), function () use ($search, $filter) {
            $where = 'WHERE 1=1';
            $params = [];

            if ($search !== '') {
                $where .= ' AND (original_name LIKE ? OR alt_text LIKE ?)';
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            if ($filter === 'image') {
                $where .= " AND type = 'image'";
            } elseif ($filter === 'video') {
                $where .= " AND type = 'video'";
            }

            return $this->paginate(
                "SELECT * FROM media {$where} ORDER BY id DESC",
                "SELECT COUNT(*) as cnt FROM media {$where}",
                $params
            );
        }, ['gallery']);
        $data['items'] = $paged['items'];
        $data['page'] = $paged['page'];
        $data['totalPages'] = $paged['totalPages'];
        $data['total'] = $paged['total'];
        $data['search'] = $search;
        $data['filter'] = $filter;
        $this->view('admin/index', $data);
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
            redirect('/admin/settings');
            return;
        }

        if ($new !== $confirm) {
            flash('error', 'رمز عبور جدید و تکرار آن مطابقت ندارند.');
            redirect('/admin/settings');
            return;
        }

        if (strlen($new) < 6) {
            flash('error', 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.');
            redirect('/admin/settings');
            return;
        }

        $user = Database::fetch("SELECT password FROM users WHERE id = ?", [Auth::id()]);
        if (!Auth::verify($current, $user['password'])) {
            flash('error', 'رمز عبور فعلی اشتباه است.');
            redirect('/admin/settings');
            return;
        }

        Database::update('users', ['password' => Auth::hash($new)], 'id = :id', ['id' => Auth::id()]);
        flash('success', 'رمز عبور با موفقیت تغییر یافت.');
        redirect('/admin/settings');
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
        ];
        return $map[$section] ?? null;
    }
}
