<?php

class Settings
{
    private static ?array $local = null;
    private const CACHE_KEY = 'settings_all';
    private const CACHE_TTL = 86400;

    public static function all(): array
    {
        if (self::$local !== null) {
            return self::$local;
        }

        self::$local = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
                $result = [];
                foreach ($rows as $row) {
                    $result[$row['setting_key']] = $row['setting_value'];
                }
                return $result;
            } catch (Throwable $e) {
                error_log('Settings::all() DB failed: ' . $e->getMessage());
                return [];
            }
        });

        return self::$local;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function invalidate(): void
    {
        self::$local = null;
        Cache::forget(self::CACHE_KEY);
    }
}
