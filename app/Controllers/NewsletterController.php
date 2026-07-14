<?php

class NewsletterController extends BaseController
{
    public function subscribe(): void
    {
        $this->verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (RateLimiter::isLocked('newsletter:' . $ip, 3, 15)) {
            http_response_code(429);
            echo json_encode(['error' => 'درخواست‌های شما بیش از حد مجاز است. لطفاً چند دقیقه صبر کنید.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        RateLimiter::recordAttempt('newsletter:' . $ip);

        if (Captcha::isEnabled('newsletter') && !Captcha::verify($_POST['captcha'] ?? '')) {
            http_response_code(400);
            echo json_encode(['error' => 'کد امنیتی اشتباه است.', 'captcha_error' => true], JSON_UNESCAPED_UNICODE);
            return;
        }

        $email = sanitize($_POST['contact'] ?? $_POST['email'] ?? '');

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
