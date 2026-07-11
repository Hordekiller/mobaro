<?php

class ContactController extends BaseController
{
    public function index(): void
    {
        $settings = Settings::all();

        $pageTitle = $settings['contact_header_text'] ?? 'تماس با ما';
        $this->view('contact/index', compact('settings', 'pageTitle'));
    }

    public function send(): void
    {
        $this->verifyCsrf();

        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        $errors = [];
        if (empty($name)) $errors['name'] = 'نام را وارد کنید.';
        if (empty($email) && empty($phone)) $errors['email'] = 'ایمیل یا تلفن را وارد کنید.';
        if (empty($message)) $errors['message'] = 'پیام را وارد کنید.';

        if (!empty($errors)) {
            $this->redirectWithErrors('/contact', $errors);
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
        redirect('/contact');
    }
}
