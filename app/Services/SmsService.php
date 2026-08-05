<?php

declare(strict_types=1);

namespace App\Services;

use App\Settings;
use App\Config;
use App\Database;
use App\Auth;
use Throwable;

class SmsService
{
    private const BASE_URL = 'https://api.sms.ir/v1';
    private const MSG_API_KEY_MISSING = 'کلید API تنظیم نشده است.';
    private const MSG_NOT_CONFIGURED = 'سرویس پیامک فعال نیست.';
    private const BULK_MAX = 100;

    private string $apiKey;
    private int $templateId;
    private string $lineNumber;

    public function __construct()
    {
        $this->apiKey = Settings::get('sms_api_key', '') ?: Config::get('sms.api_key', '');
        $this->templateId = (int) (Settings::get('sms_template_id', '0') ?: Config::get('sms_template_id', '0'));
        $this->lineNumber = Settings::get('sms_line_number', '') ?: Config::get('sms.line_number', '');
    }

    public function isConfigured(): bool
    {
        return Settings::get('sms_enabled', '0') === '1' && !empty($this->apiKey);
    }

    public function getOtpTtl(): int
    {
        return (int) (Settings::get('sms_otp_ttl', '180') ?: Config::get('sms.otp_ttl', '180'));
    }

    public function getOtpLength(): int
    {
        return (int) (Settings::get('sms_otp_length', '5') ?: Config::get('sms.otp_length', '5'));
    }

    public function generateOtp(): string
    {
        $length = $this->getOtpLength();
        $min = (int) str_repeat('1', $length);
        $max = (int) str_repeat('9', $length);
        return (string) random_int($min, $max);
    }

    public function sendVerify(string $phone, string $code, array $parameters = []): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => self::MSG_NOT_CONFIGURED];
        }

        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => self::MSG_API_KEY_MISSING];
        }

        $mobile = phoneForSms($phone);
        if ($mobile === '') {
            return ['status' => false, 'message' => 'شماره موبایل نامعتبر است.'];
        }

        if ($this->templateId <= 0) {
            return ['status' => false, 'message' => 'شناسه قالب OTP (Template ID) در تنظیمات پیامک تنظیم نشده است.'];
        }

        $params = [
            'mobile' => $mobile,
            'templateId' => $this->templateId,
            'parameters' => !empty($parameters) ? $parameters : [
                ['name' => 'Code', 'value' => $code],
            ],
        ];

        $result = $this->request(self::BASE_URL . '/send/verify', $params);

        $this->logSms($phone, 'کد تأیید: ****', 'verify', $result, $result['status'] ? 'sent' : 'failed');

        return $result;
    }

    public function sendBulk(string $message, array $phones): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => self::MSG_NOT_CONFIGURED];
        }

        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => self::MSG_API_KEY_MISSING];
        }

        if (empty($this->lineNumber)) {
            return ['status' => false, 'message' => 'شماره خط ارسال تنظیم نشده است.'];
        }

        $normalizedPhones = array_values(array_unique(array_map('normalizePhone', $phones)));
        $normalizedPhones = array_values(array_filter($normalizedPhones));

        if (empty($normalizedPhones)) {
            return ['status' => false, 'message' => 'شماره موبایل نامعتبر است.'];
        }

        $apiPhones = array_map('phoneForSms', $normalizedPhones);
        $chunks = self::chunkPhones($apiPhones);

        $errors = [];
        $sentCount = 0;

        foreach ($chunks as $chunk) {
            $params = [
                'lineNumber' => (int) $this->lineNumber,
                'messageText' => $message,
                'mobiles' => $chunk,
            ];

            $result = $this->request(self::BASE_URL . '/send/bulk', $params);

            foreach ($chunk as $apiPhone) {
                $canonical = '0' . substr($apiPhone, 2);
                $this->logSms($canonical, $message, 'bulk', $result, $result['status'] ? 'sent' : 'failed');
            }

            if ($result['status']) {
                $sentCount += count($chunk);
            } else {
                $errors[] = $result['message'];
            }
        }

        if ($sentCount === 0) {
            return [
                'status' => false,
                'message' => $errors ? implode(' | ', array_values(array_unique($errors))) : 'خطا در ارسال پیامک.',
            ];
        }

        return [
            'status' => true,
            'sent' => $sentCount,
            'message' => empty($errors) ? 'موفق' : 'موفق با ' . count($errors) . ' خطا',
            'errors' => $errors,
        ];
    }

    public function sendSingle(string $phone, string $message): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => self::MSG_NOT_CONFIGURED];
        }

        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => self::MSG_API_KEY_MISSING];
        }

        if (empty($this->lineNumber)) {
            return ['status' => false, 'message' => 'شماره خط ارسال تنظیم نشده است.'];
        }

        $mobile = $this->normalizePhone($phone);
        if ($mobile === '') {
            return ['status' => false, 'message' => 'شماره موبایل نامعتبر است.'];
        }

        $params = [
            'lineNumber' => (int) $this->lineNumber,
            'messageText' => $message,
            'mobiles' => [$mobile],
        ];

        $result = $this->request(self::BASE_URL . '/send/bulk', $params);

        $canonical = '0' . substr($mobile, 2);
        $this->logSms($canonical, $message, 'bulk', $result, $result['status'] ? 'sent' : 'failed');

        return $result;
    }

    public function sendTemplate(string $slug, array $recipients, array $variables = []): array
    {
        try {
            $template = Database::fetch(
                "SELECT * FROM sms_templates WHERE slug = ? AND is_active = 1 LIMIT 1",
                [$slug]
            );
        } catch (Throwable $e) {
            error_log("SMS template lookup failed: " . $e->getMessage());
            return ['status' => false, 'message' => 'خطا در خواندن قالب پیامک.'];
        }

        if (!$template) {
            return ['status' => false, 'message' => 'قالب پیامک یافت نشد: ' . $slug];
        }

        $message = self::renderTemplate($template['body'] ?? '', $variables);
        if ($message === '') {
            return ['status' => false, 'message' => 'متن قالب پیامک خالی است.'];
        }

        $recipients = array_values(array_unique(array_filter(array_map('normalizePhone', $recipients))));
        if (empty($recipients)) {
            return ['status' => false, 'message' => 'شماره موبایل معتبری یافت نشد.'];
        }

        if (count($recipients) === 1) {
            return $this->sendSingle($recipients[0], $message);
        }

        return $this->sendBulk($message, $recipients);
    }

    public static function renderTemplate(string $body, array $variables = []): string
    {
        $message = $body;
        foreach ($variables as $key => $value) {
            $message = str_ireplace('{' . $key . '}', (string) $value, $message);
        }
        $message = preg_replace('/\{[A-Za-z_\x{0600}-\x{06FF}][A-Za-z0-9_\x{0600}-\x{06FF}]*\}/u', '', $message);
        return trim((string) $message);
    }

    public static function chunkPhones(array $phones): array
    {
        return array_chunk(array_values($phones), self::BULK_MAX);
    }

    public static function notify(string $slug, array $recipients, array $variables = []): array
    {
        try {
            $service = new self();
            if (!$service->isConfigured()) {
                return ['status' => false, 'message' => self::MSG_NOT_CONFIGURED];
            }
            return $service->sendTemplate($slug, $recipients, $variables);
        } catch (Throwable $e) {
            error_log("SMS notify failed: " . $e->getMessage());
            return ['status' => false, 'message' => 'خطا در ارسال پیامک اطلاع‌رسانی.'];
        }
    }

    public function getCredit(): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => self::MSG_API_KEY_MISSING];
        }

        return $this->request(self::BASE_URL . '/credit', [], 'GET');
    }

    public function getLines(): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => self::MSG_API_KEY_MISSING];
        }

        return $this->request(self::BASE_URL . '/line', [], 'GET');
    }

    public function createVerificationCode(string $phone, string $purpose = 'register'): ?string
    {
        $code = $this->generateOtp();
        $ttl = $this->getOtpTtl();
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        try {
            Database::query(
                "DELETE FROM verification_codes WHERE phone = ? AND purpose = ?",
                [$phone, $purpose]
            );

            Database::insert('verification_codes', [
                'phone' => $phone,
                'code' => $code,
                'purpose' => $purpose,
                'expires_at' => $expiresAt,
            ]);

            $smsResult = $this->sendVerify($phone, $code);

            if (!$smsResult['status']) {
                $masked = substr($phone, 0, 4) . '****' . substr($phone, -2);
                error_log("SMS send failed for {$masked}");
            }

            return $code;
        } catch (Throwable $e) {
            error_log("OTP creation failed: " . $e->getMessage());
            return null;
        }
    }

    public function verifyCode(string $phone, string $code, string $purpose = 'register'): bool
    {
        try {
            $record = Database::fetch(
                "SELECT id FROM verification_codes
                 WHERE phone = ? AND code = ? AND purpose = ? AND used = 0 AND expires_at > NOW()
                 ORDER BY id DESC LIMIT 1",
                [$phone, $code, $purpose]
            );

            if (!$record) {
                return false;
            }

            Database::query(
                "UPDATE verification_codes SET used = 1 WHERE id = ?",
                [$record['id']]
            );

            if ($purpose === 'register') {
                Database::query(
                    "UPDATE users SET phone_verified = 1 WHERE phone = ?",
                    [$phone]
                );
            }

            return true;
        } catch (Throwable $e) {
            error_log("OTP verification failed: " . $e->getMessage());
            return false;
        }
    }

    public function isPhoneVerified(string $phone): bool
    {
        try {
            $user = Database::fetch("SELECT phone_verified FROM users WHERE phone = ?", [$phone]);
            return $user && (int) ($user['phone_verified'] ?? 0) === 1;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        return phoneForSms($phone);
    }

    private function request(string $url, array $params = [], string $method = 'POST'): array
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $this->apiKey,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => 'Mobaro/' . Config::get('app.version', '1.0'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method === 'POST' && !empty($params)) {
            $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
                'Content-Length: ' . strlen($jsonData),
            ]));
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($err) {
            error_log("SMS API error: {$err}");
            return ['status' => false, 'message' => 'خطا در اتصال به سرویس پیامک: ' . $err];
        }

        $response = json_decode($result, true);

        if (!is_array($response)) {
            return ['status' => false, 'message' => 'پاسخ نامعتبر از سرویس پیامک'];
        }

        if (isset($response['status']) && $response['status'] === 1) {
            return ['status' => true, 'data' => $response['data'] ?? [], 'message' => $response['message'] ?? 'موفق'];
        }

        $errorMessage = $response['message'] ?? 'خطای ناشناخته';
        $errorCode = $response['status'] ?? $httpCode;
        error_log("SMS API failed: status={$errorCode}, message={$errorMessage}");
        return ['status' => false, 'message' => $errorMessage, 'code' => $errorCode];
    }

    private function logSms(string $phone, string $message, string $type, array $apiResult, string $status = 'sent'): void
    {
        try {
            $apiMessageId = null;
            $credits = 0;

            if (isset($apiResult['data'])) {
                $data = $apiResult['data'];
                $apiMessageId = $data['messageId'] ?? $data['packId'] ?? null;
                $credits = $data['cost'] ?? 0;
            }

            $adminId = null;
            if (Auth::check() && (Auth::user()['role'] ?? 'user') === 'admin') {
                $adminId = Auth::id();
            }

            Database::insert('sms_logs', [
                'phone' => $phone,
                'message' => mb_substr($message, 0, 1000),
                'type' => $type,
                'status' => $status,
                'credits' => $credits,
                'api_message_id' => $apiMessageId ? (string) $apiMessageId : null,
                'api_response' => json_encode($apiResult, JSON_UNESCAPED_UNICODE),
                'sent_by' => $adminId,
            ]);
        } catch (Throwable $e) {
            error_log("SMS log failed: " . $e->getMessage());
        }
    }
}
