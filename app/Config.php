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
            'brand.phone' => '۰۲۱-۲۲۸۸۴۲۶۷',
            'brand.address' => 'تهران، خیابان ولیعصر، پلاک ۱۲۸',
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

            // Cache
            'cache.prefix' => env('CACHE_PREFIX') ?: 'mobaro',
            'cache.dir' => ($d = env('CACHE_DIR')) ? $d : __DIR__ . '/../storage/cache',
            'cache.ttl.default' => (int) (env('CACHE_TTL_DEFAULT') ?: 3600),
            'cache.ttl.page' => (int) (env('CACHE_TTL_PAGE') ?: 600),
            'cache.ttl.admin' => (int) (env('CACHE_TTL_ADMIN') ?: 300),
        ];
    }
}
