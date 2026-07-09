<?php

class NewsletterController
{
    public function subscribe(): void
    {
        $email = sanitize($_POST['email'] ?? '');

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'ایمیل یا شماره تماس را وارد کنید.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $existing = Database::fetch("SELECT id FROM newsletter WHERE email = ?", [$email]);
        if ($existing) {
            echo json_encode(['success' => true, 'message' => 'شما قبلاً عضو خبرنامه شده‌اید.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        Database::insert('newsletter', ['email' => $email]);
        echo json_encode(['success' => true, 'message' => 'به خبرنامه ما خوش آمدید!'], JSON_UNESCAPED_UNICODE);
    }
}
