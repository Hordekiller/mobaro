    </div>

    <footer id="about" class="bg-zinc-950 text-zinc-400">
        <div class="max-w-screen-2xl mx-auto px-8 pt-20">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-y-12">
                <div>
                    <div class="flex items-center gap-x-3 text-white mb-6">
                        <div class="w-8 h-8 bg-rose-500 rounded-2xl flex items-center justify-center">
                            <i class="fa-solid fa-spa"></i>
                        </div>
                        <span class="logo-font text-4xl font-bold text-white">
                            <?= e($settings['brand_name'] ?? 'موبارو') ?>
                        </span>
                    </div>
                    <p class="text-xs leading-relaxed max-w-[190px]">
                        سالن زیبایی و مرکز آرایشی حرفه‌ای با ۱۲ سال سابقه در تهران
                    </p>
                    <div class="flex gap-x-5 mt-10">
                        <a href="<?= e($settings['brand_instagram'] ?? '#') ?>" class="fa-brands fa-instagram text-2xl cursor-pointer hover:text-rose-400 transition-colors"></a>
                        <a href="<?= e($settings['brand_telegram'] ?? '#') ?>" class="fa-brands fa-telegram text-2xl cursor-pointer hover:text-rose-400 transition-colors"></a>
                        <a href="<?= e($settings['brand_linkedin'] ?? '#') ?>" class="fa-brands fa-linkedin text-2xl cursor-pointer hover:text-rose-400 transition-colors"></a>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-white mb-6 tracking-widest">دسترسی سریع</div>
                    <div class="space-y-5 text-sm">
                        <a href="/#services" class="block cursor-pointer hover:text-white transition-colors">خدمات</a>
                        <a href="/#models" class="block cursor-pointer hover:text-white transition-colors">مدل‌ها</a>
                        <a href="/#education" class="block cursor-pointer hover:text-white transition-colors">آموزش</a>
                        <a href="/shop" class="block cursor-pointer hover:text-white transition-colors">فروشگاه</a>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-white mb-6 tracking-widest">آرایشگران ما</div>
                    <div class="space-y-6">
                        <?php if (!empty($artists)): ?>
                            <?php $i = 0; foreach ($artists as $artist): if ($i >= 2) break; $i++; ?>
                            <div class="flex gap-x-4">
                                <img src="<?= $artist['avatar'] ? '/assets/images/' . e($artist['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($artist['name']) . '&background=e11d48&color=fff' ?>"
                                     class="w-9 h-9 object-cover rounded-2xl" alt="">
                                <div class="text-xs">
                                    <div class="font-medium text-white"><?= e($artist['name']) ?></div>
                                    <div class="text-zinc-500"><?= e($artist['specialty']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <div class="text-xs font-semibold text-white mb-6 tracking-widest">تماس با ما</div>
                    <div class="text-sm space-y-6">
                        <div class="flex items-center gap-x-3">
                            <i class="fa-solid fa-phone text-rose-400"></i>
                            <span><?= e($settings['brand_phone'] ?? '۰۲۱-۲۲۸۸۴۲۶۷') ?></span>
                        </div>
                        <div class="flex items-center gap-x-3">
                            <i class="fa-solid fa-location-dot text-rose-400"></i>
                            <span><?= e($settings['brand_address'] ?? 'تهران، خیابان ولیعصر، پلاک ۱۲۸') ?></span>
                        </div>
                        <div class="text-xs text-zinc-500 leading-loose">
                            <?= e($settings['brand_hours'] ?? 'شنبه تا پنجشنبه ۹ صبح - ۸ شب') ?>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <div class="bg-zinc-900 rounded-3xl p-7">
                        <div class="text-white text-sm font-medium mb-4">خبرنامه ما</div>
                        <p class="text-xs mb-6 leading-tight">جدیدترین مدل‌ها، تخفیف‌ها و آموزش‌ها را زودتر از همه دریافت کنید</p>
                        <div class="relative">
                            <input id="newsletter-input" type="text" placeholder="ایمیل یا شماره تماس"
                                   class="w-full bg-zinc-800 border-none focus:ring-2 focus:ring-rose-400 rounded-3xl py-6 px-7 text-sm placeholder:text-zinc-500">
                            <button onclick="subscribeNewsletter()"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white text-zinc-900 px-8 py-4 text-xs font-bold rounded-3xl">ارسال</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 mt-12 border-t border-white/10 flex flex-row justify-center items-center gap-8">
                <div class="trust-badge-placeholder flex flex-col items-center gap-1 text-zinc-500">
                    <div class="w-20 h-20 bg-zinc-800 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-certificate text-3xl text-zinc-600"></i>
                    </div>
                    <span class="text-[10px]">نماد اعتماد الکترونیک</span>
                </div>
                <div class="trust-badge-placeholder flex flex-col items-center gap-1 text-zinc-500">
                    <div class="w-20 h-20 bg-zinc-800 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-3xl text-zinc-600"></i>
                    </div>
                    <span class="text-[10px]">ساماندهی</span>
                </div>
            </div>

            <div class="pt-10 mt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center text-[10px]">
                <div>© ۱۴۰۴ <?= e($settings['brand_name'] ?? 'موبارو') ?>. تمامی حقوق محفوظ است.</div>
                <div class="flex items-center gap-x-6 text-[10px] mt-6 md:mt-0">
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard" class="cursor-pointer hover:text-white">پنل کاربری</a>
                    <?php else: ?>
                        <a href="/login" class="cursor-pointer hover:text-white">ورود به پنل</a>
                    <?php endif; ?>
                    <div class="w-px h-3 bg-white/30"></div>
                    <span class="cursor-pointer hover:text-white">حریم خصوصی</span>
                    <div class="w-px h-3 bg-white/30"></div>
                    <span class="cursor-pointer hover:text-white">شرایط استفاده</span>
                </div>
                <div class="text-emerald-300 mt-6 md:mt-0">ساخته شده با ❤️ برای زیبایی شما</div>
            </div>
        </div>
    </footer>

    <div id="toast-container" class="hidden fixed bottom-6 right-6 z-[999999]"></div>

    <script src="/assets/js/frontend.js?v=2.2"></script>
    <script>
        <?php if ($msg = flash('success')): ?>
        setTimeout(() => showToast('<?= e($msg) ?>', 'success'), 500);
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
        setTimeout(() => showToast('<?= e($msg) ?>', 'error'), 500);
        <?php endif; ?>
    </script>
</body>
</html>
