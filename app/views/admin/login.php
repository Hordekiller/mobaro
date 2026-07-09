<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود ادمین | موبارو</title>
    <meta name="csrf" content="<?= $_SESSION['_csrf'] ?? '' ?>">
    <script src="/assets/libs/tailwind/tailwind-full.js"></script>
    <link rel="stylesheet" href="/assets/libs/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/fonts/google-fonts-woff2.css">
    <link rel="stylesheet" href="/assets/fonts/vazirmatn-font-face.css">
    <style>
        body { font-family: 'Vazirmatn', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-200 overflow-hidden">
                    <img src="/assets/images/logo.png" alt="موبارو" class="w-full h-full object-cover" onerror="this.innerHTML='<i class=\'fa-solid fa-shield-halved text-white text-3xl\'></i>'">
                </div>
                <h1 class="text-2xl font-bold mb-1">پنل مدیریت</h1>
                <p class="text-zinc-500 text-sm">موبارو</p>
            </div>
            <form method="POST" action="/admin/login" class="px-8 pb-8 space-y-5">
                <?= csrf() ?>
                <?php if ($err = flashError('admin')): ?>
                    <p class="text-red-500 text-xs text-center bg-red-50 py-3 rounded-2xl"><?= e($err) ?></p>
                <?php endif; ?>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">نام کاربری</label>
                    <input name="username" type="text" placeholder="admin"
                           class="w-full border rounded-3xl px-7 py-5 outline-none focus:border-rose-500 transition-colors"
                           value="<?= e(old('username')) ?>">
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">رمز عبور</label>
                    <input name="password" type="password" placeholder="••••••••"
                           class="w-full border rounded-3xl px-7 py-5 outline-none focus:border-rose-500 transition-colors">
                </div>
                <button type="submit" class="w-full py-5 bg-rose-600 hover:bg-rose-700 transition-all rounded-3xl text-white font-semibold shadow-inner">
                    ورود به پنل
                </button>
                <div class="text-center">
                    <a href="/login" class="text-xs text-zinc-400 hover:text-rose-500 transition-colors">بازگشت به سایت</a>
                </div>
            </form>
        </div>
        <p class="text-center text-xs text-zinc-600 mt-6">© ۱۴۰۴ موبارو. پنل مدیریت</p>
    </div>
</body>
</html>
<?php clearFlashErrors(); ?>
