<?php

declare(strict_types=1);

use App\Auth;
use App\Cache;
use App\Config;
use App\RateLimiter;
use App\Settings;

error_reporting(E_ALL);

// ── Force HTTPS + non-www (PHP fallback for when .htaccess is bypassed) ──
$host = $_SERVER['HTTP_HOST'] ?? '';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
    || ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on';

$isLocalHost = in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
    || str_starts_with($host, '127.0.0.1:')
    || str_starts_with($host, 'localhost:');

if (!$isLocalHost && ($host !== 'mobaro.ir' || !$isHttps)) {
    header('Location: https://mobaro.ir' . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/SEOService.php';

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
}

$appDebug = ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'false') === 'true';
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('display_startup_errors', $appDebug ? '1' : '0');
ini_set('log_errors', '1');

$logPath = __DIR__ . '/../storage/logs';
if (!is_dir($logPath)) {
    @mkdir($logPath, 0755, true);
}
ini_set('error_log', $logPath . '/app.log');

$sessPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessPath)) {
    @mkdir($sessPath, 0755, true);
}
session_save_path($sessPath);
Auth::start();
csrf();

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

Cache::init();
RateLimiter::cleanup();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://www.google.com https://www.gstatic.com https://cdn.tiny.cloud https://www.googletagmanager.com https://www.google-analytics.com https://analytics.google.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com https://cdn.tiny.cloud; font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com https://cdn.tiny.cloud; img-src 'self' data: blob: https:; frame-src https://www.youtube.com https://www.aparat.com; connect-src 'self' https://cdn.tiny.cloud https://www.googletagmanager.com https://www.google-analytics.com https://analytics.google.com;");
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

$uri = $_SERVER['REQUEST_URI'] ?? '';
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' &&
    !str_starts_with($uri, '/admin') &&
    !str_starts_with($uri, '/dashboard') &&
    !str_starts_with($uri, '/api')
) {
    // Personalized markup (logged-in nav, cart/wishlist counters, and the CSRF
    // token in a <meta> tag) must never be served from a shared cache to another
    // visitor. Use private caching for authenticated requests.
    $cacheControl = Auth::check()
        ? 'Cache-Control: private, no-store'
        : 'Cache-Control: public, max-age=300, stale-while-revalidate=60';
    header($cacheControl);
}

set_exception_handler(function (Throwable $e) {
    error_log(sprintf(
        '[%s] %s: %s in %s:%d%s%s',
        date('c'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        PHP_EOL,
        $e->getTraceAsString()
    ));

    if (Config::get('app.debug')) {
        echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
        return;
    }
    http_response_code(500);
    $settings = Settings::all();
    require_once __DIR__ . '/views/layouts/header.php';
    $errorMessage = 'خطایی غیرمنتظره رخ داده است. لطفاً دوباره تلاش کنید.';
    require_once __DIR__ . '/views/errors/500.php';
    require_once __DIR__ . '/views/layouts/footer.php';
    exit;
});

if (Config::get('app.debug')) {
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
