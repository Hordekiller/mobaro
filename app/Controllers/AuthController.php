<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Captcha;
use App\RateLimiter;
use App\Database;
use App\Services\SmsService;
use App\GoogleAuth;

class AuthController extends BaseController
{
    private const PATH_DASHBOARD = '/dashboard';
    private const PATH_LOGIN = '/login';
    private const PATH_REGISTER = '/register';
    private const MSG_CAPTCHA_ERROR = 'کد امنیتی صحیح نیست.';

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(self::PATH_DASHBOARD);
        }
        if (!empty($_GET['redirect'])) {
            $_SESSION['redirect_after_login'] = $_GET['redirect'];
        }
        $_SESSION['captcha_question'] = Captcha::store();
        $captchaQuestion = $_SESSION['captcha_question'];
        $this->view('auth/login', compact('captchaQuestion'));
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect(self::PATH_DASHBOARD);
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
            $this->redirectWithErrors(self::PATH_LOGIN, ['captcha' => self::MSG_CAPTCHA_ERROR]);
            return;
        }

        $login = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_LOGIN, ['phone' => 'شماره تلفن یا نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        RateLimiter::cleanup();
        if (RateLimiter::isLocked('user_' . $login)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_LOGIN, ['rate_limit' => 'تعداد تلاش‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
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
            $this->redirectWithErrors(self::PATH_LOGIN, ['password' => $msg]);
            return;
        }

        RateLimiter::recordAttempt('user_' . $login, true);
        Auth::login($user['id'], $user);
        $this->syncSessionWishlist();

        $redirect = $_SESSION['redirect_after_login'] ?? self::PATH_DASHBOARD;
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    }

    public function register(): void
    {
        $this->verifyCsrf();

        if (!Captcha::verify($_POST['captcha'] ?? '')) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_REGISTER, ['captcha' => self::MSG_CAPTCHA_ERROR]);
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
            $this->redirectWithErrors(self::PATH_REGISTER, $errors);
            return;
        }

        $existing = Database::fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
        if ($existing) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_REGISTER, ['phone' => 'این شماره تلفن قبلاً ثبت شده است.']);
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

        $smsService = new SmsService();
        if ($smsService->isConfigured()) {
            $code = $smsService->createVerificationCode($phone, 'register');
            if ($code) {
                $_SESSION['verify_phone'] = $phone;
                flash('success', 'کد تأیید به شماره ' . $phone . ' ارسال شد.');
                redirect('/verify-otp?phone=' . urlencode($phone));
                return;
            }
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        Auth::login($userId, $user);
        $this->syncSessionWishlist();

        flash('success', 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!');
        redirect(self::PATH_DASHBOARD);
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
            $this->redirectWithErrors(self::PATH_LOGIN, ['captcha' => self::MSG_CAPTCHA_ERROR]);
            return;
        }

        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($phone)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_LOGIN, ['phone' => 'شماره تلفن را وارد کنید.']);
            return;
        }

        RateLimiter::init();
        if (RateLimiter::isLocked('forgot_' . $phone)) {
            $_SESSION['captcha_question'] = Captcha::store();
            $this->redirectWithErrors(self::PATH_LOGIN, ['rate_limit' => 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.']);
            return;
        }

        RateLimiter::recordAttempt('forgot_' . $phone, false);

        flash('success', 'اگر این شماره در سیستم ثبت شده باشد، لطفاً با شماره تماس سالن هماهنگ کنید.');
        $_SESSION['captcha_question'] = Captcha::store();
        redirect(self::PATH_LOGIN);
    }

    public function googleRedirect(): void
    {
        if (!GoogleAuth::isConfigured()) {
            flash('error', 'ورود با گوگل فعال نیست. لطفاً با شماره تلفن وارد شوید.');
            redirect(self::PATH_LOGIN);
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
            redirect(self::PATH_LOGIN);
            return;
        }

        if (empty($code)) {
            flash('error', 'کد تأیید گوگل دریافت نشد.');
            redirect(self::PATH_LOGIN);
            return;
        }

        $tokenData = GoogleAuth::exchangeCode($code);
        if (!$tokenData) {
            flash('error', 'خطا در احراز هویت گوگل. لطفاً دوباره تلاش کنید.');
            redirect(self::PATH_LOGIN);
            return;
        }

        $googleUser = GoogleAuth::getUserInfo($tokenData['access_token']);
        if (!$googleUser) {
            flash('error', 'خطا در دریافت اطلاعات کاربر از گوگل.');
            redirect(self::PATH_LOGIN);
            return;
        }

        $user = GoogleAuth::findOrCreateUser($googleUser);
        Auth::login($user['id'], $user);
        $this->syncSessionWishlist();

        flash('success', 'با موفقیت وارد شدید!');
        $redirect = $_SESSION['redirect_after_login'] ?? self::PATH_DASHBOARD;
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

    public function showVerifyOtp(): void
    {
        if (Auth::check()) {
            redirect(self::PATH_DASHBOARD);
        }

        $phone = $_GET['phone'] ?? $_SESSION['verify_phone'] ?? '';
        if (empty($phone)) {
            flash('error', 'شماره تلفن یافت نشد. لطفاً دوباره ثبت‌نام کنید.');
            redirect(self::PATH_REGISTER);
            return;
        }

        $this->view('auth/verify-otp', ['phone' => $phone]);
    }

    public function verifyOtp(): void
    {
        if (Auth::check()) {
            redirect(self::PATH_DASHBOARD);
            return;
        }

        $phone = $_POST['phone'] ?? $_SESSION['verify_phone'] ?? '';
        $code = trim($_POST['code'] ?? '');

        if (empty($phone) || empty($code)) {
            $this->redirectWithErrors('/verify-otp?phone=' . urlencode($phone), ['code' => 'کد تأیید را وارد کنید.']);
            $_SESSION['verify_phone'] = $phone;
            return;
        }

        $smsService = new SmsService();
        $valid = $smsService->verifyCode($phone, $code, 'register');

        if (!$valid) {
            $this->redirectWithErrors('/verify-otp?phone=' . urlencode($phone), ['code' => 'کد تأیید نادرست یا منقضی شده است.']);
            $_SESSION['verify_phone'] = $phone;
            return;
        }

        unset($_SESSION['verify_phone']);

        $user = Database::fetch("SELECT * FROM users WHERE phone = ?", [$phone]);
        if (!$user) {
            flash('error', 'کاربر یافت نشد.');
            redirect(self::PATH_REGISTER);
            return;
        }

        Auth::login($user['id'], $user);
        $this->syncSessionWishlist();

        flash('success', 'ثبت‌نام با موفقیت تأیید شد. خوش آمدید!');
        redirect(self::PATH_DASHBOARD);
    }
}
