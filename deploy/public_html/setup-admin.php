<?php
/**
 * Mobaro - First Admin Setup
 * 
 * IMPORTANT: Delete this file after creating the admin user!
 * Access: https://your-domain.com/setup-admin.php
 */

session_start();

define('SETUP_TOKEN', 'mobaro-setup-' . md5($_SERVER['HTTP_HOST'] ?? 'localhost'));

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $family = trim($_POST['family'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($token !== SETUP_TOKEN) {
        $error = 'توکن نامعتبر است.';
    } elseif ($name === '' || $phone === '' || $password === '') {
        $error = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif ($password !== $password2) {
        $error = 'رمز عبور و تکرار آن مطابقت ندارند.';
    } elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    } else {
        try {
            require_once __DIR__ . '/vendor/autoload.php';
            require_once __DIR__ . '/app/bootstrap.php';

            // Check if admin already exists
            $existing = Database::fetch("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            if ($existing) {
                $error = 'یک ادمین قبلاً ساخته شده است. فایل setup-admin.php را حذف کنید.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                Database::query(
                    "INSERT INTO users (name, family, phone, password, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)",
                    [$name, $family, $phone, $hashed]
                );
                $success = true;
            }
        } catch (Throwable $e) {
            $error = 'خطا در اتصال به دیتابیس: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب موبارو - ساخت مدیر</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #fdf2f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 20px; padding: 40px; max-width: 440px; width: 100%; box-shadow: 0 4px 24px rgba(225,29,72,0.08); }
        h1 { color: #e11d48; font-size: 1.5rem; margin-bottom: 8px; text-align: center; }
        .subtitle { color: #71717a; font-size: 0.875rem; text-align: center; margin-bottom: 28px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; color: #3f3f46; }
        input { width: 100%; padding: 12px 16px; border: 2px solid #fce7f3; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        input:focus { border-color: #e11d48; }
        button { width: 100%; padding: 14px; background: #e11d48; color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #be185d; }
        .error { background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; text-align: center; }
        .success { background: #f0fdf4; color: #16a34a; padding: 16px; border-radius: 10px; text-align: center; font-size: 0.95rem; line-height: 1.8; }
        .success strong { display: block; font-size: 1.1rem; margin-bottom: 8px; }
        .warning { background: #fffbeb; color: #b45309; padding: 12px; border-radius: 10px; margin-top: 12px; font-size: 0.8rem; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>mobaro</h1>
        <p class="subtitle">ساخت اولین حساب مدیریت</p>

        <?php if (!empty($error)) : ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="success">
                <strong>مدیر با موفقیت ساخته شد!</strong>
                اکنون می‌توانید وارد پنل مدیریت شوید.<br>
                <a href="/admin/login" style="color:#e11d48;font-weight:700;">ورود به پنل مدیریت</a>
                <div class="warning">
                    این فایل (setup-admin.php) را <strong>فوراً</strong> از هاست حذف کنید.
                </div>
            </div>
        <?php else : ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars(SETUP_TOKEN) ?>">

                <label>نام</label>
                <input type="text" name="name" required placeholder="مثال: علی">

                <label>نام خانوادگی</label>
                <input type="text" name="family" required placeholder="مثال: محمدی">

                <label>شماره موبایل</label>
                <input type="text" name="phone" required placeholder="۰۹۱۲۱۲۳۴۵۶۷" dir="ltr">

                <label>رمز عبور</label>
                <input type="password" name="password" required placeholder="حداقل ۶ کاراکتر">

                <label>تکرار رمز عبور</label>
                <input type="password" name="password2" required placeholder="تکرار رمز عبور">

                <button type="submit">ساخت مدیر</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
