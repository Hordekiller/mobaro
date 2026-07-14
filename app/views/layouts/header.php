<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'موبارو | سالن زیبایی حرفه‌ای') ?></title>
    <meta name="description" content="سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای">
    <meta property="og:title" content="موبارو | سالن زیبایی حرفه‌ای">
    <meta property="og:description" content="سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای">
    <meta property="og:image" content="/favicon/og-image.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <?php $_csrf_token = $_SESSION['_csrf'] ?? ''; ?>
    <meta name="csrf" content="<?= e($_csrf_token) ?>">
    <script>function csrfParam(){var t=document.querySelector('meta[name="csrf"]');return'_csrf='+encodeURIComponent(t?t.getAttribute('content'):'')}</script>
    <script src="/assets/libs/tailwind/tailwind-full.js"></script>
    <link rel="stylesheet" href="/assets/libs/fontawesome/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/favicon/icon-512.png">
    <link rel="stylesheet" href="/assets/fonts/google-fonts-woff2.css">
    <link rel="stylesheet" href="/assets/fonts/vazirmatn-font-face.css">
    <link rel="stylesheet" href="/assets/css/frontend.css?v=2.2">
    <style>
        :root {
            --primary: <?= e($settings['color_primary'] ?? '#e11d48') ?>;
            --primary-dark: <?= e($settings['color_primary_dark'] ?? '#be185d') ?>;
            --gold: <?= e($settings['color_gold'] ?? '#D4AF37') ?>;
        }
        body { font-family: 'Vazirmatn', system-ui, sans-serif; }
        .logo-font { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-800 overflow-x-hidden pb-20 md:pb-0">
    <div id="progress-bar" class="scroll-progress w-0"></div>

    <nav class="bg-white border-b border-zinc-100 shadow-sm fixed w-full z-50">
        <div class="max-w-screen-2xl mx-auto">
            <div class="px-8 py-5 flex items-center justify-between">
                <a href="/" class="flex items-center gap-x-3">
                    <div class="w-11 h-11 bg-rose-600 rounded-2xl flex items-center justify-center shadow-inner overflow-hidden">
                        <img src="/assets/images/logo.png" alt="موبارو" class="w-full h-full object-cover" onerror="this.innerHTML='<i class=\'fa-solid fa-spa text-white text-3xl\'></i>'">
                    </div>
                    <span class="logo-font text-4xl font-bold tracking-tighter text-rose-600">
                        <?= e($settings['brand_name'] ?? 'موبارو') ?>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-x-8 text-sm font-medium">
                    <a href="/" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/') ?>">خانه</a>
                    <a href="/#services" class="nav-link text-zinc-700 hover:text-rose-600">خدمات</a>
                    <a href="/#models" class="nav-link text-zinc-700 hover:text-rose-600">مدل‌ها</a>
                    <a href="/academy" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/academy') ?>">آکادمی</a>
                    <a href="/shop" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/shop') ?>">فروشگاه</a>
                    <a href="/blog" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/blog') ?>">وبلاگ</a>
                    <a href="/contact" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/contact') ?>">تماس با ما</a>
                    <a href="/about" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/about') ?>">درباره ما</a>
                </div>

                <div class="flex items-center gap-x-4">
                    <div class="hidden md:flex items-center gap-x-4">
                        <button onclick="toggleWishlistSidebar()" class="relative flex items-center gap-x-2 px-4 py-2.5 bg-white hover:bg-zinc-100 border border-zinc-200 rounded-3xl text-sm font-medium transition-colors">
                            <i class="fa-regular fa-heart text-rose-500"></i>
                            <span id="wishlist-count" class="text-xs bg-rose-500 text-white w-5 h-5 flex items-center justify-center rounded-full">
                                <?php
                                if (Auth::check()) {
                                    $wcResult = Database::fetch("SELECT COUNT(*) as cnt FROM wishlist WHERE user_id = ?", [Auth::id()]);
                                    echo e($wcResult['cnt'] ?? 0);
                                } else {
                                    echo e(count($_SESSION['wishlist'] ?? []));
                                }
                                ?>
                            </span>
                        </button>

                        <button onclick="toggleCart()" class="flex items-center gap-x-2 px-5 py-2.5 bg-white hover:bg-zinc-100 border border-zinc-200 rounded-3xl text-sm font-medium transition-colors">
                            <i class="fa-solid fa-cart-shopping text-rose-500"></i>
                            <span id="cart-count" class="text-xs bg-rose-500 text-white w-5 h-5 flex items-center justify-center rounded-full">
                                <?= e(array_sum(array_column($_SESSION['cart'] ?? [], 'qty'))) ?>
                            </span>
                        </button>

                        <?php if (Auth::check()) : ?>
                            <a href="/dashboard" class="flex items-center gap-x-2 bg-rose-600 hover:bg-rose-700 transition-colors text-white px-7 py-3 rounded-3xl text-sm font-semibold shadow-md shadow-rose-200">
                                <i class="fa-solid fa-user"></i>
                                <span>پنل کاربری</span>
                            </a>
                        <?php else : ?>
                            <a href="/login" class="flex items-center gap-x-2 bg-rose-600 hover:bg-rose-700 transition-colors text-white px-7 py-3 rounded-3xl text-sm font-semibold shadow-md shadow-rose-200">
                                <i class="fa-solid fa-user"></i>
                                <span>ورود / ثبت‌نام</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-20">
