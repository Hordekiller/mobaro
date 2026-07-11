<?php $title = e($settings['about_title'] ?? 'درباره ما | موبارو'); ?>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-rose-50/30">
    <div class="relative bg-gradient-to-r from-rose-600 to-rose-800 py-20 overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full translate-x-1/2 translate-y-1/2"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <span class="inline-block px-4 py-1 rounded-full bg-white/20 text-white text-sm font-medium mb-4">درباره موبارو</span>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4"><?= e($settings['about_title'] ?? 'درباره ما') ?></h1>
            <p class="text-rose-100 text-lg max-w-2xl mx-auto">سالن زیبایی حرفه‌ای با ۱۲ سال تجربه درخشان درخشان در تهران</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-10 pb-20">
        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12 mb-10">
            <div class="grid md:grid-cols-2 gap-10 items-start">
                <div>
                    <?php $aboutImage = $settings['about_image'] ?? ''; ?>
                    <?php if (!empty($aboutImage) && file_exists(__DIR__ . '/../../../public/assets/images/' . $aboutImage)): ?>
                    <img src="/assets/images/<?= e($aboutImage) ?>" alt="درباره موبارو" class="w-full rounded-2xl object-cover shadow-lg">
                    <?php else: ?>
                    <div class="w-full aspect-video bg-gradient-to-br from-rose-100 to-amber-50 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-spa text-8xl text-rose-300"></i>
                    </div>
                    <?php endif; ?>
                    <div class="grid grid-cols-3 gap-4 mt-6">
                        <div class="bg-rose-50 rounded-xl p-4 text-center">
                            <span class="block text-2xl font-bold text-rose-600">۱۲+</span>
                            <span class="text-xs text-zinc-500">سال تجربه</span>
                        </div>
                        <div class="bg-rose-50 rounded-xl p-4 text-center">
                            <span class="block text-2xl font-bold text-rose-600">۱۵۰۰+</span>
                            <span class="text-xs text-zinc-500">مشتری راضی</span>
                        </div>
                        <div class="bg-rose-50 rounded-xl p-4 text-center">
                            <span class="block text-2xl font-bold text-rose-600">۲۰+</span>
                            <span class="text-xs text-zinc-500">خدمات تخصصی</span>
                        </div>
                    </div>
                </div>
                <div class="prose prose-zinc max-w-none">
                    <?php $aboutContent = $settings['about_content'] ?? ''; ?>
                    <?php if (!empty($aboutContent)): ?>
                        <?= $aboutContent ?>
                    <?php else: ?>
                    <h2 class="text-2xl font-bold text-zinc-800">سالن زیبایی موبارو</h2>
                    <p class="text-zinc-600 leading-relaxed">
                        سالن زیبایی موبارو با بیش از ۱۲ سال تجربه در زمینه ارائه خدمات آرایشی و زیبایی، یکی از معتبرترین سالن‌های زیبایی تهران می‌باشد. ما با بهره‌گیری از جدیدترین متدهای روز دنیا و بهترین مواد آرایشی، خدمات با کیفیتی را به شما عزیزان ارائه می‌دهیم.
                    </p>
                    <p class="text-zinc-600 leading-relaxed">
                        تیم حرفه‌ای ما متشکل از آرایشگران مجرب و متخصص در زمینه‌های مختلف از جمله کوتاهی و رنگ مو، کراتین تراپی، میکاپ، ناخن و مراقبت‌های پوستی، آماده خدمت‌رسانی به شما عزیزان می‌باشند.
                    </p>
                    <h3 class="text-xl font-bold text-zinc-800">چرا موبارو؟</h3>
                    <ul class="text-zinc-600 space-y-2">
                        <li>استفاده از برترین برندهای آرایشی و بهداشتی</li>
                        <li>کادر مجرب و حرفه‌ای با آموزش‌های مداوم</li>
                        <li>فضایی آرام و دلنشین با رعایت کامل بهداشت</li>
                        <li>قیمت‌های منصفانه و رقابتی</li>
                        <li>مشاوره رایگان قبل از خدمات</li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-rose-600 to-rose-800 rounded-3xl p-8 md:p-12 text-white text-center">
            <h2 class="text-3xl font-bold mb-4">همین امروز نوبت خود را رزرو کنید</h2>
            <p class="text-rose-100 mb-8 max-w-xl mx-auto">برای دریافت مشاوره رایگان و رزرو نوبت با ما تماس بگیرید یا به صورت آنلاین نوبت خود را ثبت کنید.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/booking" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-rose-700 rounded-2xl font-bold hover:shadow-xl transition-all">
                    <i class="fa-solid fa-calendar-check"></i>
                    رزرو آنلاین نوبت
                </a>
                <a href="/contact" class="inline-flex items-center gap-2 px-8 py-4 bg-rose-500 text-white rounded-2xl font-bold hover:bg-rose-400 transition-all">
                    <i class="fa-solid fa-phone"></i>
                    <?= e($settings['brand_phone'] ?? 'تماس با ما') ?>
                </a>
            </div>
        </div>
    </div>
</div>
