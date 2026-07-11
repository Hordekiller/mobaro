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

        $validTabs = ['appointments', 'courses', 'orders', 'wishlist', 'wallet', 'addresses', 'account', 'password', 'order_detail', 'reviews', 'blog-comments'];
        if (!in_array($tab, $validTabs)) {
            redirect('/dashboard');
        }

        $data = compact('user', 'tab');

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
                    "SELECT ce.*, c.title, c.teacher, c.type, c.image, c.category, c.duration, c.slug
                     FROM course_enrollments ce
                     JOIN courses c ON ce.course_id = c.id
                     WHERE ce.user_id = ?",
                    [$user['id']]
                );
                break;

            case 'orders':
                $orderPage = max(1, (int) ($_GET['page'] ?? 1));
                $orderPerPage = 10;
                $totalOrders = (int) Database::fetch(
                    "SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?",
                    [$user['id']]
                )['cnt'];
                $orderTotalPages = max(1, (int) ceil($totalOrders / $orderPerPage));
                $orderPage = min($orderPage, $orderTotalPages);
                $orderOffset = ($orderPage - 1) * $orderPerPage;

                $data['orders'] = Database::fetchAll(
                    "SELECT o.*,
                            GROUP_CONCAT(CONCAT(oi.product_name, ' (x', oi.quantity, ')') ORDER BY oi.id ASC SEPARATOR ', ') as items_list,
                            GROUP_CONCAT(COALESCE(p.image, '') ORDER BY oi.id ASC SEPARATOR '||') as items_images,
                            GROUP_CONCAT(oi.product_id ORDER BY oi.id ASC SEPARATOR '||') as items_ids,
                            COALESCE(SUM(oi.quantity), 0) as item_count
                     FROM orders o
                     LEFT JOIN order_items oi ON o.id = oi.order_id
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE o.user_id = ?
                     GROUP BY o.id
                     ORDER BY o.created_at DESC
                     LIMIT {$orderPerPage} OFFSET {$orderOffset}",
                    [$user['id']]
                );
                $data['orderPage'] = $orderPage;
                $data['orderTotalPages'] = $orderTotalPages;
                $data['orderTotal'] = $totalOrders;
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

            case 'order_detail':
                $id = (int) ($_GET['id'] ?? 0);
                $data['order'] = $id ? Database::fetch(
                    "SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?",
                    [$id, $user['id']]
                ) : null;
                $data['items'] = $data['order'] ? Database::fetchAll(
                    "SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?",
                    [$id]
                ) : [];
                break;

            case 'reviews':
                if ($this->columnExists('reviews', 'user_id')) {
                    $data['reviews'] = Database::fetchAll(
                        "SELECT r.*, p.name as product_name, p.image as product_image
                         FROM reviews r
                         JOIN products p ON r.product_id = p.id
                         WHERE r.user_id = ?
                         ORDER BY r.created_at DESC",
                        [$user['id']]
                    );
                } else {
                    $userName = trim(($user['name'] ?? '') . ' ' . ($user['family'] ?? ''));
                    $data['reviews'] = Database::fetchAll(
                        "SELECT r.*, p.name as product_name, p.image as product_image
                         FROM reviews r
                         JOIN products p ON r.product_id = p.id
                         WHERE r.user_name = ?
                         ORDER BY r.created_at DESC",
                        [$userName]
                    );
                }
                break;

            case 'blog-comments':
                $data['blogComments'] = $this->tableExists('blog_comments')
                    ? Database::fetchAll(
                        "SELECT bc.*, bp.title as post_title, bp.slug as post_slug
                         FROM blog_comments bc
                         JOIN blog_posts bp ON bc.post_id = bp.id
                         WHERE bc.user_id = ?
                         ORDER BY bc.created_at DESC",
                        [$user['id']]
                    )
                    : [];
                break;
        }

        $this->view('dashboard/index', $data);
    }

    private function tableExists(string $table): bool
    {
        $result = Database::fetch(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?",
            [$table]
        );

        return (int) ($result['cnt'] ?? 0) > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        $result = Database::fetch(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        );

        return (int) ($result['cnt'] ?? 0) > 0;
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
        $phone = sanitize($_POST['phone'] ?? '');

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
            'phone' => $phone,
            'is_default' => $isDefault,
        ]);

        flash('success', 'آدرس جدید اضافه شد.');
        back();
    }

    public function updateAddress(int $id): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $existing = Database::fetch(
            "SELECT * FROM addresses WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$existing) {
            $this->json(['error' => 'آدرس یافت نشد'], 404);
            return;
        }

        $title = sanitize($_POST['title'] ?? 'خانه');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? 'تهران');
        $zipCode = sanitize($_POST['zip_code'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($address)) {
            $this->json(['error' => 'آدرس را وارد کنید.'], 400);
            return;
        }

        $isDefault = (int) ($_POST['is_default'] ?? 0);
        if ($isDefault) {
            Database::update('addresses', ['is_default' => 0], 'user_id = :uid AND id != :aid', ['uid' => Auth::id(), 'aid' => $id]);
        }

        Database::update('addresses', [
            'title' => $title,
            'address' => $address,
            'city' => $city,
            'zip_code' => $zipCode,
            'phone' => $phone,
            'is_default' => $isDefault,
        ], 'id = :id', ['id' => $id]);

        if (isset($_POST['from_cart']) && $_POST['from_cart'] === '1') {
            $this->json(['success' => true, 'message' => 'آدرس به‌روزرسانی شد.']);
        } else {
            flash('success', 'آدرس با موفقیت به‌روزرسانی شد.');
            back();
        }
    }

    public function deleteAddress(int $id): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();
        Database::delete('addresses', 'id = ? AND user_id = ?', [$id, Auth::id()]);
        $this->json(['success' => true, 'message' => 'آدرس حذف شد.']);
    }

    public function cancelAppointment(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['appointment_id'] ?? 0);
        if (!$id) {
            $this->json(['error' => 'نوبت نامعتبر'], 400);
            return;
        }

        $apt = Database::fetch(
            "SELECT id, user_id, status FROM appointments WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$apt) {
            $this->json(['error' => 'نوبت یافت نشد'], 404);
            return;
        }
        if (!in_array($apt['status'], ['pending', 'confirmed'])) {
            $this->json(['error' => 'امکان لغو این نوبت وجود ندارد'], 400);
            return;
        }

        Database::update('appointments', ['status' => 'cancelled'], 'id = :id', ['id' => $id]);
        $this->json(['success' => true, 'message' => 'نوبت با موفقیت لغو شد.']);
    }

    public function cancelOrder(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['order_id'] ?? 0);
        if (!$id) {
            $this->json(['error' => 'سفارش نامعتبر'], 400);
            return;
        }

        $order = Database::fetch(
            "SELECT id, user_id, status FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            $this->json(['error' => 'سفارش یافت نشد'], 404);
            return;
        }
        if ($order['status'] !== 'pending') {
            $this->json(['error' => 'فقط سفارش‌های در انتظار پرداخت قابل لغو هستند'], 400);
            return;
        }

        Database::update('orders', ['status' => 'cancelled'], 'id = :id', ['id' => $id]);
        $this->json(['success' => true, 'message' => 'سفارش با موفقیت لغو شد.']);
    }

    public function rescheduleAppointment(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $id = (int) ($_POST['appointment_id'] ?? 0);
        $date = sanitize($_POST['new_date'] ?? '');
        $time = sanitize($_POST['new_time'] ?? '');

        if (!$id || !$date || !$time) {
            $this->json(['error' => 'اطلاعات ناقص است'], 400);
            return;
        }

        $apt = Database::fetch(
            "SELECT id, user_id, status FROM appointments WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$apt) {
            $this->json(['error' => 'نوبت یافت نشد'], 404);
            return;
        }
        if (!in_array($apt['status'], ['pending', 'confirmed'])) {
            $this->json(['error' => 'امکان تغییر این نوبت وجود ندارد'], 400);
            return;
        }

        Database::update('appointments', [
            'appointment_date' => $date,
            'appointment_time' => $time,
        ], 'id = :id', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'نوبت با موفقیت تغییر یافت.']);
    }

    public function orderDetail(): void
    {
        Auth::requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            back();
            return;
        }

        $order = Database::fetch(
            "SELECT o.*,
                    GROUP_CONCAT(CONCAT(oi.product_name, ' × ', oi.quantity, ' — ', FORMAT(oi.price, 0), ' تومان') SEPARATOR '\n') as items_text
             FROM orders o
             LEFT JOIN order_items oi ON o.id = oi.order_id
             WHERE o.id = ? AND o.user_id = ?
             GROUP BY o.id",
            [$id, Auth::id()]
        );

        if (!$order) {
            back();
            return;
        }

        $items = Database::fetchAll(
            "SELECT oi.*, p.image FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );

        $user = Auth::user();
        $this->view('dashboard/index', compact('user', 'order', 'items') + ['tab' => 'order_detail']);
    }

    public function walletTopUp(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $amount = (int) ($_POST['amount'] ?? 0);
        if ($amount < 10000) {
            $this->json(['error' => 'حداقل مبلغ افزایش ۱۰,۰۰۰ تومان است'], 400);
            return;
        }

        $zpl = new ZarinPal();
        $callbackUrl = Config::get('app.url') . '/dashboard/wallet/payment/callback';
        $result = $zpl->requestPayment($amount, 'افزایش موجودی کیف پول', $callbackUrl, null, Auth::user()['phone'] ?? null);

        if ($result['status']) {
            $_SESSION['wallet_topup_amount'] = $amount;
            $_SESSION['wallet_topup_authority'] = $result['authority'];
            $this->json([
                'success' => true,
                'payment_required' => true,
                'redirect' => $result['redirect_url'],
                'message' => 'در حال انتقال به درگاه پرداخت...',
            ]);
        } else {
            $this->json(['error' => $result['message']], 500);
        }
    }

    public function walletPaymentCallback(): void
    {
        Auth::requireAuth();

        $authority = $_GET['Authority'] ?? '';
        $status = $_GET['Status'] ?? '';

        if (!$authority || $status !== 'OK') {
            flash('error', 'پرداخت لغو شد.');
            redirect('/dashboard/wallet');
            return;
        }

        $amount = (int) ($_SESSION['wallet_topup_amount'] ?? 0);
        if (!$amount) {
            flash('error', 'اطلاعات پرداخت نامعتبر است.');
            redirect('/dashboard/wallet');
            return;
        }

        $zpl = new ZarinPal();
        $result = $zpl->verifyPayment($amount, $authority);

        if ($result['status']) {
            Database::insert('transactions', [
                'user_id' => Auth::id(),
                'type' => 'wallet_deposit',
                'amount' => $amount,
                'description' => 'افزایش موجودی کیف پول',
                'payment_id' => $result['ref_id'],
                'payment_status' => 'paid',
            ]);

            $current = Database::fetch("SELECT wallet FROM users WHERE id = ?", [Auth::id()]);
            $newBalance = $current['wallet'] + $amount;
            Database::update('users', ['wallet' => $newBalance], 'id = :id', ['id' => Auth::id()]);

            unset($_SESSION['wallet_topup_amount'], $_SESSION['wallet_topup_authority']);
            flash('success', 'کیف پول شما به مبلغ ' . number_format($amount) . ' تومان افزایش یافت.');
        } else {
            flash('error', 'پرداخت ناموفق بود: ' . $result['message']);
        }

        redirect('/dashboard/wallet');
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
