<?php
$dir = __DIR__ . '/../public/assets/images/placeholders';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$categories = [
    'beauty' => [
        ['bg' => [225, 29, 72], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [190, 24, 93], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [136, 19, 55], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [244, 63, 94], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [225, 29, 72], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
    ],
    'hair' => [
        ['bg' => [212, 175, 55], 'text' => [40, 40, 40], 'label' => 'Mobaro'],
        ['bg' => [184, 148, 47], 'text' => [40, 40, 40], 'label' => 'Mobaro'],
        ['bg' => [245, 215, 110], 'text' => [40, 40, 40], 'label' => 'Mobaro'],
        ['bg' => [200, 165, 60], 'text' => [40, 40, 40], 'label' => 'Mobaro'],
        ['bg' => [230, 190, 70], 'text' => [40, 40, 40], 'label' => 'Mobaro'],
    ],
    'skin' => [
        ['bg' => [253, 246, 240], 'text' => [139, 92, 65], 'label' => 'Mobaro'],
        ['bg' => [240, 230, 220], 'text' => [139, 92, 65], 'label' => 'Mobaro'],
        ['bg' => [250, 240, 235], 'text' => [139, 92, 65], 'label' => 'Mobaro'],
        ['bg' => [245, 235, 228], 'text' => [139, 92, 65], 'label' => 'Mobaro'],
        ['bg' => [238, 228, 218], 'text' => [139, 92, 65], 'label' => 'Mobaro'],
    ],
    'nail' => [
        ['bg' => [236, 72, 153], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [244, 114, 182], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [251, 146, 200], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [192, 38, 110], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [219, 60, 130], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
    ],
    'makeup' => [
        ['bg' => [159, 90, 253], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [147, 51, 234], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [168, 85, 247], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [126, 34, 206], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
        ['bg' => [139, 62, 218], 'text' => [255, 255, 255], 'label' => 'Mobaro'],
    ],
    'salon' => [
        ['bg' => [55, 48, 62], 'text' => [225, 29, 72], 'label' => 'Mobaro'],
        ['bg' => [62, 55, 70], 'text' => [225, 29, 72], 'label' => 'Mobaro'],
        ['bg' => [48, 42, 55], 'text' => [225, 29, 72], 'label' => 'Mobaro'],
        ['bg' => [58, 50, 65], 'text' => [225, 29, 72], 'label' => 'Mobaro'],
        ['bg' => [52, 45, 58], 'text' => [225, 29, 72], 'label' => 'Mobaro'],
    ],
];

$count = 1;
foreach ($categories as $cat => $colors) {
    foreach ($colors as $i => $c) {
        $img = imagecreatetruecolor(400, 400);
        $bg = imagecolorallocate($img, $c['bg'][0], $c['bg'][1], $c['bg'][2]);
        imagefilledrectangle($img, 0, 0, 399, 399, $bg);

        $tc = imagecolorallocate($img, $c['text'][0], $c['text'][1], $c['text'][2]);
        $size = 28;
        $font = 0;
        $text = $c['label'];
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        imagestring($img, $font, (400 - $tw) / 2, (400 - $th) / 2, $text, $tc);

        $sub = imagecolorallocate($img, $c['text'][0], $c['text'][1], $c['text'][2]);
        imagestring($img, 1, (400 - imagefontwidth(1) * strlen(strtoupper($cat))) / 2, (400 - $th) / 2 + 40, strtoupper($cat), $sub);

        imagepng($img, $dir . "/p{$count}.png");
        imagedestroy($img);
        $count++;
    }
}

echo "Generated " . ($count - 1) . " placeholder images in $dir\n";
