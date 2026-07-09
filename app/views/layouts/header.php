<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'موبارو | سالن زیبایی حرفه‌ای') ?></title>
    <meta name="description" content="سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای">
    <meta name="csrf" content="<?= e($_SESSION['_csrf'] ?? '') ?>">
    <script>function csrfParam(){var t=document.querySelector('meta[name="csrf"]');return'_csrf='+encodeURIComponent(t?t.getAttribute('content'):'')}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/frontend.css?v=1.0">
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
<body class="bg-zinc-50 text-zinc-800 overflow-x-hidden">
    <div id="progress-bar" class="scroll-progress w-0"></div>

    <nav class="bg-white border-b border-zinc-100 shadow-sm fixed w-full z-50">
        <div class="max-w-screen-2xl mx-auto">
            <div class="px-8 py-5 flex items-center justify-between">
                <a href="/" class="flex items-center gap-x-3">
                    <div class="w-11 h-11 bg-rose-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-spa text-white text-3xl"></i>
                    </div>
                    <span class="logo-font text-4xl font-bold tracking-tighter text-rose-600">
                        <?= e($settings['brand_name'] ?? 'موبارو') ?>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-x-8 text-sm font-medium">
                    <a href="/" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/') ?>">خانه</a>
                    <a href="/#services" class="nav-link text-zinc-700 hover:text-rose-600">خدمات</a>
                    <a href="/#models" class="nav-link text-zinc-700 hover:text-rose-600">مدل‌ها</a>
                    <a href="/#education" class="nav-link text-zinc-700 hover:text-rose-600">آموزش</a>
                    <a href="/shop" class="nav-link text-zinc-700 hover:text-rose-600 <?= isActive('/shop') ?>">فروشگاه</a>
                    <a href="/#about" class="nav-link text-zinc-700 hover:text-rose-600">درباره ما</a>
                </div>

                <div class="flex items-center gap-x-4">
                    <a href="/cart" class="flex items-center gap-x-2 px-5 py-2.5 bg-white hover:bg-zinc-100 border border-zinc-200 rounded-3xl text-sm font-medium transition-colors">
                        <i class="fa-solid fa-cart-shopping text-rose-500"></i>
                        <span id="cart-count" class="text-xs bg-rose-500 text-white w-5 h-5 flex items-center justify-center rounded-full">
                            <?= array_sum(array_column($_SESSION['cart'] ?? [], 'qty')) ?>
                        </span>
                    </a>

                    <?php if (Auth::check()): ?>
                        <a href="/dashboard" class="flex items-center gap-x-2 bg-rose-600 hover:bg-rose-700 transition-colors text-white px-7 py-3 rounded-3xl text-sm font-semibold shadow-md shadow-rose-200">
                            <i class="fa-solid fa-user"></i>
                            <span>پنل کاربری</span>
                        </a>
                    <?php else: ?>
                        <a href="/login" class="flex items-center gap-x-2 bg-rose-600 hover:bg-rose-700 transition-colors text-white px-7 py-3 rounded-3xl text-sm font-semibold shadow-md shadow-rose-200">
                            <i class="fa-solid fa-user"></i>
                            <span>ورود / ثبت‌نام</span>
                        </a>
                    <?php endif; ?>

                    <button onclick="toggleMobileMenu()" class="md:hidden w-11 h-11 flex items-center justify-center text-2xl text-zinc-700">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t py-6 px-6">
            <div class="flex flex-col gap-y-6 text-lg">
                <a href="/" class="font-medium">خانه</a>
                <a href="/#services" class="font-medium">خدمات</a>
                <a href="/#models" class="font-medium">مدل‌های مو</a>
                <a href="/#education" class="font-medium">آموزش‌های زیبایی</a>
                <a href="/shop" class="font-medium">فروشگاه</a>
                <a href="/#about" class="font-medium">درباره ما</a>
                <div class="pt-6 border-t flex flex-col gap-y-3">
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard" class="w-full py-4 bg-rose-600 text-white rounded-3xl font-semibold text-center">پنل کاربری</a>
                        <a href="/logout" class="w-full py-4 border border-zinc-300 rounded-3xl font-semibold text-center">خروج</a>
                    <?php else: ?>
                        <a href="/login" class="w-full py-4 bg-rose-600 text-white rounded-3xl font-semibold text-center">ورود به حساب کاربری</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-20">
