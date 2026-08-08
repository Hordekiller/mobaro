<?php

declare(strict_types=1);

namespace App\Services;

use App\Cache;
use App\Settings;

/**
 * Best-effort IndexNow notifier for Bing, Yandex, Seznam and Naver.
 *
 * Fired from SitemapController::index() when the sitemap is regenerated, so
 * changed URLs are announced right after content edits. Throttled to one
 * submission per 6 hours (via an auto-expiring cache key) and fully isolated:
 * any failure — blocked outbound HTTP from Iranian hosts, timeouts, a bad key —
 * is swallowed so it can never slow down or break the sitemap response.
 *
 * Per the 2026 sitemap guidance, Google's legacy /ping endpoint is gone, so
 * IndexNow is the modern equivalent for instant re-crawl notifications.
 */
class SitemapNotifier
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';
    private const THROTTLE_KEY = 'sitemap_indexnow_throttle';
    private const THROTTLE_SECONDS = 21600;
    private const TIMEOUT_SECONDS = 2;

    /**
     * @param list<string> $locs absolute URLs to announce (deduplicated)
     */
    public static function notify(array $locs): void
    {
        try {
            if (!self::enabled() || $locs === []) {
                return;
            }

            $key = trim((string) Settings::get('indexnow_key', ''));
            if (!preg_match('/^[a-zA-Z0-9_\-]{8,64}$/', $key)) {
                return;
            }

            if (!self::ensureKeyFile($key)) {
                return;
            }

            if (Cache::has(self::THROTTLE_KEY)) {
                return;
            }

            $host = parse_url($locs[0], PHP_URL_HOST);
            if (!is_string($host) || $host === '') {
                return;
            }

            $payload = json_encode([
                'host' => $host,
                'key' => $key,
                'keyLocation' => 'https://' . $host . '/' . $key . '.txt',
                'urlList' => array_values(array_slice($locs, 0, 10000)),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json; charset=utf-8\r\nContent-Length: " . strlen((string) $payload),
                    'content' => $payload,
                    'timeout' => self::TIMEOUT_SECONDS,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            @file_get_contents(self::ENDPOINT, false, $context);

            Cache::set(self::THROTTLE_KEY, time(), self::THROTTLE_SECONDS);
        } catch (\Throwable $e) {
            error_log('IndexNow notify failed: ' . $e->getMessage());
        }
    }

    private static function enabled(): bool
    {
        return (bool) Settings::get('sitemap_ping_enabled', false);
    }

    /**
     * Makes sure public/{key}.txt exists (crawlers verify the key at that
     * URL). Returns false when the directory is not writable.
     */
    private static function ensureKeyFile(string $key): bool
    {
        $path = __DIR__ . '/../../public/' . $key . '.txt';
        if (!is_file($path)) {
            $written = @file_put_contents($path, $key);
            if ($written === false) {
                return false;
            }
        }

        return true;
    }
}
