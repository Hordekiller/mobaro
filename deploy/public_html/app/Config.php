<?php

class Config
{
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

            // Brand (hardcoded — not in .env)
            'brand.name' => 'موبارو',
            'brand.phone' => '۰۳۱-۳۶۶۶۲۱۲۲',
            'brand.address' => 'اصفهان، چهارباغ بالا، بن‌بست ۲۱، پلاک ۱۴، واحد ۶',
            'brand.hours' => 'شنبه تا پنجشنبه ۹ صبح - ۸ شب',
            'brand.email' => 'info@mobaro.ir',
            'brand.instagram' => '#',
            'brand.telegram' => '#',
            'brand.linkedin' => '#',

            // Colors (hardcoded theme)
            'color.primary' => '#e11d48',
            'color.primaryDark' => '#be185d',
            'color.gold' => '#D4AF37',
            'color.cream' => '#FDF6F0',
            'color.text' => '#27272A',

            // Upload
            'upload.maxSize' => 5 * 1024 * 1024,
            'upload.allowedTypes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'upload.path' => __DIR__ . '/../public/uploads',

            // ZarinPal
            'zarinpal.merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
            'zarinpal.sandbox' => env('ZARINPAL_SANDBOX', 'true') === 'true',

            // SMS (Kavenegar) — defaults from .env, overridden by database
            'sms.enabled' => env('SMS_ENABLED', 'false') === 'true',
            'sms.api_key' => env('SMS_API_KEY', ''),
            'sms.sender' => env('SMS_SENDER', ''),
            'sms.otp_ttl' => (int) (env('SMS_OTP_TTL') ?: 180),
            'sms.otp_length' => (int) (env('SMS_OTP_LENGTH') ?: 5),

            // Cache
            'cache.prefix' => env('CACHE_PREFIX') ?: 'mobaro',
            'cache.dir' => ($d = env('CACHE_DIR')) ? $d : __DIR__ . '/../storage/cache',
            'cache.ttl.default' => (int) (env('CACHE_TTL_DEFAULT') ?: 3600),
            'cache.ttl.page' => (int) (env('CACHE_TTL_PAGE') ?: 600),
            'cache.ttl.admin' => (int) (env('CACHE_TTL_ADMIN') ?: 300),
        ];

        self::loadSmsFromDb();
    }

    private static function loadSmsFromDb(): void
    {
        try {
            if (!class_exists('Database') || !class_exists('Settings')) {
                return;
            }
            $settings = Settings::all();
            $smsMap = [
                'sms_enabled' => 'sms.enabled',
                'sms_api_key' => 'sms.api_key',
                'sms_sender' => 'sms.sender',
                'sms_otp_ttl' => 'sms.otp_ttl',
                'sms_otp_length' => 'sms.otp_length',
            ];
            foreach ($smsMap as $dbKey => $configKey) {
                if (isset($settings[$dbKey]) && $settings[$dbKey] !== '') {
                    $val = $settings[$dbKey];
                    if (in_array($configKey, ['sms.otp_ttl', 'sms.otp_length'])) {
                        $val = (int) $val;
                    } elseif ($configKey === 'sms.enabled') {
                        $val = ($val === '1' || $val === 'true' || $val === true);
                    }
                    self::$cache[$configKey] = $val;
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore if DB not available yet
        }
    }
}
