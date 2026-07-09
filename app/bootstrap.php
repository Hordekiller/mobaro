<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

ini_set('display_errors', env('APP_DEBUG', 'true') === 'true' ? '1' : '0');

Auth::start();

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

if (Config::get('app.debug')) {
    set_error_handler(function ($severity, $message, $file, $line) {
        if (str_contains($message, 'Undefined array key') || str_contains($message, 'Undefined index')) {
            return null;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
