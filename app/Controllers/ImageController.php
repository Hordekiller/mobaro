<?php

class ImageController
{
    private static array $palettes = [
        'beauty'  => ['bg' => '#e11d48', 'fg' => '#ffffff', 'accent' => '#fda4af'],
        'gold'    => ['bg' => '#D4AF37', 'fg' => '#1a1a1a', 'accent' => '#f5d76e'],
        'skin'    => ['bg' => '#FDF6F0', 'fg' => '#8b5c41', 'accent' => '#e8d5c4'],
        'nail'    => ['bg' => '#ec4899', 'fg' => '#ffffff', 'accent' => '#f9a8d4'],
        'makeup'  => ['bg' => '#9333ea', 'fg' => '#ffffff', 'accent' => '#c084fc'],
        'salon'   => ['bg' => '#374151', 'fg' => '#e11d48', 'accent' => '#6b7280'],
        'rose'    => ['bg' => '#fda4af', 'fg' => '#1a1a1a', 'accent' => '#e11d48'],
        'dark'    => ['bg' => '#18181b', 'fg' => '#e11d48', 'accent' => '#3f3f46'],
        'cream'   => ['bg' => '#fef3c7', 'fg' => '#92400e', 'accent' => '#fbbf24'],
        'teal'    => ['bg' => '#0d9488', 'fg' => '#ffffff', 'accent' => '#5eead4'],
    ];

    private static array $patterns = ['circle', 'diamond', 'wave', 'dots', 'cross'];

    public static function random(int $width, int $height): void
    {
        $seed = rand(1, 999999);
        self::serve($width, $height, $seed);
    }

    public static function seeded(int $width, int $height, int $seed): void
    {
        self::serve($width, $height, $seed);
    }

    private static function serve(int $width, int $height, int $seed): void
    {
        $cacheDir = __DIR__ . '/../../public/assets/images/cache';

        if (random_int(1, 100) === 1) {
            self::cleanupOldCache($cacheDir);
        }

        $cacheKey = "{$width}x{$height}_{$seed}.svg";
        $cacheFile = $cacheDir . '/' . $cacheKey;

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (file_exists($cacheFile)) {
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=86400');
            readfile($cacheFile);
            return;
        }

        mt_srand($seed);
        $paletteKeys = array_keys(self::$palettes);
        $palette = self::$palettes[$paletteKeys[array_rand($paletteKeys)]];
        $pattern = self::$patterns[array_rand(self::$patterns)];

        $svg = self::generateSvg($width, $height, $palette, $pattern, $seed);

        file_put_contents($cacheFile, $svg);

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo $svg;
        exit;
    }

    private static function cleanupOldCache(string $dir): void
    {
        $cutoff = time() - 86400 * 7;
        foreach (glob($dir . '/*.svg') as $f) {
            if (is_file($f) && filemtime($f) < $cutoff) {
                @unlink($f);
            }
        }
        $avatarDir = $dir . '/avatars';
        if (is_dir($avatarDir)) {
            foreach (glob($avatarDir . '/*.svg') as $f) {
                if (is_file($f) && filemtime($f) < $cutoff) {
                    @unlink($f);
                }
            }
        }
    }

    private static function generateSvg(int $w, int $h, array $palette, string $pattern, int $seed): string
    {
        $bg = $palette['bg'];
        $fg = $palette['fg'];
        $accent = $palette['accent'];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
        $svg .= '<rect width="' . $w . '" height="' . $h . '" fill="' . $bg . '"/>';

        switch ($pattern) {
            case 'circle':
                for ($i = 0; $i < 5; $i++) {
                    $cx = mt_rand(0, $w);
                    $cy = mt_rand(0, $h);
                    $r = mt_rand(intdiv($w, 8), intdiv($w, 2));
                    $opacity = mt_rand(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $color . '" opacity="' . $opacity . '"/>';
                }
                break;

            case 'diamond':
                for ($i = 0; $i < 4; $i++) {
                    $cx = mt_rand(intdiv($w, 4), intdiv($w * 3, 4));
                    $cy = mt_rand(intdiv($h, 4), intdiv($h * 3, 4));
                    $size = mt_rand(intdiv($w, 6), intdiv($w, 2));
                    $opacity = mt_rand(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $points = ($cx) . ',' . ($cy - $size) . ' ' .
                              ($cx + $size) . ',' . $cy . ' ' .
                              ($cx) . ',' . ($cy + $size) . ' ' .
                              ($cx - $size) . ',' . $cy;
                    $svg .= '<polygon points="' . $points . '" fill="' . $color . '" opacity="' . $opacity . '"/>';
                }
                break;

            case 'wave':
                $amp = mt_rand(intdiv($h, 10), intdiv($h, 4));
                $freq = mt_rand(2, 6);
                for ($i = 0; $i < 3; $i++) {
                    $offset = mt_rand(0, $h);
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $opacity = mt_rand(8, 20) / 100;
                    $d = 'M0 ' . $offset;
                    for ($x = 0; $x <= $w; $x += 10) {
                        $y = $offset + sin(($x / $w) * M_PI * $freq + $i) * $amp;
                        $d .= ' L' . $x . ' ' . $y;
                    }
                    $d .= ' L' . $w . ' ' . $h . ' L0 ' . $h . ' Z';
                    $svg .= '<path d="' . $d . '" fill="' . $color . '" opacity="' . $opacity . '"/>';
                }
                break;

            case 'dots':
                $spacing = mt_rand(20, 50);
                $radius = mt_rand(2, 6);
                $ox = mt_rand(0, $spacing);
                $oy = mt_rand(0, $spacing);
                for ($x = $ox; $x < $w; $x += $spacing) {
                    for ($y = $oy; $y < $h; $y += $spacing) {
                        $r = $radius + mt_rand(-1, 1);
                        $opacity = mt_rand(15, 40) / 100;
                        $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="' . $fg . '" opacity="' . $opacity . '"/>';
                    }
                }
                break;

            case 'cross':
                for ($i = 0; $i < 6; $i++) {
                    $cx = mt_rand(0, $w);
                    $cy = mt_rand(0, $h);
                    $size = mt_rand(intdiv($w, 10), intdiv($w, 3));
                    $thickness = mt_rand(2, 8);
                    $opacity = mt_rand(10, 30) / 100;
                    $color = $i % 2 === 0 ? $fg : $accent;
                    $svg .= '<rect x="' . ($cx - $thickness) . '" y="' . ($cy - $size) . '" width="' . ($thickness * 2) . '" height="' . ($size * 2) . '" fill="' . $color . '" opacity="' . $opacity . '" rx="2"/>';
                    $svg .= '<rect x="' . ($cx - $size) . '" y="' . ($cy - $thickness) . '" width="' . ($size * 2) . '" height="' . ($thickness * 2) . '" fill="' . $color . '" opacity="' . $opacity . '" rx="2"/>';
                }
                break;
        }

        $svg .= '</svg>';
        return $svg;
    }
}
