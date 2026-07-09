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
            'db.host' => getenv('DB_HOST') ?: 'localhost',
            'db.name' => getenv('DB_NAME') ?: 'mobaro',
            'db.user' => getenv('DB_USER') ?: 'root',
            'db.pass' => getenv('DB_PASS') ?: '',
            'db.charset' => 'utf8mb4',

            // App
            'app.name' => 'Mobaro',
            'app.url' => getenv('APP_URL') ?: 'http://localhost:8080',
            'app.env' => getenv('APP_ENV') ?: 'development',
            'app.debug' => (getenv('APP_DEBUG') ?: 'true') === 'true',

            // Brand
            'brand.name' => 'موبارو',
            'brand.phone' => '۰۲۱-۲۲۸۸۴۲۶۷',
            'brand.address' => 'تهران، خیابان ولیعصر، پلاک ۱۲۸',
            'brand.hours' => 'شنبه تا پنجشنبه ۹ صبح - ۸ شب',
            'brand.email' => 'info@mobaro.ir',
            'brand.instagram' => '#',
            'brand.telegram' => '#',
            'brand.linkedin' => '#',

            // Colors
            'color.primary' => '#e11d48',
            'color.primaryDark' => '#be185d',
            'color.gold' => '#D4AF37',
            'color.cream' => '#FDF6F0',
            'color.text' => '#27272A',

            // Upload
            'upload.maxSize' => 5 * 1024 * 1024,
            'upload.allowedTypes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'upload.path' => __DIR__ . '/../public/uploads',
        ];
    }
}
