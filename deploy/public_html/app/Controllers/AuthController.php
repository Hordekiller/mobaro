<?php

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $_SESSION['captcha_question'] = Captcha::store();
        $captchaQuestion = $_SESSION['captcha_question'];
        $this->view('auth/login', compact('captchaQuestion'));
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $_SESSION['captcha_question'] = Captcha::store();
        $captchaQuestion = $_SESSION['captcha_question'];
        $this->view('auth/register', compact('captchaQuestion'));
    }

    public function login(): void
    {
        $this->verifyCsrf();

        if (!Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['captcha' => 'کد امنیتی اشتباه است.']);
            return;
        }

        $login = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['phone' => 'شماره تلفن یا نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        RateLimiter::cleanup();
        if (RateLimiter::isLocked('user_' . $login)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['rate_limit' => 'تعداد تلاش‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE (phone = ? OR name = ?) AND (is_active IS NULL OR is_active = 1)", [$login, $login]);

        if (!$user || !Auth::verify($password, $user['password'])) {
            RateLimiter::recordAttempt('user_' . $login, false);
            $_SESSION['captcha_question'] = Captcha::store();
            $remaining = RateLimiter::remainingAttempts('user_' . $login);
            $msg = 'شماره تلفن / نام کاربری یا رمز عبور اشتباه است.';
            if ($remaining <= 2 && $remaining > 0) {
                $msg .= " ({$remaining} تلاش باقی‌مانده)";
            }
            $this->redirectWithErrors('/login', ['password' => $msg]);
            return;
        }

        RateLimiter::recordAttempt('user_' . $login, true);
        Auth::login($user['id'], $user);
        $this->syncSessionWishlist();

        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    }

    public function register(): void
    {
        $this->verifyCsrf();

        if (!Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/register', ['captcha' => 'کد امنیتی اشتباه است.']);
            return;
        }

        $name = sanitize($_POST['name'] ?? '');
        $family = sanitize($_POST['family'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = $this->validate(
            compact('name', 'family', 'phone', 'password'),
            ['name' => 'required|min:2', 'family' => 'required|min:2', 'phone' => 'required|min:10', 'password' => 'required|min:6']
        );

        if (!empty($errors)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/register', $errors);
            return;
        }

        $existing = Database::fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
        if ($existing) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/register', ['phone' => 'این شماره تلفن قبلاً ثبت شده است.']);
            return;
        }

        $userId = Database::insert('users', [
            'name' => $name,
            'family' => $family,
            'phone' => $phone,
            'password' => Auth::hash($password),
            'level' => 'bronze',
            'points' => 50,
            'wallet' => 0,
            'phone_verified' => 0,
        ]);

        Database::insert('transactions', [
            'user_id' => $userId,
            'type' => 'points_earn',
            'amount' => 50,
            'description' => 'امتیاز ثبت‌نام',
        ]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        Auth::login($userId, $user);
        $this->syncSessionWishlist();

        flash('success', 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/');
    }

    public function forgot(): void
    {
        $this->verifyCsrf();

        if (!Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['captcha' => 'کد امنیتی اشتباه است.']);
            return;
        }

        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($phone)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['phone' => 'شماره تلفن را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        if (RateLimiter::isLocked('forgot_' . $phone)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors('/login', ['rate_limit' => 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
            return;
        }

        $user = Database::fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
        if (!$user) {
            RateLimiter::recordAttempt('forgot_' . $phone, false);
            flash('success', 'اگر این شماره در سیستم ثبت شده باشد، کد تأیید ارسال خواهد شد.');
            $_SESSION['captcha_question'] = Captcha::store();
            redirect('/login');
            return;
        }

        $sms = new SmsService();
        if (!$sms->isConfigured()) {
            RateLimiter::recordAttempt('forgot_' . $phone, false);
            flash('error', 'بازیابی رمز عبور از طریق پیامک فعال نیست. لطفاً با شماره ' . Settings::get('brand_phone', '۰۳۱-۳۶۶۶۲۱۲۲') . ' تماس بگیرید.');
            $_SESSION['captcha_question'] = Captcha::store();
            redirect('/login');
            return;
        }

        $otp = $this->generateOtp();
        $ttl = Config::get('sms.otp_ttl', 180);
        $this->storeOtp($phone, $otp, 'reset_password', $ttl);

        $result = $sms->sendVerify($phone, $otp);

        RateLimiter::recordAttempt('forgot_' . $phone, false);

        $_SESSION['otp_phone'] = $phone;
        $_SESSION['otp_purpose'] = 'reset_password';
        $_SESSION['otp_expires'] = time() + $ttl;

        if ($result['status']) {
            flash('success', 'کد تأیید به شماره ' . $phone . ' ارسال شد.');
        } else {
            flash('error', 'خطا در ارسال کد تأیید. لطفاً دوباره تلاش کنید.');
        }

        redirect('/verify-otp');
    }

    public function showOtpForm(): void
    {
        if (empty($_SESSION['otp_phone']) || empty($_SESSION['otp_purpose'])) {
            redirect('/login');
            return;
        }
        $phone = $_SESSION['otp_phone'];
        $this->view('auth/verify-otp', compact('phone'));
    }

    public function verifyOtp(): void
    {
        $this->verifyCsrf();

        $code = sanitize($_POST['code'] ?? '');
        $phone = $_SESSION['otp_phone'] ?? '';
        $purpose = $_SESSION['otp_purpose'] ?? '';

        if (empty($phone) || empty($purpose)) {
            flash('error', 'جلسه منقضی شده. لطفاً دوباره تلاش کنید.');
            redirect('/login');
            return;
        }

        if (empty($code)) {
            $this->redirectWithErrors('/verify-otp', ['code' => 'کد تأیید را وارد کنید.']);
            return;
        }

        $valid = $this->verifyOtpCode($phone, $code, $purpose);
        if (!$valid) {
            $this->redirectWithErrors('/verify-otp', ['code' => 'کد تأیید نادرست یا منقضی شده است.']);
            return;
        }

        if ($purpose === 'register') {
            $regData = $_SESSION['register_data'] ?? [];
            if (empty($regData)) {
                flash('error', 'اطلاعات ثبت‌نام یافت نشد.');
                redirect('/register');
                return;
            }

            $userId = Database::insert('users', [
                'name' => $regData['name'],
                'family' => $regData['family'],
                'phone' => $phone,
                'password' => Auth::hash($regData['password']),
                'level' => 'bronze',
                'points' => 50,
                'wallet' => 0,
                'phone_verified' => 1,
            ]);

            Database::insert('transactions', [
                'user_id' => $userId,
                'type' => 'points_earn',
                'amount' => 50,
                'description' => 'امتیاز ثبت‌نام',
            ]);

            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            Auth::login($userId, $user);
            $this->syncSessionWishlist();

            unset($_SESSION['otp_phone'], $_SESSION['otp_purpose'], $_SESSION['otp_expires'], $_SESSION['register_data']);
            flash('success', 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!');
            redirect('/dashboard');

        } elseif ($purpose === 'reset_password') {
            $_SESSION['reset_password_phone'] = $phone;
            unset($_SESSION['otp_phone'], $_SESSION['otp_purpose'], $_SESSION['otp_expires']);
            redirect('/reset-password');
        }
    }

    public function showResetPasswordForm(): void
    {
        if (empty($_SESSION['reset_password_phone'])) {
            redirect('/login');
            return;
        }
        $this->view('auth/reset-password');
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();

        $phone = $_SESSION['reset_password_phone'] ?? '';
        if (empty($phone)) {
            flash('error', 'جلسه منقضی شده.');
            redirect('/login');
            return;
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 6) {
            $this->redirectWithErrors('/reset-password', ['password' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.']);
            return;
        }
        if ($password !== $confirm) {
            $this->redirectWithErrors('/reset-password', ['password_confirm' => 'تکرار رمز عبور مطابقت ندارد.']);
            return;
        }

        Database::update('users', ['password' => Auth::hash($password)], 'phone = ?', [$phone]);
        unset($_SESSION['reset_password_phone']);

        flash('success', 'رمز عبور با موفقیت تغییر کرد. لطفاً وارد شوید.');
        redirect('/login');
    }

    public function showVerifyOtpPage(): void
    {
        $this->view('auth/verify-otp', ['phone' => $_SESSION['otp_phone'] ?? '']);
    }

    public function showResetPasswordPage(): void
    {
        $this->view('auth/reset-password');
    }

    private function generateOtp(): string
    {
        $length = Config::get('sms.otp_length', 5);
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    private function storeOtp(string $phone, string $code, string $purpose, int $ttl): void
    {
        Database::query(
            "UPDATE verification_codes SET used = 1 WHERE phone = ? AND purpose = ? AND used = 0",
            [$phone, $purpose]
        );
        Database::insert('verification_codes', [
            'phone' => $phone,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl),
        ]);
    }

    private function verifyOtpCode(string $phone, string $code, string $purpose): bool
    {
        $row = Database::fetch(
            "SELECT id FROM verification_codes WHERE phone = ? AND code = ? AND purpose = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            [$phone, $code, $purpose]
        );
        if (!$row) {
            return false;
        }
        Database::update('verification_codes', ['used' => 1], 'id = :id', ['id' => $row['id']]);
        return true;
    }

    public function googleRedirect(): void
    {
        if (!GoogleAuth::isConfigured()) {
            flash('error', 'ورود با گوگل فعال نیست. لطفاً با شماره تلفن وارد شوید.');
            redirect('/login');
            return;
        }
        header('Location: ' . GoogleAuth::getAuthUrl());
        exit;
    }

    public function googleCallback(): void
    {
        $code = $_GET['code'] ?? '';
        $error = $_GET['error'] ?? '';

        if ($error) {
            flash('error', 'ورود با گوگل لغو شد.');
            redirect('/login');
            return;
        }

        if (empty($code)) {
            flash('error', 'کد تأیید گوگل دریافت نشد.');
            redirect('/login');
            return;
        }

        $tokenData = GoogleAuth::exchangeCode($code);
        if (!$tokenData) {
            flash('error', 'خطا در احراز هویت گوگل. لطفاً دوباره تلاش کنید.');
            redirect('/login');
            return;
        }

        $googleUser = GoogleAuth::getUserInfo($tokenData['access_token']);
        if (!$googleUser) {
            flash('error', 'خطا در دریافت اطلاعات کاربر از گوگل.');
            redirect('/login');
            return;
        }

        $user = GoogleAuth::findOrCreateUser($googleUser);
        Auth::login($user['id'], $user);
        $this->syncSessionWishlist();

        flash('success', 'با موفقیت وارد شدید!');
        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    }

    private function syncSessionWishlist(): void
    {
        $sessionIds = $_SESSION['wishlist'] ?? [];
        if (empty($sessionIds) || !Auth::check()) {
            return;
        }
        foreach ($sessionIds as $pid) {
            $pid = (int) $pid;
            if ($pid <= 0) {
                continue;
            }
            $exists = Database::fetch(
                "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
                [Auth::id(), $pid]
            );
            if (!$exists) {
                Database::insert('wishlist', ['user_id' => Auth::id(), 'product_id' => $pid]);
            }
        }
        $_SESSION['wishlist'] = [];
    }
}
