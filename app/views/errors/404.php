<?php

$title = 'صفحه یافت نشد | موبارو'; ?>
<div class="min-h-screen flex items-center justify-center px-4" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 50%, #fff5f5 100%);">
    <div class="text-center max-w-lg">
        <div class="relative mb-10">
            <div class="w-32 h-32 bg-rose-100 rounded-full flex items-center justify-center mx-auto">
                <i class="fa-solid fa-circle-question text-rose-600 text-5xl"></i>
            </div>
            <div class="absolute -top-2 -right-2 md:right-20 w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center animate-bounce">
                <i class="fa-solid fa-spa text-amber-500 text-xl"></i>
            </div>
        </div>
        <h1 class="text-9xl font-bold text-rose-600 mb-2" style="font-family: 'Playfair Display', serif;">404</h1>
        <div class="w-16 h-1 bg-gradient-to-r from-rose-500 to-amber-400 mx-auto rounded-full mb-6"></div>
        <p class="text-2xl text-zinc-700 font-bold mb-3">صفحه‌ای که به دنبال آن هستید پیدا نشد!</p>
        <p class="text-zinc-500 mb-8 leading-relaxed">متأسفیم، صفحه مورد نظر شما وجود ندارد، حذف شده یا آدرس آن اشتباه است.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/"
               class="inline-flex items-center gap-2 px-8 py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all font-medium shadow-lg">
                <i class="fa-solid fa-home"></i>
                بازگشت به خانه
            </a>
            <a href="/blog"
               class="inline-flex items-center gap-2 px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl transition-all font-medium shadow-lg">
                <i class="fa-solid fa-pen"></i>
                جدیدترین مقالات
            </a>
            <a href="/contact"
               class="inline-flex items-center gap-2 px-8 py-4 bg-white border-2 border-zinc-200 hover:border-rose-300 text-zinc-700 rounded-2xl transition-all font-medium">
                <i class="fa-solid fa-phone"></i>
                تماس با ما
            </a>
        </div>
    </div>
</div>
