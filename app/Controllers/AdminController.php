<?php

class AdminController extends BaseController
{
    private function requireAdmin(): void
    {
        Auth::requireAdmin();
    }

    public function loginForm(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            redirect('/admin');
        }
        $this->viewRaw('admin/login');
    }

    public function doLogin(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            redirect('/admin');
        }
        $this->verifyCsrf();
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->redirectWithErrors('/admin/login', ['admin' => 'نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE name = ? AND role = 'admin'", [$username]);

        if (!$user || !Auth::verify($password, $user['password'])) {
            $this->redirectWithErrors('/admin/login', ['admin' => 'نام کاربری یا رمز عبور اشتباه است.']);
            return;
        }

        Auth::login($user['id'], $user);
        flash('success', 'خوش آمدید ' . e($user['name']));
        redirect('/admin');
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $stats = [
            'users' => Database::fetch("SELECT COUNT(*) as cnt FROM users")['cnt'],
            'appointments' => Database::fetch("SELECT COUNT(*) as cnt FROM appointments")['cnt'],
            'orders' => Database::fetch("SELECT COUNT(*) as cnt FROM orders")['cnt'],
            'revenue' => Database::fetch("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status != 'cancelled'")['total'],
        ];

        $recentAppointments = Database::fetchAll(
            "SELECT a.*, u.name as user_name, u.family as user_family, s.title as service_title
             FROM appointments a
             JOIN users u ON a.user_id = u.id
             LEFT JOIN services s ON a.service_id = s.id
             ORDER BY a.created_at DESC LIMIT 10"
        );

        $recentOrders = Database::fetchAll(
            "SELECT o.*, u.name as user_name, u.family as user_family
             FROM orders o
             JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC LIMIT 5"
        );

        $this->view('admin/index', compact('stats', 'recentAppointments', 'recentOrders') + ['section' => 'dashboard']);
    }

    public function section(string $section): void
    {
        $this->requireAdmin();
        $validSections = ['services', 'artists', 'appointments', 'products', 'users', 'courses', 'enrollments', 'testimonials', 'transactions', 'settings', 'hair-models', 'tutorials', 'orders', 'newsletter'];

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
        ];

        $columnsMap = $this->getColumns($section);
        $data['columns'] = $columnsMap;

        $table = $tableMap[$section] ?? null;
        if ($table) {
            $data['items'] = Database::fetchAll("SELECT * FROM {$table} ORDER BY id DESC");
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
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status'],
                ['key' => 'notes', 'label' => 'یادداشت', 'type' => 'textarea'],
            ],
            'products' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'stock', 'label' => 'موجودی', 'type' => 'text'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'users' => [
                ['key' => 'avatar', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'family', 'label' => 'نام خانوادگی', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'email', 'label' => 'ایمیل', 'type' => 'text'],
                ['key' => 'role', 'label' => 'نقش', 'type' => 'select', 'options' => ['user', 'admin']],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'text'],
                ['key' => 'points', 'label' => 'امتیاز', 'type' => 'text'],
                ['key' => 'wallet', 'label' => 'کیف پول', 'type' => 'price'],
            ],
            'courses' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'teacher', 'label' => 'مدرس', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'text'],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'description', 'label' => 'توضیحات', 'type' => 'textarea'],
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
            ],
            'enrollments' => [
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'course_title', 'label' => 'دوره', 'type' => 'text'],
                ['key' => 'progress', 'label' => 'پیشرفت %', 'type' => 'text'],
                ['key' => 'enrolled_at', 'label' => 'تاریخ ثبت‌نام', 'type' => 'text'],
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
                ['key' => 'is_active', 'label' => 'فعال', 'type' => 'boolean'],
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
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status'],
                ['key' => 'created_at', 'label' => 'تاریخ', 'type' => 'text'],
            ],
            'settings' => [
                ['key' => 'setting_key', 'label' => 'کلید', 'type' => 'text'],
                ['key' => 'setting_value', 'label' => 'مقدار', 'type' => 'textarea'],
            ],
        ];
        return $all[$section] ?? [['key' => 'id', 'label' => 'شناسه', 'type' => 'text']];
    }

    public function save(string $section): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

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

        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize($_POST[$field]);
            }
        }

        if ($section === 'settings') {
            $this->updateSettings();
            return;
        }

        if ($section === 'orders') {
            if ($id && isset($data['status'])) {
                Database::update($table, ['status' => $data['status']], 'id = :id', ['id' => $id]);
                flash('success', 'وضعیت سفارش به‌روزرسانی شد.');
            }
            redirect('/admin/orders');
            return;
        }

        if ($section === 'appointments') {
            if ($id) {
                $updateData = [];
                if (isset($data['status'])) $updateData['status'] = $data['status'];
                if (isset($data['notes'])) $updateData['notes'] = $data['notes'];
                if (!empty($updateData)) {
                    Database::update($table, $updateData, 'id = :id', ['id' => $id]);
                    flash('success', 'نوبت با موفقیت به‌روزرسانی شد.');
                }
            }
            redirect('/admin/appointments');
            return;
        }

        if ($section === 'enrollments') {
            if ($id && isset($data['progress'])) {
                Database::update($table, ['progress' => (int)$data['progress']], 'id = :id', ['id' => $id]);
                flash('success', 'پیشرفت دوره به‌روزرسانی شد.');
            }
            redirect('/admin/enrollments');
            return;
        }

        if ($section === 'transactions' || $section === 'newsletter') {
            redirect('/admin/' . $section);
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
                $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar_' . $id);
                if ($uploaded) {
                    $data['avatar'] = $uploaded;
                }
            }
            if (!empty($data)) {
                Database::update($table, $data, 'id = :id', ['id' => $id]);
            }
            flash('success', 'کاربر با موفقیت ذخیره شد.');
            redirect('/admin/users');
            return;
        }

        if ($section === 'artists') {
            if (!empty($_FILES['avatar']['name'])) {
                $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar');
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
            flash('success', 'آرایشگر با موفقیت ذخیره شد.');
            redirect('/admin/artists');
            return;
        }

        if (!empty($_FILES['image']['name'])) {
            $uploaded = FileUploader::upload($_FILES['image'], 'product');
            if ($uploaded) {
                $data['image'] = $uploaded;
            }
        }
        if (!empty($_FILES['avatar']['name'])) {
            $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar');
            if ($uploaded) {
                $data['avatar'] = $uploaded;
            }
        }

        if ($id) {
            Database::update($table, $data, 'id = :id', ['id' => $id]);
        } else {
            Database::insert($table, $data);
        }

        flash('success', 'با موفقیت ذخیره شد.');
        redirect('/admin/' . $section);
    }

    public function delete(string $section, int $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $table = $this->sectionToTable($section);

        if ($table) {
            Database::delete($table, 'id = ?', [$id]);
        }

        flash('success', 'با موفقیت حذف شد.');
        redirect('/admin/' . $section);
    }

    public function updateSettings(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $settingKey = substr($key, 8);
                $value = sanitize($value);
                $existing = Database::fetch("SELECT id FROM settings WHERE setting_key = ?", [$settingKey]);
                if ($existing) {
                    Database::update('settings', ['setting_value' => $value], 'setting_key = :k', ['k' => $settingKey]);
                } else {
                    Database::insert('settings', ['setting_key' => $settingKey, 'setting_value' => $value]);
                }
            }
        }

        flash('success', 'تنظیمات با موفقیت ذخیره شد.');
        redirect('/admin/settings');
    }

    // Specific section handlers
    private function sectionAppointments(array &$data): void
    {
        $data['items'] = Database::fetchAll(
            "SELECT a.*, u.name as user_name, u.family as user_family, s.title as service_title, ar.name as artist_name
             FROM appointments a
             JOIN users u ON a.user_id = u.id
             LEFT JOIN services s ON a.service_id = s.id
             LEFT JOIN artists ar ON a.artist_id = ar.id
             ORDER BY a.appointment_date DESC"
        );
        $data['columns'] = $this->getColumns('appointments');
        $this->view('admin/index', $data);
    }

    private function sectionUsers(array &$data): void
    {
        $data['items'] = Database::fetchAll("SELECT id, name, family, phone, email, avatar, role, level, points, wallet, created_at FROM users ORDER BY id DESC");
        $data['columns'] = $this->getColumns('users');
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
        $data['items'] = Database::fetchAll(
            "SELECT o.*, u.name as user_name, u.family as user_family
             FROM orders o
             JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC"
        );
        $data['columns'] = $this->getColumns('orders');
        $this->view('admin/index', $data);
    }

    private function sectionEnrollments(array &$data): void
    {
        $data['items'] = Database::fetchAll(
            "SELECT ce.*, u.name as user_name, u.family as user_family, c.title as course_title
             FROM course_enrollments ce
             JOIN users u ON ce.user_id = u.id
             JOIN courses c ON ce.course_id = c.id
             ORDER BY ce.enrolled_at DESC"
        );
        $data['columns'] = $this->getColumns('enrollments');
        $this->view('admin/index', $data);
    }

    private function sectionTransactions(array &$data): void
    {
        $data['items'] = Database::fetchAll(
            "SELECT t.*, u.name as user_name, u.family as user_family
             FROM transactions t
             JOIN users u ON t.user_id = u.id
             ORDER BY t.created_at DESC"
        );
        $data['columns'] = $this->getColumns('transactions');
        $this->view('admin/index', $data);
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
        ];
        return $map[$section] ?? null;
    }
}
