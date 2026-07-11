    </div>

    <footer id="about" class="bg-zinc-950 text-zinc-400">
        <div class="max-w-screen-2xl mx-auto px-8 pt-20">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-y-8 md:gap-y-12">
                <div>
                    <div class="flex items-center gap-x-3 text-white mb-6">
                        <div class="w-8 h-8 bg-rose-500 rounded-2xl flex items-center justify-center">
                            <i class="fa-solid fa-spa"></i>
                        </div>
                        <span class="logo-font text-4xl font-bold text-white">
                            <?= e($settings['brand_name'] ?? 'موبارو') ?>
                        </span>
                    </div>
                    <p class="text-xs leading-relaxed max-w-xs">
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
                        <a href="/about" class="block cursor-pointer hover:text-white transition-colors">درباره ما</a>
                        <a href="/academy" class="block cursor-pointer hover:text-white transition-colors">آکادمی</a>
                        <a href="/shop" class="block cursor-pointer hover:text-white transition-colors">فروشگاه</a>
                        <a href="/blog" class="block cursor-pointer hover:text-white transition-colors">وبلاگ</a>
                        <a href="/contact" class="block cursor-pointer hover:text-white transition-colors">تماس با ما</a>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-white mb-6 tracking-widest">آرایشگران ما</div>
                    <div class="space-y-6">
                        <?php if (!empty($artists)): ?>
                            <?php $i = 0; foreach ($artists as $artist): if ($i >= 2) break; $i++; ?>
                            <div class="flex gap-x-4">
                                <img src="<?= $artist['avatar'] ? '/assets/images/' . e($artist['avatar']) : '/avatar/' . urlencode($artist['name']) . '/72' ?>"
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
                            <?php $newsletterCaptcha = Captcha::isEnabled('newsletter'); ?>
                            <?php if ($newsletterCaptcha): ?>
                            <div class="flex items-center gap-2 mt-3" id="newsletter-captcha-row">
                                <input id="newsletter-captcha" type="text" inputmode="numeric" placeholder="کد امنیتی"
                                       class="flex-1 bg-zinc-800 border-none focus:ring-2 focus:ring-rose-400 rounded-3xl py-4 px-5 text-sm placeholder:text-zinc-500 text-center">
                                <span class="text-zinc-400 text-xs whitespace-nowrap" id="newsletter-captcha-q"><?= e($_SESSION['captcha_question'] ?? Captcha::store()) ?> = ?</span>
                                <button type="button" onclick="refreshNewsletterCaptcha()" class="text-zinc-500 hover:text-rose-400 text-xs"><i class="fa-solid fa-rotate"></i></button>
                            </div>
                            <?php endif; ?>
                            <button onclick="subscribeNewsletter()"
                                    class="mt-3 w-full bg-white text-zinc-900 py-5 text-xs font-bold rounded-3xl hover:bg-rose-50 transition-colors">عضویت در خبرنامه</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 mt-12 border-t border-white/10 flex flex-col md:flex-row justify-center items-center gap-4 md:gap-8">
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

            <div class="pt-10 mt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center text-xs md:text-[10px]">
                <div>© ۱۴۰۴ <?= e($settings['brand_name'] ?? 'موبارو') ?>. تمامی حقوق محفوظ است.</div>
                <div class="flex items-center gap-x-6 text-xs md:text-[10px] mt-6 md:mt-0">
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard" class="cursor-pointer hover:text-white">پنل کاربری</a>
                    <?php else: ?>
                        <a href="/login" class="cursor-pointer hover:text-white">ورود به پنل</a>
                    <?php endif; ?>
                    <div class="w-px h-3 bg-white/30"></div>
                    <a href="/privacy" class="cursor-pointer hover:text-white">حریم خصوصی</a>
                    <div class="w-px h-3 bg-white/30"></div>
                    <a href="/terms" class="cursor-pointer hover:text-white">شرایط استفاده</a>
                </div>
                <div class="text-emerald-300 mt-6 md:mt-0">ساخته شده با ❤️ برای زیبایی شما</div>
            </div>
        </div>
    </footer>

    <div id="toast-container" class="hidden fixed bottom-6 right-6 z-[999999]"></div>

    <!-- Mobile Bottom Navigation Bar -->
    <div id="bottom-nav" class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-lg border-t border-zinc-200 z-50 safe-bottom">
        <div class="flex items-center justify-around py-2">
            <a href="/" class="bottom-nav-item active flex flex-col items-center gap-0.5 px-3 py-1">
                <i class="fa-solid fa-house text-xl"></i>
                <span class="text-[10px] font-medium">خانه</span>
            </a>
            <a href="/shop" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                <i class="fa-solid fa-store text-xl"></i>
                <span class="text-[10px] font-medium">فروشگاه</span>
            </a>
            <a href="/blog" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                <i class="fa-solid fa-pen text-xl"></i>
                <span class="text-[10px] font-medium">وبلاگ</span>
            </a>
            <a href="/#booking" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                <i class="fa-solid fa-calendar-check text-xl"></i>
                <span class="text-[10px] font-medium">رزرو</span>
            </a>
            <a href="/academy" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                <i class="fa-solid fa-graduation-cap text-xl"></i>
                <span class="text-[10px] font-medium">آکادمی</span>
            </a>
            <?php if (Auth::check()): ?>
                <a href="/dashboard" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                    <i class="fa-solid fa-user text-xl"></i>
                    <span class="text-[10px] font-medium">پروفایل</span>
                </a>
            <?php else: ?>
                <a href="/login" class="bottom-nav-item flex flex-col items-center gap-0.5 px-3 py-1">
                    <i class="fa-solid fa-user text-xl"></i>
                    <span class="text-[10px] font-medium">ورود</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

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
