<?php

$title = 'خطا | موبارو';
$errorMessage = $errorMessage ?? 'خطایی غیرمنتظره رخ داده است. لطفاً دوباره تلاش کنید.';
?>
<div class="min-h-screen flex items-center justify-center px-4" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-8">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-4xl"></i>
        </div>
        <h1 class="text-8xl font-bold text-rose-600 mb-4">۵۰۰</h1>
        <p class="text-xl text-zinc-700 mb-2">خطای داخلی سرور</p>
        <p class="text-zinc-500 mb-8"><?= e($errorMessage) ?></p>
        <a href="/"
           class="inline-flex items-center gap-2 px-8 py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all font-medium">
            <i class="fa-solid fa-arrow-right"></i>
            بازگشت به خانه
        </a>
    </div>
</div>
