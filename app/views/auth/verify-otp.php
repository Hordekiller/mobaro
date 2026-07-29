<?php

declare(strict_types=1);

$title = 'تأیید شماره تلفن | ' . ($settings['brand_name'] ?? 'موبارو'); ?>
<div class="min-h-screen bg-gradient-to-br from-rose-50 to-white flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-rose-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-rose-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-zinc-800">تأیید شماره تلفن</h1>
            <p class="text-zinc-500 mt-2">کد تأیید ارسال شده به <strong class="text-zinc-700"><?= e($phone ?? '') ?></strong> را وارد کنید</p>
        </div>

        <?php if ($errors = $_SESSION['flash_errors'] ?? []) : ?>
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
                <?php foreach ($errors as $err) : ?>
                    <p class="text-red-600 text-sm"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['flash_errors']);
        endif; ?>

        <?php if ($flash = $_SESSION['flash_success'] ?? '') : ?>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6">
                <p class="text-emerald-600 text-sm"><?= e($flash) ?></p>
            </div>
            <?php unset($_SESSION['flash_success']);
        endif; ?>

        <form method="POST" action="/verify-otp" class="bg-white rounded-3xl shadow-xl p-8">
            <?= csrf() ?>
            <input type="hidden" name="phone" value="<?= e($phone ?? '') ?>">

            <div class="mb-6">
                <label for="verify-code" class="block text-sm font-semibold text-zinc-700 mb-2">کد تأیید</label>
                <input id="verify-code" type="text" name="code" maxlength="5" inputmode="numeric" autocomplete="one-time-code"
                    class="w-full text-center text-2xl tracking-[0.5em] font-mono px-4 py-4 border-2 border-zinc-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none transition-all"
                    placeholder="-----" required autofocus>
            </div>

            <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold transition-all">
                <i class="fa-solid fa-check ml-2"></i>تأیید کد
            </button>

            <div class="text-center mt-6">
                <a href="/login" class="text-sm text-zinc-500 hover:text-rose-600 transition-colors">
                    <i class="fa-solid fa-arrow-right ml-1"></i>بازگشت به صفحه ورود
                </a>
            </div>
        </form>
    </div>
</div>
