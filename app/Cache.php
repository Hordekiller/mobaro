<?php

class Cache
{
    private const VERSION_DIR = 'versions';

    private static string $dir;
    private static array $local = [];
    private static array $localExpiry = [];
    private static int $failCount = 0;
    private static int $failTime = 0;

    public static function init(): void
    {
        self::$dir = Config::get('cache.dir', __DIR__ . '/../storage/cache');
        if (!is_dir(self::$dir)) {
            @mkdir(self::$dir, 0775, true);
            @mkdir(self::$dir . '/locks', 0775, true);
            @mkdir(self::$dir . '/tags', 0775, true);
            @mkdir(self::$dir . '/' . self::VERSION_DIR, 0775, true);
            file_put_contents(self::$dir . '/.gitignore', "*\n");
        }
        if (random_int(1, 100) === 1) {
            self::cleanup();
        }
    }

    public static function remember(string $key, int $ttl, callable $fn, array|string $tags = []): mixed
    {
        $ns = self::ns($key);

        if (array_key_exists($ns, self::$local)) {
            $exp = self::$localExpiry[$ns] ?? 0;
            if ($exp === 0 || time() <= $exp) {
                return self::$local[$ns];
            }
            unset(self::$local[$ns], self::$localExpiry[$ns]);
        }

        $entry = self::readEntry($key);
        if ($entry !== null) {
            self::$local[$ns] = $entry['v'];
            self::$localExpiry[$ns] = $entry['e'];
            return $entry['v'];
        }

        $jitter = max(1, intdiv($ttl, 10));
        $effectiveTtl = $ttl + random_int(0, $jitter);

        if (!self::lock($key)) {
            usleep(100_000);
            $entry = self::readEntry($key);
            if ($entry !== null) {
                self::$local[$ns] = $entry['v'];
                self::$localExpiry[$ns] = $entry['e'];
                return $entry['v'];
            }
            return $fn();
        }

        try {
            $value = $fn();
            self::set($key, $value, $effectiveTtl);
            $tagList = is_array($tags) ? $tags : [$tags];
            foreach ($tagList as $t) {
                self::tag($t, $key);
            }
            return $value;
        } finally {
            self::unlock($key);
        }
    }

    public static function get(string $key): mixed
    {
        $ns = self::ns($key);

        if (array_key_exists($ns, self::$local)) {
            $exp = self::$localExpiry[$ns] ?? 0;
            if ($exp === 0 || time() <= $exp) {
                return self::$local[$ns];
            }
            unset(self::$local[$ns], self::$localExpiry[$ns]);
        }

        $entry = self::readEntry($key);
        if ($entry !== null) {
            self::$local[$ns] = $entry['v'];
            self::$localExpiry[$ns] = $entry['e'];
            return $entry['v'];
        }

        return null;
    }

    public static function has(string $key): bool
    {
        $ns = self::ns($key);

        if (array_key_exists($ns, self::$local)) {
            $exp = self::$localExpiry[$ns] ?? 0;
            if ($exp === 0 || time() <= $exp) {
                return true;
            }
            unset(self::$local[$ns], self::$localExpiry[$ns]);
        }

        return self::readEntry($key) !== null;
    }

    public static function set(string $key, mixed $value, int $ttl = 0): void
    {
        $ns = self::ns($key);
        $exp = $ttl > 0 ? time() + $ttl : ($ttl < 0 ? time() - 1 : 0);
        self::$local[$ns] = $value;
        self::$localExpiry[$ns] = $exp;

        $entry = [
            'ver' => self::version(),
            'e' => $exp,
            'v' => $value,
        ];

        $path = self::path($key);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $written = @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($written === false) {
            self::logFailure('file_put_contents', $path);
        }
    }

    public static function forget(string $key): void
    {
        $ns = self::ns($key);
        unset(self::$local[$ns], self::$localExpiry[$ns]);

        $path = self::path($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function flush(): void
    {
        self::$local = [];
        self::$localExpiry = [];
        $dir = self::dir();
        foreach (glob($dir . '/*/*.json') as $f) @unlink($f);
        foreach (glob($dir . '/tags/*.tag') as $f) @unlink($f);
        foreach (glob($dir . '/' . self::VERSION_DIR . '/*.ver') as $f) @unlink($f);
    }

    public static function flushByTag(string $tag): void
    {
        $tagPath = self::dir() . '/tags/' . md5(self::prefix() . ':' . $tag) . '.tag';
        if (!is_file($tagPath)) {
            return;
        }

        $keys = @file($tagPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($keys as $key) {
            self::forget($key);
        }
        @unlink($tagPath);
    }

    public static function tag(string $tag, string $key): void
    {
        $tagDir = self::dir() . '/tags';
        if (!is_dir($tagDir)) {
            @mkdir($tagDir, 0775, true);
        }

        $tagPath = $tagDir . '/' . md5(self::prefix() . ':' . $tag) . '.tag';
        $existing = [];
        if (is_file($tagPath)) {
            $existing = file($tagPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        }

        if (!in_array($key, $existing, true)) {
            file_put_contents($tagPath, $key . "\n", FILE_APPEND | LOCK_EX);
        }
    }

    public static function version(): int
    {
        $verFile = self::dir() . '/' . self::VERSION_DIR . '/default.ver';
        if (!is_file($verFile)) {
            return 1;
        }
        return (int) @file_get_contents($verFile) ?: 1;
    }

    public static function bumpVersion(): void
    {
        $verDir = self::dir() . '/' . self::VERSION_DIR;
        if (!is_dir($verDir)) {
            @mkdir($verDir, 0775, true);
        }
        file_put_contents($verDir . '/default.ver', (string) (self::version() + 1), LOCK_EX);
        self::$local = [];
        self::$localExpiry = [];
    }

    public static function cleanup(): void
    {
        $dir = self::dir();
        $now = time();
        $currentVer = self::version();
        foreach (glob($dir . '/*/*.json') as $path) {
            $data = @file_get_contents($path);
            if ($data === false) continue;
            $entry = json_decode($data, true);
            if (!$entry) continue;
            if ($entry['ver'] < $currentVer || ($entry['e'] !== 0 && $now > $entry['e'])) {
                @unlink($path);
            }
        }
    }

    private static function readEntry(string $key): ?array
    {
        $path = self::path($key);
        if (!is_file($path)) return null;

        $data = @file_get_contents($path);
        if ($data === false) {
            self::logFailure('file_get_contents', $path);
            return null;
        }

        $entry = json_decode($data, true);
        if (!$entry || !isset($entry['ver'], $entry['e'])) {
            return null;
        }

        $currentVer = self::version();
        if ($entry['ver'] < $currentVer) {
            @unlink($path);
            return null;
        }

        if ($entry['e'] !== 0 && time() > $entry['e']) {
            @unlink($path);
            return null;
        }

        return $entry;
    }

    private static function prefix(): string
    {
        return Config::get('cache.prefix', 'mobaro');
    }

    private static function ns(string $key): string
    {
        return self::prefix() . ':' . $key;
    }

    private static function path(string $key): string
    {
        return self::dir() . '/' . self::safeFileName($key) . '.json';
    }

    private static function safeFileName(string $key): string
    {
        $hash = md5(self::ns($key));
        return substr($hash, 0, 2) . '/' . $hash;
    }

    private static function dir(): string
    {
        if (empty(self::$dir)) {
            self::init();
        }
        return self::$dir;
    }

    private static function lock(string $key): bool
    {
        $lockFile = self::dir() . '/locks/' . md5(self::ns($key)) . '.lock';
        $lockDir = dirname($lockFile);
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0775, true);
        }

        $fp = @fopen($lockFile, 'c');
        if (!$fp) {
            return true;
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return false;
        }

        fwrite($fp, (string) getmypid());
        fclose($fp);
        return true;
    }

    private static function unlock(string $key): void
    {
        $lockFile = self::dir() . '/locks/' . md5(self::ns($key)) . '.lock';
        if (is_file($lockFile)) {
            @unlink($lockFile);
        }
    }

    private static function logFailure(string $operation, string $path): void
    {
        $now = time();
        if ($now - self::$failTime > 60) {
            self::$failCount = 0;
            self::$failTime = $now;
        }
        self::$failCount++;

        if (self::$failCount <= 3) {
            error_log("Cache {$operation} failed: {$path}");
        } elseif (self::$failCount === 4) {
            error_log("Cache persistent failure detected ({$operation}). Suppressing further warnings.");
        }
    }
}
