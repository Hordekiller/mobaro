<?php
// Router script for PHP built-in dev server only.
// On production (Apache), .htaccess handles routing instead.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
