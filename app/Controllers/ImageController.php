<?php

declare(strict_types=1);

namespace App\Controllers;

class ImageController
{
    private const COLOR_WHITE = '#ffffff';
    private const COLOR_ROSE = '#e11d48';
    private const SVG_HEIGHT = '" height="';
    private const SVG_FILL = '" fill="';
    private const SVG_OPACITY = '" opacity="';

    private static int $rngState = 0;

    private static array $palettes = [
        'beauty'  => ['bg' => self::COLOR_ROSE, 'fg' => self::COLOR_WHITE, 'accent' => '#fda4af'],
        'gold'    => ['bg' => '#D4AF37', 'fg' => '#1a1a1a', 'accent' => '#f5d76e'],
        'skin'    => ['bg' => '#FDF6F0', 'fg' => '#8b5c41', 'accent' => '#e8d5c4'],
        'nail'    => ['bg' => '#ec4899', 'fg' => self::COLOR_WHITE, 'accent' => '#f9a8d4'],
        'makeup'  => ['bg' => '#9333ea', 'fg' => self::COLOR_WHITE, 'accent' => '#c084fc'],
        'salon'   => ['bg' => '#374151', 'fg' => self::COLOR_ROSE, 'accent' => '#6b7280'],
        'rose'    => ['bg' => '#fda4af', 'fg' => '#1a1a1a', 'accent' => self::COLOR_ROSE],
        'dark'    => ['bg' => '#18181b', 'fg' => self::COLOR_ROSE, 'accent' => '#3f3f46'],
        'cream'   => ['bg' => '#fef3c7', 'fg' => '#92400e', 'accent' => '#fbbf24'],
        'teal'    => ['bg' => '#0d9488', 'fg' => self::COLOR_WHITE, 'accent' => '#5eead4'],
    ];

    private static array $patterns = ['circle', 'diamond', 'wave', 'dots', 'cross'];

    private static function seedRng(int $seed): void
    {
        self::$rngState = $seed !== 0 ? $seed : 1;
    }

    private static function rngInt(int $min, int $max): int
    {
        self::$rngState ^= self::$rngState << 13;
        self::$rngState ^= self::$rngState >> 17;
        self::$rngState ^= self::$rngState << 5;
        return $min + abs(self::$rngState) % ($max - $min + 1);
    }

    private static function rngPick(array $items): mixed
    {
        return $items[self::rngInt(0, count($items) - 1)];
    }

    public function random(int $width, int $height): void
    {
        $seed = random_int(1, 999999);
        self::serve($width, $height, $seed);
    }

    public function seeded(int $width, int $height, int $seed): void
    {
        self::serve($width, $height, $seed);
    }

    private const MAX_DIM = 2000;

    private static function serve(int $width, int $height, int $seed): void
    {
        if ($width < 1 || $height < 1 || $width > self::MAX_DIM || $height > self::MAX_DIM) {
            http_response_code(404);
            exit;
        }

        $cacheDir = __DIR__ . '/../../public/assets/images/cache';

        if (random_int(1, 100) === 1) {
            self::cleanupOldCache($cacheDir);
        }

        $cacheKey = "{$width}x{$height}_{$seed}.svg";
        $cacheFile = $cacheDir . '/' . $cacheKey;

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        if (file_exists($cacheFile)) {
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=86400');
            readfile($cacheFile);
            exit;
        }

        self::seedRng($seed);
        $paletteKeys = array_keys(self::$palettes);
        $palette = self::$palettes[self::rngPick($paletteKeys)];
        $pattern = self::rngPick(self::$patterns);

        $svg = self::generateSvg($width, $height, $palette, $pattern, $seed);

        @file_put_contents($cacheFile, $svg);

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo $svg;
        exit;
    }

    private static function cleanupOldCache(string $dir): void
    {
        $cutoff = time() - 86400 * 7;
        $files = glob($dir . '/*.svg');
        if (is_array($files)) {
            foreach ($files as $f) {
                if (is_file($f) && filemtime($f) < $cutoff) {
                    @unlink($f);
                }
            }
        }
        $avatarDir = $dir . '/avatars';
        if (is_dir($avatarDir)) {
            $avatarFiles = glob($avatarDir . '/*.svg');
            if (is_array($avatarFiles)) {
                foreach ($avatarFiles as $f) {
                    if (is_file($f) && filemtime($f) < $cutoff) {
                        @unlink($f);
                    }
                }
            }
        }
    }

    private static function generateSvg(int $w, int $h, array $palette, string $pattern, int $seed): string
    {
        $bg = $palette['bg'];
        $fg = $palette['fg'];
        $accent = $palette['accent'];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . self::SVG_HEIGHT . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
        $svg .= '<rect width="' . $w . self::SVG_HEIGHT . $h . self::SVG_FILL . $bg . '"/>';

        switch ($pattern) {
            case 'circle':
                for ($i = 0; $i < 5; $i++) {
                    $cx = self::rngInt(0, $w);
                    $cy = self::rngInt(0, $h);
                    $r = self::rngInt(intdiv($w, 8), intdiv($w, 2));
                    $opacity = self::rngInt(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . self::SVG_FILL . $color . self::SVG_OPACITY . $opacity . '"/>';
                }
                break;

            case 'diamond':
                for ($i = 0; $i < 4; $i++) {
                    $cx = self::rngInt(intdiv($w, 4), intdiv($w * 3, 4));
                    $cy = self::rngInt(intdiv($h, 4), intdiv($h * 3, 4));
                    $size = self::rngInt(intdiv($w, 6), intdiv($w, 2));
                    $opacity = self::rngInt(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $points = ($cx) . ',' . ($cy - $size) . ' ' .
                              ($cx + $size) . ',' . $cy . ' ' .
                              ($cx) . ',' . ($cy + $size) . ' ' .
                              ($cx - $size) . ',' . $cy;
                    $svg .= '<polygon points="' . $points . self::SVG_FILL . $color . self::SVG_OPACITY . $opacity . '"/>';
                }
                break;

            case 'wave':
                $amp = self::rngInt(intdiv($h, 10), intdiv($h, 4));
                $freq = self::rngInt(2, 6);
                for ($i = 0; $i < 3; $i++) {
                    $offset = self::rngInt(0, $h);
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $opacity = self::rngInt(8, 20) / 100;
                    $d = 'M0 ' . $offset;
                    for ($x = 0; $x <= $w; $x += 10) {
                        $y = $offset + sin(($x / $w) * M_PI * $freq + $i) * $amp;
                        $d .= ' L' . $x . ' ' . $y;
                    }
                    $d .= ' L' . $w . ' ' . $h . ' L0 ' . $h . ' Z';
                    $svg .= '<path d="' . $d . self::SVG_FILL . $color . self::SVG_OPACITY . $opacity . '"/>';
                }
                break;

            case 'dots':
                $spacing = self::rngInt(20, 50);
                $radius = self::rngInt(2, 6);
                $ox = self::rngInt(0, $spacing);
                $oy = self::rngInt(0, $spacing);
                for ($x = $ox; $x < $w; $x += $spacing) {
                    for ($y = $oy; $y < $h; $y += $spacing) {
                        $r = $radius + self::rngInt(-1, 1);
                        $opacity = self::rngInt(15, 40) / 100;
                        $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . self::SVG_FILL . $fg . self::SVG_OPACITY . $opacity . '"/>';
                    }
                }
                break;

            case 'cross':
                for ($i = 0; $i < 6; $i++) {
                    $cx = self::rngInt(0, $w);
                    $cy = self::rngInt(0, $h);
                    $size = self::rngInt(intdiv($w, 10), intdiv($w, 3));
                    $thickness = self::rngInt(2, 8);
                    $opacity = self::rngInt(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $svg .= '<rect x="' . ($cx - $thickness) . '" y="' . ($cy - $size) . '" width="' . ($thickness * 2) . self::SVG_HEIGHT . ($size * 2) . self::SVG_FILL . $color . self::SVG_OPACITY . $opacity . '" rx="2"/>';
                    $svg .= '<rect x="' . ($cx - $size) . '" y="' . ($cy - $thickness) . '" width="' . ($size * 2) . self::SVG_HEIGHT . ($thickness * 2) . self::SVG_FILL . $color . self::SVG_OPACITY . $opacity . '" rx="2"/>';
                }
                break;

            default:
                break;
        }

        $svg .= '</svg>';
        return $svg;
    }
}
