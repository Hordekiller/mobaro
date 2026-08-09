<?php

declare(strict_types=1);

use App\Config;
use App\Database;
use App\Settings;

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
    return url('assets/' . ltrim($path, '/'));
}

function url(string $path = ''): string
{
    $baseUrl = rtrim((string) Config::get('app.url', ''), '/');
    if ($baseUrl === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;
    }
    $normalizedPath = '/' . ltrim($path, '/');

    return $baseUrl . ($normalizedPath === '/' ? '/' : $normalizedPath);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $parsed = parse_url($referer);
    $host = $parsed['host'] ?? '';
    $appHost = parse_url((string) Config::get('app.url', ''), PHP_URL_HOST);
    if ($appHost === '' || $host === $appHost || $host === '') {
        header('Location: ' . $referer);
    } else {
        header('Location: /');
    }
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
    if (!isset($_SESSION['_flash_errors'][$key])) {
        return null;
    }
    $val = $_SESSION['_flash_errors'][$key];
    unset($_SESSION['_flash_errors'][$key]);
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
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    $input = str_replace($persian, $english, $input);
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function faToEnDigits(string $input): string
{
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($arabic, $english, str_replace($persian, $english, $input));
}

function normalizePhone(string $input): string
{
    $input = trim(faToEnDigits($input));
    $input = preg_replace('/[\s\-().،,]/u', '', $input);
    $input = ltrim($input, '+');

    if (!ctype_digit($input)) {
        return '';
    }

    if (str_starts_with($input, '0098')) {
        $input = substr($input, 4);
    } elseif (str_starts_with($input, '98') && strlen($input) >= 12) {
        $input = substr($input, 2);
    }

    if (strlen($input) === 10 && $input[0] === '9') {
        $input = '0' . $input;
    }

    if (!preg_match('/^09\d{9}$/', $input)) {
        return '';
    }

    return $input;
}

function phoneForSms(string $phone): string
{
    $phone = normalizePhone($phone);
    if ($phone === '' || !str_starts_with($phone, '09')) {
        return '';
    }
    return '98' . substr($phone, 1);
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
        'about' => '/about',
        'contact' => '/contact',
        'blog' => '/blog',
    ];
    return url($routes[$name] ?? '/');
}

function isActive(string $path): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $uri === $path ? 'active' : '';
}

function priceFormat(int|string $amount): string
{
    return number_format((int) $amount) . ' تومان';
}

function formatFileSize(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 0) . ' KB';
    }
    return $bytes . ' B';
}

function likePattern(string $search): string
{
    return '%' . Database::escapeLike($search) . '%';
}

function timeAgo(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) {
        $result = 'لحظاتی پیش';
    } elseif ($diff < 3600) {
        $result = floor($diff / 60) . ' دقیقه پیش';
    } elseif ($diff < 86400) {
        $result = floor($diff / 3600) . ' ساعت پیش';
    } else {
        $result = jdate('Y/m/d', $timestamp);
    }
    return $result;
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
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function faNum(int|string|float $num): string
{
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    if (is_float($num)) {
        $num = normalizeDecimal($num);
    } elseif (is_string($num) && str_contains($num, '.') && is_numeric($num)) {
        $num = normalizeDecimal((float) $num);
    }
    return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $persian, (string) $num);
}

function normalizeDecimal(int|string|float $num): string
{
    return rtrim(rtrim(sprintf('%.2F', (float) $num), '0'), '.');
}

function getYoutubeId(string $url): string
{
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}

function getAparatHash(string $url): string
{
    preg_match('/aparat\.com\/v\/([a-zA-Z0-9_-]+)/', $url, $matches);
    if (!empty($matches[1])) {
        return $matches[1];
    }
    preg_match('/videohash\/([a-zA-Z0-9_-]+)/', $url, $matches);
    return $matches[1] ?? '';
}

function getVideoEmbedHtml(string $url, string $type = 'upload'): string
{
    if ($type === 'youtube') {
        $id = getYoutubeId($url);
        if ($id) {
            return '<iframe class="w-full h-full" src="https://www.youtube.com/embed/' . e($id) . '" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
        }
    }
    if ($type === 'aparat') {
        $hash = getAparatHash($url);
        if ($hash) {
            return '<iframe class="w-full h-full" src="https://www.aparat.com/video/video/embed/videohash/' . e($hash) . '/vt/frame" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
        }
    }
    return '<video controls class="w-full h-full object-contain"><source src="' . e($url) . '" type="video/mp4">مرورگر شما پخش ویدیو را پشتیبانی نمی‌کند.</video>';
}

function jsEscape(string $value): string
{
    return addcslashes($value, "\\\'\n\r\t/");
}

function jsonScript(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    if (empty($text)) {
        $text = 'item-' . bin2hex(random_bytes(4));
    }
    return $text;
}

function smsStatusLabel(string $status): string
{
    return match (strtolower($status)) {
        'pending' => 'در انتظار',
        'processing' => 'در حال پردازش',
        'confirmed' => 'تأیید شد',
        'shipped' => 'ارسال شد',
        'delivered' => 'تحویل شد',
        'completed', 'done' => 'تکمیل شد',
        'cancelled' => 'لغو شد',
        'rejected' => 'رد شد',
        'failed' => 'ناموفق',
        'active' => 'فعال',
        default => $status,
    };
}

function smsOwnerPhone(): string
{
    return normalizePhone(Settings::get('sms_admin_phone', ''));
}

function smsTemplateVariablesLabel(string $variables): string
{
    $decoded = json_decode($variables, true);
    if (is_array($decoded)) {
        return implode(', ', array_map('strval', $decoded));
    }
    return $variables;
}
