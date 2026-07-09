<?php

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $this->view('auth/login');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $this->view('auth/register');
    }

    public function login(): void
    {
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($phone) || empty($password)) {
            $this->redirectWithErrors('/login', ['phone' => 'شماره تلفن و رمز عبور را وارد کنید.']);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE phone = ?", [$phone]);

        if (!$user || !Auth::verify($password, $user['password'])) {
            $this->redirectWithErrors('/login', ['password' => 'شماره تلفن یا رمز عبور اشتباه است.']);
            return;
        }

        Auth::login($user['id'], $user);

        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    }

    public function register(): void
    {
        $name = sanitize($_POST['name'] ?? '');
        $family = sanitize($_POST['family'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = $this->validate(
            compact('name', 'family', 'phone', 'password'),
            ['name' => 'required|min:2', 'family' => 'required|min:2', 'phone' => 'required|min:10', 'password' => 'required|min:6']
        );

        if (!empty($errors)) {
            $this->redirectWithErrors('/register', $errors);
            return;
        }

        $existing = Database::fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
        if ($existing) {
            $this->redirectWithErrors('/register', ['phone' => 'این شماره تلفن قبلاً ثبت شده است.']);
            return;
        }

        $userId = Database::insert('users', [
            'name' => $name,
            'family' => $family,
            'phone' => $phone,
            'password' => Auth::hash($password),
            'level' => 'نقره‌ای',
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
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($phone)) {
            $this->redirectWithErrors('/login', ['phone' => 'شماره تلفن را وارد کنید.']);
            return;
        }

        $user = Database::fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
        if (!$user) {
            flash('success', 'اگر این شماره در سیستم ثبت شده باشد، پیامک بازیابی ارسال خواهد شد.');
            redirect('/login');
            return;
        }

        $newPassword = substr(bin2hex(random_bytes(4)), 0, 8);
        Database::update('users', ['password' => Auth::hash($newPassword)], 'id = :id', ['id' => $user['id']]);

        flash('success', 'رمز عبور جدید به شماره شما پیامک شد. (دمو: ' . $newPassword . ')');
        redirect('/login');
    }
}
