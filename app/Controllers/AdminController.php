<?php

class AdminController extends BaseController
{
    private function requireAdmin(): void
    {
        Auth::requireAdmin();
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

        $recentOrders = Database::fetchAll("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

        $this->view('admin/index', compact('stats', 'recentAppointments', 'recentOrders') + ['section' => 'dashboard']);
    }

    public function section(string $section): void
    {
        $this->requireAdmin();
        $validSections = ['services', 'artists', 'appointments', 'products', 'users', 'courses', 'testimonials', 'settings', 'hair-models', 'tutorials'];

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
            'testimonials' => 'testimonials',
            'hair-models' => 'hair_models',
            'tutorials' => 'tutorials',
        ];

        $columnsMap = $this->getColumns($section);
        $data['columns'] = $columnsMap;

        $table = $tableMap[$section] ?? null;
        if ($table) {
            $data['items'] = Database::fetchAll("SELECT * FROM {$table} ORDER BY id DESC");
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
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
            ],
            'artists' => [
                ['key' => 'avatar', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'specialty', 'label' => 'تخصص', 'type' => 'text'],
            ],
            'appointments' => [
                ['key' => 'user_name', 'label' => 'کاربر', 'type' => 'text'],
                ['key' => 'service_title', 'label' => 'خدمت', 'type' => 'text'],
                ['key' => 'appointment_date', 'label' => 'تاریخ', 'type' => 'text'],
                ['key' => 'appointment_time', 'label' => 'ساعت', 'type' => 'text'],
                ['key' => 'status', 'label' => 'وضعیت', 'type' => 'status'],
            ],
            'products' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'price', 'label' => 'قیمت', 'type' => 'price', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
                ['key' => 'stock', 'label' => 'موجودی', 'type' => 'text'],
            ],
            'users' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text'],
                ['key' => 'family', 'label' => 'نام خانوادگی', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'تلفن', 'type' => 'text'],
                ['key' => 'role', 'label' => 'نقش', 'type' => 'status'],
                ['key' => 'level', 'label' => 'سطح', 'type' => 'text'],
                ['key' => 'points', 'label' => 'امتیاز', 'type' => 'text'],
            ],
            'courses' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'teacher', 'label' => 'مدرس', 'type' => 'text'],
                ['key' => 'type', 'label' => 'نوع', 'type' => 'text'],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
            ],
            'testimonials' => [
                ['key' => 'name', 'label' => 'نام', 'type' => 'text', 'required' => true],
                ['key' => 'role', 'label' => 'نقش', 'type' => 'text'],
                ['key' => 'rating', 'label' => 'امتیاز', 'type' => 'text'],
            ],
            'hair-models' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'category', 'label' => 'دسته', 'type' => 'text'],
            ],
            'tutorials' => [
                ['key' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                ['key' => 'title', 'label' => 'عنوان', 'type' => 'text', 'required' => true],
                ['key' => 'duration', 'label' => 'مدت', 'type' => 'text'],
                ['key' => 'views', 'label' => 'بازدید', 'type' => 'text'],
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

        if (!empty($_FILES['image']['name'])) {
            $uploaded = $this->uploadFile($_FILES['image']);
            if ($uploaded) {
                $data['image'] = $uploaded;
            }
        }
        if (!empty($_FILES['avatar']['name'])) {
            $uploaded = $this->uploadFile($_FILES['avatar']);
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
        $data['items'] = Database::fetchAll("SELECT id, name, family, phone, email, role, level, points, wallet, created_at FROM users ORDER BY id DESC");
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

    private function sectionToTable(string $section): ?string
    {
        $map = [
            'services' => 'services',
            'artists' => 'artists',
            'products' => 'products',
            'courses' => 'courses',
            'testimonials' => 'testimonials',
            'hair-models' => 'hair_models',
            'tutorials' => 'tutorials',
        ];
        return $map[$section] ?? null;
    }

    private function uploadFile(array $file): ?string
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt) || $file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMime)) {
            return null;
        }

        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = __DIR__ . '/../../public/assets/images/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $filename;
        }

        return null;
    }
}
