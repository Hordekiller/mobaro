<?php

declare(strict_types=1);

?>
<section class="relative overflow-hidden bg-gradient-to-br from-rose-600 via-pink-600 to-fuchsia-600 text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-screen-2xl mx-auto px-8 py-20 md:py-28">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-x-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm mb-6">
                <i class="fa-solid fa-shield-halved"></i>
                <span>سند رسمی</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold leading-tight tracking-tight">حریم خصوصی</h1>
            <p class="mt-4 text-white/70 text-sm">آخرین به‌روزرسانی: <?= e($settings['privacy_updated_at'] ?? 'تیر ۱۴۰۵') ?></p>
        </div>
    </div>
</section>

<div class="max-w-3xl mx-auto px-4 py-16">
    <div class="prose-content">

        <?php if (!empty($settings['privacy_content'])) : ?>
            <?= $settings['privacy_content'] ?>
        <?php else : ?>
        <div class="bg-rose-50 border border-rose-100 rounded-2xl p-6 mb-12">
            <p class="text-sm text-zinc-600 leading-relaxed">
                سالن زیبایی <?= e($settings['brand_name'] ?? 'موبارو') ?> («ما»، «<?= e($settings['brand_name'] ?? 'موبارو') ?>»، «وب‌سایت») متعهد به حفاظت از حریم خصوصی کاربران خود است.
                این سند توضیح می‌دهد که چه اطلاعاتی جمع‌آوری می‌شود، چگونه استفاده می‌شود و چه حقوقی دارید.
            </p>
        </div>
        <?php endif; ?>

        <section id="contact" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۹</span>
                تماس با ما
            </h2>
            <p class="text-zinc-600 leading-relaxed mb-6">
                اگر سؤال یا نگرانی درباره این سیاست حریم خصوصی دارید، از روش‌های زیر با ما در تماس باشید:
            </p>
            <div class="bg-zinc-50 border border-zinc-100 rounded-2xl p-6 space-y-3">
                <div class="flex items-center gap-3 text-zinc-600">
                    <i class="fa-solid fa-phone text-rose-500"></i>
                    <span><?= e($settings['brand_phone'] ?? '۰۳۱-۳۶۶۶۲۱۲۲') ?></span>
                </div>
                <div class="flex items-center gap-3 text-zinc-600">
                    <i class="fa-solid fa-envelope text-rose-500"></i>
                    <span><?= e($settings['brand_email'] ?? 'info@mobaro.ir') ?></span>
                </div>
                <div class="flex items-center gap-3 text-zinc-600">
                    <i class="fa-solid fa-location-dot text-rose-500"></i>
                    <span><?= e($settings['brand_address'] ?? 'تهران، خیابان ولیعصر، پلاک ۱۲۸') ?></span>
                </div>
            </div>
        </section>

    </div>
</div>
