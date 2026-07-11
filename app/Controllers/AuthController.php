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
        ]);

        Database::insert('transactions', [
            'user_id' => $userId,
            'type' => 'points_earn',
            'amount' => 50,
            'description' => 'امتیاز ثبت‌نام',
        ]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        Auth::login($userId, $user);

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

        RateLimiter::recordAttempt('forgot_' . $phone, false);

        flash('success', 'اگر این شماره در سیستم ثبت شده باشد، لطفاً با شماره تماس سالن هماهنگ کنید.');
        $_SESSION['captcha_question'] = Captcha::store();
        redirect('/login');
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

        flash('success', 'با موفقیت وارد شدید!');
        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    }
}
