<?php

class AvatarController
{
    private static array $colors = [
        '#e11d48', '#be185d', '#9333ea', '#7c3aed',
        '#2563eb', '#0891b2', '#059669', '#d97706',
        '#dc2626', '#c026d3', '#7c2d12', '#164e63',
    ];

    public static function generate(string $name, int $size = 64): void
    {
        $cacheDir = __DIR__ . '/../../public/assets/images/cache/avatars';
        $hash = md5($name . $size);
        $cacheFile = $cacheDir . '/' . $hash . '.svg';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (file_exists($cacheFile)) {
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=604800');
            readfile($cacheFile);
            return;
        }

        $initials = self::getInitials($name);
        $color = self::getColor($name);
        $bgLight = self::lighten($color, 0.85);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" rx="' . intdiv($size, 2) . '" fill="' . $bgLight . '"/>';
        $fontSize = intdiv($size, 3);
        $svg .= '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Vazirmatn, Tahoma, sans-serif" font-size="' . $fontSize . '" font-weight="700" fill="' . $color . '">' . htmlspecialchars($initials) . '</text>';
        $svg .= '</svg>';

        file_put_contents($cacheFile, $svg);

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=604800');
        echo $svg;
        exit;
    }

    private static function getInitials(string $name): string
    {
        $name = trim($name);
        if (empty($name)) return '?';

        $parts = preg_split('/\s+/', $name);
        if (count($parts) >= 2) {
            $first = mb_substr($parts[0], 0, 1, 'UTF-8');
            $last = mb_substr(end($parts), 0, 1, 'UTF-8');
            return mb_strtoupper($first . $last, 'UTF-8');
        }
        return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
    }

    private static function getColor(string $name): string
    {
        $hash = crc32($name);
        return self::$colors[abs($hash) % count(self::$colors)];
    }

    private static function lighten(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, intval($r + (255 - $r) * $amount));
        $g = min(255, intval($g + (255 - $g) * $amount));
        $b = min(255, intval($b + (255 - $b) * $amount));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
