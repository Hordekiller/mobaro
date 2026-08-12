<?php

declare(strict_types=1);

use App\Auth;

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <?php
    $settings = $settings ?? [];
    $seo = $seo ?? [];
    $brandName = $settings['brand_name'] ?? 'موبارو';
    $titlePrefix = $settings['title_prefix'] ?? '';
    $titleSuffix = $settings['title_suffix'] ?? '';
    $metaTitle = ($settings['meta_title'] ?? '') ?: $brandName;
    $rawTitle = ($seo['title'] ?? '') ?: (($title ?? '') ?: $metaTitle);
    $fullTitle = $titlePrefix . $rawTitle;
    if ($titleSuffix !== '' && !str_contains($rawTitle, $titleSuffix) && !str_contains($rawTitle, $brandName)) {
        $fullTitle .= $titleSuffix;
    }
    ?>
    <title><?= e($fullTitle) ?></title>
    <?php $_csrf_token = $_SESSION['_csrf'] ?? ''; ?>
    <meta name="csrf" content="<?= e($_csrf_token) ?>">
    <script>function csrfParam(){var t=document.querySelector('meta[name="csrf"]');return'_csrf='+encodeURIComponent(t?t.getAttribute('content'):'')}</script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/libs/fontawesome/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/favicon/icon-512.png">
    <link rel="stylesheet" href="/assets/fonts/google-fonts-woff2.css">
    <link rel="stylesheet" href="/assets/fonts/vazirmatn-font-face.css">
    <link rel="stylesheet" href="/assets/css/frontend.css?v=2.2">
    <link rel="stylesheet" href="/assets/css/admin.css?v=3">
    <style>
        :root {
            --primary: <?= e($settings['color_primary'] ?? '#e11d48') ?>;
            --primary-dark: <?= e($settings['color_primary_dark'] ?? '#be185d') ?>;
            --gold: <?= e($settings['color_gold'] ?? '#D4AF37') ?>;
        }
        body { font-family: 'Vazirmatn', system-ui, sans-serif; }
        .logo-font { font-family: 'Playfair Display', 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="admin-body bg-zinc-50 text-zinc-800">
