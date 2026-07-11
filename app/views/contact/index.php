<?php $title = e($pageTitle ?? 'تماس با ما') . ' | موبارو'; ?>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-rose-50/30">
    <div class="relative bg-gradient-to-r from-rose-600 to-rose-800 py-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4"><?= e($pageTitle ?? 'تماس با ما') ?></h1>
            <p class="text-rose-100 text-lg">خوشحال می‌شویم نظرات، پیشنهادات و سوالات شما را بشنویم</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-10 pb-20">
        <div class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-phone text-rose-600 text-xl"></i>
                </div>
                <h3 class="font-bold mb-2">تلفن تماس</h3>
                <p class="text-zinc-500 text-sm"><?= e($settings['brand_phone'] ?? '۰۲۱-۲۲۸۸۴۲۶۷') ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-envelope text-rose-600 text-xl"></i>
                </div>
                <h3 class="font-bold mb-2">ایمیل</h3>
                <p class="text-zinc-500 text-sm"><?= e($settings['brand_email'] ?? 'info@mobaro.ir') ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-location-dot text-rose-600 text-xl"></i>
                </div>
                <h3 class="font-bold mb-2">آدرس</h3>
                <p class="text-zinc-500 text-sm"><?= e($settings['brand_address'] ?? 'تهران، خیابان ولیعصر، پلاک ۱۲۸') ?></p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h2 class="text-2xl font-bold mb-2">ارسال پیام</h2>
                <p class="text-zinc-500 text-sm mb-6">پیام خود را برای ما ارسال کنید</p>

                <?php if ($msg = flash('success')) : ?>
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm"><?= e($msg) ?></div>
                <?php endif; ?>

                <form action="/contact/send" method="POST" class="space-y-4">
                    <?= csrf() ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="name" placeholder="نام و نام خانوادگی" value="<?= e(old('name')) ?>"
                                   class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                            <?php if ($err = flashError('name')) :
                                ?><p class="text-red-500 text-xs mt-1"><?= e($err) ?></p><?php
                            endif; ?>
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="ایمیل" value="<?= e(old('email')) ?>"
                                   class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                            <?php if ($err = flashError('email')) :
                                ?><p class="text-red-500 text-xs mt-1"><?= e($err) ?></p><?php
                            endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="phone" placeholder="تلفن" value="<?= e(old('phone')) ?>"
                                   class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                        </div>
                        <div>
                            <input type="text" name="subject" placeholder="موضوع" value="<?= e(old('subject')) ?>"
                                   class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <textarea name="message" rows="5" placeholder="پیام شما..." 
                                  class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all"><?= e(old('message')) ?></textarea>
                        <?php if ($err = flashError('message')) :
                            ?><p class="text-red-500 text-xs mt-1"><?= e($err) ?></p><?php
                        endif; ?>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-rose-600 text-white rounded-xl font-bold hover:shadow-lg transition-all">
                        <i class="fa-solid fa-paper-plane ml-2"></i>ارسال پیام
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h2 class="text-2xl font-bold mb-2">اطلاعات تماس</h2>
                <p class="text-zinc-500 text-sm mb-6">راه‌های ارتباطی با ما</p>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-phone text-rose-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">تلفن</h4>
                            <p class="text-zinc-500 text-sm"><?= e($settings['brand_phone'] ?? '۰۲۱-۲۲۸۸۴۲۶۷') ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-envelope text-rose-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">ایمیل</h4>
                            <p class="text-zinc-500 text-sm"><?= e($settings['brand_email'] ?? 'info@mobaro.ir') ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-location-dot text-rose-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">آدرس</h4>
                            <p class="text-zinc-500 text-sm"><?= e($settings['brand_address'] ?? 'تهران، خیابان ولیعصر، پلاک ۱۲۸') ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-clock text-rose-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">ساعت کاری</h4>
                            <p class="text-zinc-500 text-sm"><?= e($settings['brand_hours'] ?? 'شنبه تا پنجشنبه ۹ صبح - ۸ شب') ?></p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-zinc-100">
                        <h4 class="font-semibold mb-3">ما را دنبال کنید</h4>
                        <div class="flex gap-3">
                            <a href="<?= e($settings['brand_instagram'] ?? '#') ?>" class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="<?= e($settings['brand_telegram'] ?? '#') ?>" class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                <i class="fa-brands fa-telegram"></i>
                            </a>
                            <a href="<?= e($settings['brand_linkedin'] ?? '#') ?>" class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                <i class="fa-brands fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($settings['contact_map_location'])) : ?>
        <div class="mt-8 bg-white rounded-2xl p-4 shadow-lg overflow-hidden">
            <h3 class="text-lg font-bold mb-4 px-2">موقعیت ما روی نقشه</h3>
            <div class="rounded-xl overflow-hidden aspect-video">
                <?= $settings['contact_map_location'] ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
