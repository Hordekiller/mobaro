<?php

declare(strict_types=1);

$brandName = $settings['brand_name'] ?? 'موبارو';
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
                سالن زیبایی <?= e($brandName) ?> («ما»، «<?= e($brandName) ?>»، «وب‌سایت») متعهد به حفاظت از حریم خصوصی کاربران خود است.
                این سند توضیح می‌دهد که چه اطلاعاتی جمع‌آوری می‌شود، چگونه استفاده می‌شود و چه حقوقی دارید.
            </p>
        </div>
        <?php endif; ?>

        <section id="what-collected" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۱</span>
                اطلاعاتی که جمع‌آوری می‌کنیم
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-id-badge text-rose-500 mt-1.5 text-sm"></i>
                    <span><strong>اطلاعات ثبت‌نامی</strong> که خودتان هنگام ایجاد حساب ارائه می‌دهید: نام، نام خانوادگی، شماره تلفن همراه و رمز عبور.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-receipt text-rose-500 mt-1.5 text-sm"></i>
                    <span><strong>اطلاعات استفاده از خدمات:</strong> سوابق رزرو نوبت، سفارش‌های خرید، دوره‌های آموزشی، تراکنش‌های کیف پول و پیام‌های ارسالی.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-display text-rose-500 mt-1.5 text-sm"></i>
                    <span><strong>اطلاعات فنی خودکار:</strong> آدرس IP، نوع مرورگر و سیستم‌عامل، صفحات بازدیدشده و زمان دسترسی.</span>
                </li>
            </ul>
        </section>

        <section id="how-used" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۲</span>
                نحوه استفاده از اطلاعات
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-check-double text-emerald-500 mt-1.5 text-sm"></i>
                    <span>ارائه و پشتیبانی خدمات درخواستی شما (رزرو، خرید، دوره‌ها).</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-money-bill-wave text-emerald-500 mt-1.5 text-sm"></i>
                    <span>پردازش و پیگیری تراکنش‌های مالی و صدور فاکتور.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-bell text-emerald-500 mt-1.5 text-sm"></i>
                    <span>اطلاع‌رسانی درباره وضعیت رزرو، سفارش، تغییرات حساب و خدمات.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-shield-halved text-emerald-500 mt-1.5 text-sm"></i>
                    <span>بهبود کیفیت خدمات و افزایش امنیت وب‌سایت.</span>
                </li>
            </ul>
        </section>

        <section id="third-parties" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۳</span>
                اشتراک‌گذاری با اشخاص ثالث
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-ban text-slate-500 mt-1.5 text-sm"></i>
                    <span>اطلاعات شما بدون رضایت شما به فروش یا اجاره داده نمی‌شود.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-credit-card text-slate-500 mt-1.5 text-sm"></i>
                    <span>درگاه‌های پرداخت (مانند زرین‌پال) تنها اطلاعات لازم برای انجام تراکنش را دریافت می‌کنند.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-gavel text-slate-500 mt-1.5 text-sm"></i>
                    <span>در صورت الزام قانونی، اطلاعات طبق حکم مراجع ذیصلاح ارائه می‌شود.</span>
                </li>
            </ul>
        </section>

        <section id="security" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۴</span>
                امنیت و ذخیره‌سازی اطلاعات
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-lock text-blue-500 mt-1.5 text-sm"></i>
                    <span>تبادل اطلاعات از طریق رمزنگاری TLS انجام می‌شود و رمز عبور به صورت رمزنگاری‌شده ذخیره می‌گردد.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-user-shield text-blue-500 mt-1.5 text-sm"></i>
                    <span>دسترسی به اطلاعات فقط برای کارکنان مجاز و در حد نیاز شغلی فراهم است.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-clock-rotate-left text-blue-500 mt-1.5 text-sm"></i>
                    <span>اطلاعات تا زمانی که حساب کاربری شما فعال است نگهداری می‌شود و پس از حذف حساب، مطابق قوانین مربوطه حذف یا ناشناس‌سازی می‌شود.</span>
                </li>
            </ul>
        </section>

        <section id="cookies" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۵</span>
                کوکی‌ها
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-cookie-bite text-amber-500 mt-1.5 text-sm"></i>
                    <span>برای حفظ وضعیت ورود، امنیت فرم‌ها و بهبود تجربه کاربری از کوکی استفاده می‌کنیم.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-gear text-amber-500 mt-1.5 text-sm"></i>
                    <span>می‌توانید کوکی‌ها را از تنظیمات مرورگر خود غیرفعال کنید؛ در این صورت برخی امکانات ممکن است به درستی کار نکنند.</span>
                </li>
            </ul>
        </section>

        <section id="user-rights" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۶</span>
                حقوق کاربر
            </h2>
            <ul class="space-y-3 text-zinc-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-eye text-violet-500 mt-1.5 text-sm"></i>
                    <span>حق دسترسی به اطلاعات ثبت‌شده خود و اصلاح اطلاعات نادرست.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-user-minus text-violet-500 mt-1.5 text-sm"></i>
                    <span>حق درخواست حذف حساب کاربری از طریق پنل کاربری.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-bell-slash text-violet-500 mt-1.5 text-sm"></i>
                    <span>حق لغو رضایت نسبت به دریافت پیام‌های اطلاع‌رسانی در هر زمان.</span>
                </li>
            </ul>
        </section>

        <section id="privacy-changes" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۷</span>
                تغییرات در این سیاست
            </h2>
            <p class="text-zinc-600 leading-relaxed">
                این سیاست ممکن است به‌روزرسانی شود؛ نسخه جدید همراه با تاریخ اعمال در همین صفحه منتشر می‌شود.
                ادامه استفاده از خدمات پس از اعمال تغییرات به معنای پذیرش نسخه جدید است.
            </p>
        </section>

        <section id="contact" class="scroll-mt-24 mt-16">
            <h2 class="text-2xl font-bold text-zinc-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-sm font-bold">۸</span>
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
