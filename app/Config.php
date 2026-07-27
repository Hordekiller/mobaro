<?php

class Config
{
    public const VERSION = '1.0.2';

    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (empty(self::$cache)) {
            self::load();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function reset(): void
    {
        self::$cache = [];
    }

    private static function load(): void
    {
        self::$cache = [

            // Database
            'db.host' => env('DB_HOST', 'localhost'),
            'db.name' => env('DB_NAME', ''),
            'db.user' => env('DB_USER', ''),
            'db.pass' => env('DB_PASS', ''),
            'db.charset' => env('DB_CHARSET', 'utf8mb4'),

            // App
            'app.name' => env('APP_NAME', 'Rozhin'),
            'app.url' => rtrim((string) env('APP_URL', ''), '/'),
            'app.env' => env('APP_ENV', 'production'),
            'app.debug' => env('APP_DEBUG', 'false') === 'true',
            'app.version' => self::VERSION,

            // Upload
            'upload.maxSize' => 5 * 1024 * 1024,
            'upload.allowedTypes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'upload.path' => __DIR__ . '/../public/uploads',

            // ZarinPal
            'zarinpal.merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
            'zarinpal.sandbox' => env('ZARINPAL_SANDBOX', 'true') === 'true',

            // SMS (sms.ir)
            'sms.api_key' => env('SMS_API_KEY', ''),
            'sms.sender' => env('SMS_SENDER', ''),
            'sms.template_id' => (int) (env('SMS_TEMPLATE_ID', '0') ?: '0'),
            'sms.enabled' => env('SMS_ENABLED', 'false') === 'true',
            'sms.otp_ttl' => (int) (env('SMS_OTP_TTL', '180') ?: '180'),
            'sms.otp_length' => (int) (env('SMS_OTP_LENGTH', '5') ?: '5'),
            'sms.line_number' => env('SMS_LINE_NUMBER', ''),

            // Cache
            'cache.prefix' => env('CACHE_PREFIX') ?: 'mobaro',
            'cache.dir' => ($d = env('CACHE_DIR')) ? $d : __DIR__ . '/../storage/cache',
            'cache.ttl.default' => (int) (env('CACHE_TTL_DEFAULT') ?: 3600),
            'cache.ttl.page' => (int) (env('CACHE_TTL_PAGE') ?: 600),
            'cache.ttl.admin' => (int) (env('CACHE_TTL_ADMIN') ?: 300),
        ];
    }
}
