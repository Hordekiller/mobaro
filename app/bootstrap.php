<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
}

$appDebug = ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'true') === 'true';
ini_set('display_errors', $appDebug ? '1' : '0');

$sessPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessPath)) {
    @mkdir($sessPath, 0777, true);
}
session_save_path($sessPath);
Auth::start();
csrf();

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

Cache::init();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

set_exception_handler(function (Throwable $e) {
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
        if (str_contains($message, 'Undefined array key') || str_contains($message, 'Undefined index')) {
            return null;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
