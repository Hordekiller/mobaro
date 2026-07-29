<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Settings;
use App\SEOService;
use App\RateLimiter;
use App\Database;

class ContactController extends BaseController
{
    private const PATH_CONTACT = '/contact';

    public function index(): void
    {
        $settings = Settings::all();
        $seo = SEOService::forPage('contact');

        $pageTitle = $settings['contact_header_text'] ?? 'تماس با ما';
        $this->view('contact/index', compact('settings', 'seo', 'pageTitle'));
    }

    public function send(): void
    {
        $this->verifyCsrf();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (RateLimiter::isLocked('contact:' . $ip, 5, 15)) {
            flash('error', 'درخواست‌های شما بیش از حد مجاز است. لطفاً چند دقیقه صبر کنید.');
            redirect(self::PATH_CONTACT);
            return;
        }
        RateLimiter::recordAttempt('contact:' . $ip);

        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'نام را وارد کنید.';
        }
        if (empty($email) && empty($phone)) {
            $errors['email'] = 'ایمیل یا تلفن را وارد کنید.';
        }
        if (empty($message)) {
            $errors['message'] = 'پیام را وارد کنید.';
        }

        if (!empty($errors)) {
            $this->redirectWithErrors(self::PATH_CONTACT, $errors);
            return;
        }

        Database::insert('contact_messages', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
        ]);

        flash('success', 'پیام شما با موفقیت ارسال شد. در اسرع وقت با شما تماس خواهیم گرفت.');
        redirect(self::PATH_CONTACT);
    }
}
