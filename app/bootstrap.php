<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: blob: https:; frame-src https://www.youtube.com https://www.aparat.com; connect-src 'self';");
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

$uri = $_SERVER['REQUEST_URI'] ?? '';
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' &&
    !str_starts_with($uri, '/admin') &&
    !str_starts_with($uri, '/dashboard') &&
    !str_starts_with($uri, '/api')
) {
    header('Cache-Control: public, max-age=300, stale-while-revalidate=60');
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
    require __DIR__ . '/views/layouts/header.php';
    $errorMessage = 'خطایی غیرمنتظره رخ داده است. لطفاً دوباره تلاش کنید.';
    require __DIR__ . '/views/errors/500.php';
    require __DIR__ . '/views/layouts/footer.php';
    exit;
});

if (Config::get('app.debug')) {
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
