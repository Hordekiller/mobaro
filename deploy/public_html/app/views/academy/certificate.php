<?php

$title = 'گواهی پایان دوره | ' . e($course['title']); ?>
<style>
    @media print {
        body * { visibility: hidden; }
        .certificate-wrapper, .certificate-wrapper * { visibility: visible; }
        .certificate-wrapper { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>

<div class="no-print max-w-screen-xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <a href="/dashboard/courses" class="inline-flex items-center gap-2 text-rose-600 font-semibold hover:underline">
            <i class="fa-solid fa-arrow-right"></i> بازگشت به دوره‌ها
        </a>
        <button onclick="window.print()" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-semibold hover:bg-rose-700 transition-all">
            <i class="fa-solid fa-print ml-2"></i>پرینت گواهی
        </button>
    </div>
</div>

<div class="certificate-wrapper flex items-center justify-center min-h-screen bg-zinc-100 p-8">
    <div class="bg-white w-full max-w-[900px] aspect-[1.414/1] rounded-2xl shadow-2xl relative overflow-hidden" style="font-family: 'Vazirmatn', sans-serif;">

        <!-- Decorative borders -->
        <div class="absolute inset-4 border-2 border-amber-300 rounded-xl"></div>
        <div class="absolute inset-6 border border-amber-200 rounded-lg"></div>

        <!-- Corner decorations -->
        <div class="absolute top-6 right-6 w-16 h-16">
            <div class="w-full h-full border-t-4 border-r-4 border-amber-400 rounded-tr-xl"></div>
        </div>
        <div class="absolute top-6 left-6 w-16 h-16">
            <div class="w-full h-full border-t-4 border-l-4 border-amber-400 rounded-tl-xl"></div>
        </div>
        <div class="absolute bottom-6 right-6 w-16 h-16">
            <div class="w-full h-full border-b-4 border-r-4 border-amber-400 rounded-br-xl"></div>
        </div>
        <div class="absolute bottom-6 left-6 w-16 h-16">
            <div class="w-full h-full border-b-4 border-l-4 border-amber-400 rounded-bl-xl"></div>
        </div>

        <!-- Content -->
        <div class="relative h-full flex flex-col items-center justify-center px-12 py-8 text-center">

            <!-- Logo -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-spa text-white text-lg"></i>
                </div>
                <span class="text-2xl font-bold text-rose-600" style="font-family: 'Playfair Display', serif;">MOBARO</span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-zinc-800 mb-1">گواهی پایان دوره</h1>
            <div class="w-24 h-1 bg-gradient-to-l from-amber-400 to-rose-500 rounded-full mx-auto mb-6"></div>

            <!-- Body -->
            <p class="text-zinc-500 text-sm mb-3">این گواهی به شرح زیر صادر می‌گردد</p>

            <div class="mb-4">
                <span class="text-zinc-500 text-sm">نام هنرجو:</span>
                <span class="text-xl font-bold text-zinc-800 mr-2"><?= e($user['name'] . ' ' . $user['family']) ?></span>
            </div>

            <div class="mb-2">
                <span class="text-zinc-500 text-sm">دوره آموزشی:</span>
                <span class="text-lg font-bold text-rose-600 mr-2"><?= e($course['title']) ?></span>
            </div>

            <div class="mb-2 text-sm text-zinc-500">
                مدرس: <span class="font-semibold text-zinc-700"><?= e($course['teacher']) ?></span>
                &nbsp;|&nbsp;
                مدت دوره: <span class="font-semibold text-zinc-700"><?= e($course['duration']) ?></span>
            </div>

            <div class="mt-4 mb-6 text-sm text-zinc-400">
                تاریخ شروع: <?= $certificateDate ?> &nbsp;&bull;&nbsp; تاریخ اتمام: <?= jdate('Y/m/d') ?>
            </div>

            <!-- Seal & Signature -->
            <div class="flex items-end justify-between w-full max-w-md mt-auto">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-2 rounded-full border-2 border-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-stamp text-amber-500 text-2xl"></i>
                    </div>
                    <span class="text-xs text-zinc-400">مهر رسمی آکادمی</span>
                </div>
                <div class="text-center">
                    <span class="text-xs text-zinc-400 block mb-1">شماره گواهی: MB-CERT-<?= $course['id'] ?>-<?= $userId ?? $user['id'] ?></span>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-2 rounded-full border-2 border-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-signature text-amber-500 text-2xl"></i>
                    </div>
                    <span class="text-xs text-zinc-400">امضای مدیریت</span>
                </div>
            </div>
        </div>
    </div>
</div>
