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
            'services' => Database::fetch("SELECT COUNT(*) as cnt FROM services")['cnt'],
            'appointments_today' => Database::fetch("SELECT COUNT(*) as cnt FROM appointments WHERE appointment_date = CURDATE()")['cnt'],
            'products' => Database::fetch("SELECT COUNT(*) as cnt FROM products")['cnt'],
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

        $this->view('admin/index', compact('stats', 'recentAppointments', 'recentOrders'));
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

        $table = $tableMap[$section] ?? null;
        if ($table) {
            $data['items'] = Database::fetchAll("SELECT * FROM {$table} ORDER BY id DESC");
        }

        $data['section_label'] = $this->sectionLabel($section);
        $this->view('admin/section', $data);
    }

    public function save(string $section): void
    {
        $this->requireAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        $table = $this->sectionToTable($section);

        if (!$table) {
            redirect('/admin');
        }

        $data = $_POST;
        unset($data['id'], $data['_csrf']);

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

        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $settingKey = substr($key, 8);
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
        $data['section_label'] = 'مدیریت نوبت‌ها';
        $this->view('admin/section', $data);
    }

    private function sectionUsers(array &$data): void
    {
        $data['items'] = Database::fetchAll("SELECT id, name, family, phone, email, role, level, points, wallet, created_at FROM users ORDER BY id DESC");
        $data['section_label'] = 'مدیریت کاربران';
        $this->view('admin/section', $data);
    }

    private function sectionSettings(array &$data): void
    {
        $rows = Database::fetchAll("SELECT * FROM settings ORDER BY id");
        $data['settings'] = [];
        foreach ($rows as $row) {
            $data['settings'][$row['setting_key']] = $row['setting_value'];
        }
        $data['section_label'] = 'تنظیمات سایت';
        $this->view('admin/section', $data);
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

    private function sectionLabel(string $section): string
    {
        $labels = [
            'services' => 'مدیریت خدمات',
            'artists' => 'مدیریت آرایشگران',
            'appointments' => 'مدیریت نوبت‌ها',
            'products' => 'مدیریت محصولات',
            'users' => 'مدیریت کاربران',
            'courses' => 'مدیریت دوره‌ها',
            'testimonials' => 'مدیریت نظرات',
            'settings' => 'تنظیمات سایت',
            'hair-models' => 'مدل‌های مو',
            'tutorials' => 'آموزش‌ها',
        ];
        return $labels[$section] ?? $section;
    }

    private function uploadFile(array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed) || $file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = __DIR__ . '/../../public/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return 'uploads/' . $filename;
        }

        return null;
    }
}
