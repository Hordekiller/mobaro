<?php

class DashboardController extends BaseController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();

        $stats = [
            'upcoming_appointments' => Database::fetch(
                "SELECT COUNT(*) as cnt FROM appointments WHERE user_id = ? AND appointment_date >= CURDATE() AND status IN ('confirmed', 'pending')",
                [$user['id']]
            )['cnt'] ?? 0,
            'active_courses' => Database::fetch(
                "SELECT COUNT(*) as cnt FROM course_enrollments WHERE user_id = ? AND progress < 100",
                [$user['id']]
            )['cnt'] ?? 0,
            'active_orders' => Database::fetch(
                "SELECT COUNT(*) as cnt FROM orders WHERE user_id = ? AND status NOT IN ('delivered', 'cancelled')",
                [$user['id']]
            )['cnt'] ?? 0,
        ];

        $nextAppointment = Database::fetch(
            "SELECT a.*, s.title as service_title, s.price, ar.name as artist_name
             FROM appointments a
             LEFT JOIN services s ON a.service_id = s.id
             LEFT JOIN artists ar ON a.artist_id = ar.id
             WHERE a.user_id = ? AND a.appointment_date >= CURDATE() AND a.status IN ('confirmed', 'pending')
             ORDER BY a.appointment_date, a.appointment_time LIMIT 1",
            [$user['id']]
        );

        $recentActivities = Database::fetchAll(
            "SELECT 'appointment' as type, CONCAT('نوبت ', s.title) as title, a.created_at as created_at
             FROM appointments a LEFT JOIN services s ON a.service_id = s.id WHERE a.user_id = ?
             UNION ALL
             SELECT 'order' as type, CONCAT('سفارش ', o.tracking_code) as title, o.created_at
             FROM orders o WHERE o.user_id = ?
             UNION ALL
             SELECT 'transaction' as type, t.description as title, t.created_at
             FROM transactions t WHERE t.user_id = ?
             ORDER BY created_at DESC LIMIT 4",
            [$user['id'], $user['id'], $user['id']]
        );

        $this->view('dashboard/index', compact('user', 'stats', 'nextAppointment', 'recentActivities'));
    }

    public function tab(string $tab): void
    {
        Auth::requireAuth();
        $user = Auth::user();

        $validTabs = ['appointments', 'courses', 'orders', 'wishlist', 'wallet', 'addresses', 'account', 'password'];
        if (!in_array($tab, $validTabs)) {
            redirect('/dashboard');
        }

        $data = compact('user');

        switch ($tab) {
            case 'appointments':
                $data['appointments'] = Database::fetchAll(
                    "SELECT a.*, s.title as service_title, s.price, ar.name as artist_name, ar.avatar as artist_avatar
                     FROM appointments a
                     LEFT JOIN services s ON a.service_id = s.id
                     LEFT JOIN artists ar ON a.artist_id = ar.id
                     WHERE a.user_id = ?
                     ORDER BY a.appointment_date DESC",
                    [$user['id']]
                );
                break;

            case 'courses':
                $data['enrollments'] = Database::fetchAll(
                    "SELECT ce.*, c.title, c.teacher, c.type, c.image, c.category, c.duration
                     FROM course_enrollments ce
                     JOIN courses c ON ce.course_id = c.id
                     WHERE ce.user_id = ?",
                    [$user['id']]
                );
                break;

            case 'orders':
                $data['orders'] = Database::fetchAll(
                    "SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items_list
                     FROM orders o
                     LEFT JOIN order_items oi ON o.id = oi.order_id
                     WHERE o.user_id = ?
                     GROUP BY o.id
                     ORDER BY o.created_at DESC",
                    [$user['id']]
                );
                break;

            case 'wishlist':
                $data['wishlist'] = Database::fetchAll(
                    "SELECT w.*, p.name, p.price, p.image, p.category
                     FROM wishlist w
                     JOIN products p ON w.product_id = p.id
                     WHERE w.user_id = ?",
                    [$user['id']]
                );
                break;

            case 'wallet':
                $data['transactions'] = Database::fetchAll(
                    "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
                    [$user['id']]
                );
                break;

            case 'addresses':
                $data['addresses'] = Database::fetchAll(
                    "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC",
                    [$user['id']]
                );
                break;

            case 'account':
                break;

            case 'password':
                break;
        }

        $this->view('dashboard/index', $data);
    }

    public function updateProfile(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();
        $userId = Auth::id();

        $name = sanitize($_POST['name'] ?? '');
        $family = sanitize($_POST['family'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        $data = ['name' => $name, 'family' => $family, 'email' => $email];

        if (!empty($_FILES['avatar']['name'])) {
            $uploaded = FileUploader::upload($_FILES['avatar'], 'avatar_' . $userId);
            if ($uploaded) {
                $data['avatar'] = $uploaded;
            }
        }

        Database::update('users', $data, 'id = :id', ['id' => $userId]);
        $_SESSION['user'] = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        flash('success', 'اطلاعات حساب با موفقیت به‌روزرسانی شد.');
        back();
    }

    public function changePassword(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();
        $userId = Auth::id();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $this->redirectWithErrors('/dashboard/password', ['all' => 'تمام فیلدها را پر کنید.']);
            return;
        }

        $user = Database::fetch("SELECT password FROM users WHERE id = ?", [$userId]);
        if (!Auth::verify($current, $user['password'])) {
            $this->redirectWithErrors('/dashboard/password', ['current' => 'رمز عبور فعلی اشتباه است.']);
            return;
        }

        if ($new !== $confirm) {
            $this->redirectWithErrors('/dashboard/password', ['confirm' => 'رمز عبور جدید و تکرار آن مطابقت ندارند.']);
            return;
        }

        if (strlen($new) < 6) {
            $this->redirectWithErrors('/dashboard/password', ['new' => 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.']);
            return;
        }

        Database::update('users', ['password' => Auth::hash($new)], 'id = :id', ['id' => $userId]);
        flash('success', 'رمز عبور با موفقیت تغییر یافت.');
        back();
    }

    public function addAddress(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $title = sanitize($_POST['title'] ?? 'خانه');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? 'تهران');
        $zipCode = sanitize($_POST['zip_code'] ?? '');

        if (empty($address)) {
            $this->redirectWithErrors('/dashboard/addresses', ['address' => 'آدرس را وارد کنید.']);
            return;
        }

        $isDefault = (int) ($_POST['is_default'] ?? 0);
        if ($isDefault) {
            Database::update('addresses', ['is_default' => 0], 'user_id = :uid', ['uid' => Auth::id()]);
        }

        Database::insert('addresses', [
            'user_id' => Auth::id(),
            'title' => $title,
            'address' => $address,
            'city' => $city,
            'zip_code' => $zipCode,
            'is_default' => $isDefault,
        ]);

        flash('success', 'آدرس جدید اضافه شد.');
        back();
    }

    public function deleteAddress(int $id): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();
        Database::delete('addresses', 'id = ? AND user_id = ?', [$id, Auth::id()]);
        $this->json(['success' => true, 'message' => 'آدرس حذف شد.']);
    }

    public function toggleWishlist(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $productId = (int) ($_POST['product_id'] ?? 0);
        if (!$productId) {
            $this->json(['error' => 'محصول نامعتبر'], 400);
            return;
        }

        $existing = Database::fetch(
            "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
            [Auth::id(), $productId]
        );

        if ($existing) {
            Database::delete('wishlist', 'id = ?', [$existing['id']]);
            $this->json(['success' => true, 'action' => 'removed', 'message' => 'از علاقه‌مندی‌ها حذف شد']);
        } else {
            Database::insert('wishlist', ['user_id' => Auth::id(), 'product_id' => $productId]);
            $this->json(['success' => true, 'action' => 'added', 'message' => 'به علاقه‌مندی‌ها اضافه شد']);
        }
    }
}
