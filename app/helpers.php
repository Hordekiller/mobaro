<?php

function env(string $key, mixed $default = null): mixed
{
    static $dotenv = [];
    if (empty($dotenv)) {
        $dotenv = $_ENV;
    }
    return $dotenv[$key] ?? $default;
}

function asset(string $path): string
{
    return Config::get('app.url') . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return Config::get('app.url') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function back(): void
{
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function flashErrors(array $errors): void
{
    $_SESSION['_flash_errors'] = $errors;
}

function flashError(string $key): ?string
{
    $val = $_SESSION['_flash_errors'][$key] ?? null;
    return $val;
}

function clearFlashErrors(): void
{
    unset($_SESSION['_flash_errors']);
}

function hasFlashErrors(): bool
{
    return !empty($_SESSION['_flash_errors']);
}

function csrf(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="_csrf" value="' . $_SESSION['_csrf'] . '">';
}

function verifyCsrf(string $token): bool
{
    return hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function route(string $name): string
{
    $routes = [
        'home' => '/',
        'login' => '/login',
        'register' => '/register',
        'logout' => '/logout',
        'dashboard' => '/dashboard',
        'admin' => '/admin',
        'shop' => '/shop',
        'cart' => '/cart',
        'booking' => '/booking',
    ];
    return url($routes[$name] ?? '/');
}

function isActive(string $path): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $uri === $path ? 'active' : '';
}

function priceFormat(int $amount): string
{
    return number_format($amount) . ' تومان';
}

function timeAgo(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'لحظاتی پیش';
    if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
    if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
    return jdate('Y/m/d', $timestamp);
}

function jdate(string $format, ?int $timestamp = null): string
{
    $timestamp = $timestamp ?: time();
    $date = getdate($timestamp);
    $gYear = $date['year'];
    $gMonth = $date['mon'];
    $gDay = $date['mday'];

    $jalali = gregorianToJalali($gYear, $gMonth, $gDay);

    $format = str_replace('Y', (string)$jalali[0], $format);
    $format = str_replace('m', sprintf('%02d', $jalali[1]), $format);
    $format = str_replace('d', sprintf('%02d', $jalali[2]), $format);

    $monthNames = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    $format = str_replace('F', $monthNames[(int)$jalali[1]], $format);

    return $format;
}

function gregorianToJalali(int $gYear, int $gMonth, int $gDay): array
{
    $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

    $gy = $gYear - 1600;
    $gm = $gMonth - 1;
    $gd = $gDay - 1;

    $gDayNo = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
    for ($i = 0; $i < $gm; $i++) {
        $gDayNo += $gDaysInMonth[$i];
    }
    if ($gm > 1 && (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0))) {
        $gDayNo++;
    }
    $gDayNo += $gd;

    $jDayNo = $gDayNo - 79;
    $jNp = floor($jDayNo / 12053);
    $jDayNo %= 12053;
    $jy = 979 + 33 * $jNp + 4 * floor($jDayNo / 1461);
    $jDayNo %= 1461;

    if ($jDayNo >= 366) {
        $jy += floor(($jDayNo - 1) / 365);
        $jDayNo = ($jDayNo - 1) % 365;
    }

    $jM = 0;
    for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; $i++) {
        $jDayNo -= $jDaysInMonth[$i];
        $jM++;
    }
    $jM++;
    $jD = $jDayNo + 1;

    return [$jy, $jM, $jD];
}

function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}
