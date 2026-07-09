<?php

error_reporting(E_ALL);
ini_set('display_errors', Config::get('app.debug') ? '1' : '0');

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/helpers.php';

Auth::start();

spl_autoload_register(function (string $class) {
    $paths = [
        __DIR__ . '/Controllers/' . $class . '.php',
        __DIR__ . '/Models/' . $class . '.php',
        __DIR__ . '/Middleware/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

if (Config::get('app.debug')) {
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
