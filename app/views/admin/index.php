<div class="min-h-screen bg-rose-50 flex" dir="rtl">
    <aside class="w-72 bg-white shadow-[0_0_40px_rgba(225,29,72,0.08)] min-h-screen flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-rose-100">
            <h1 class="text-xl font-extrabold text-rose-600"><i class="fa-solid fa-crown ml-2"></i>مدیریت موبارو</h1>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php
            $sections = [
                'dashboard' => ['fa-gauge-high', 'داشبورد', '/admin'],
                'services' => ['fa-scissors', 'خدمات'],
                'appointments' => ['fa-calendar-check', 'نوبت‌ها'],
                'artists' => ['fa-user-tie', 'آرایشگران'],
                'products' => ['fa-box', 'محصولات'],
                'product-categories' => ['fa-layer-group', 'دسته‌بندی محصولات'],
                'product-brands' => ['fa-tag', 'برندها'],
                'orders' => ['fa-truck', 'سفارش‌ها'],
                'users' => ['fa-users', 'کاربران'],
                'courses' => ['fa-graduation-cap', 'دوره‌ها'],
                'enrollments' => ['fa-user-graduate', 'ثبت‌نام دوره‌ها'],
                'testimonials' => ['fa-comment', 'نظرات'],
                'transactions' => ['fa-coins', 'تراکنش‌ها'],
                'blog' => ['fa-pen', 'وبلاگ'],
                'reviews' => ['fa-star', 'نظرات محصولات'],
                'blog-comments' => ['fa-comments', 'نظرات وبلاگ'],
                'coupons' => ['fa-tag', 'تخفیف‌ها'],
                'contact-messages' => ['fa-message', 'پیام‌ها'],
                'hair-models' => ['fa-image', 'مدل مو'],
                'tutorials' => ['fa-video', 'آموزش‌ها'],
                'newsletter' => ['fa-envelope', 'خبرنامه'],
                'captcha' => ['fa-shield-halved', 'کپچا'],
                'gallery' => ['fa-photo-film', 'گالری رسانه'],
                'settings' => ['fa-gear', 'تنظیمات'],
            ];
            foreach ($sections as $key => $sec) :
                $active = $section === $key ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-zinc-600 hover:bg-rose-50 hover:text-rose-600';
                ?>
            <a href="<?= $sec[2] ?? ('/admin/' . $key) ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all <?= $active ?>">
                <i class="fa-solid <?= $sec[0] ?> w-5 text-center"></i>
                <span><?= $sec[1] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-4 border-t border-rose-100">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-zinc-600 hover:bg-rose-50 hover:text-rose-600 transition-all">
                <i class="fa-solid fa-arrow-right w-5 text-center"></i>
                <span>بازگشت به سایت</span>
            </a>
            <a href="/logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-50 transition-all">
                <i class="fa-solid fa-sign-out w-5 text-center"></i>
                <span>خروج</span>
            </a>
        </div>
    </aside>

    <main class="flex-1 p-6 overflow-y-auto">
        <?php if ($section === 'dashboard') : ?>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold">داشبورد مدیریت</h2>
                <p class="text-zinc-400 text-sm">خلاصه وضعیت</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-rose-400 to-rose-600"><i class="fa-solid fa-users"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['users'] ?></h3><p class="text-zinc-400 text-sm">کاربران</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-amber-400 to-amber-600"><i class="fa-solid fa-calendar-check"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['appointments'] ?></h3><p class="text-zinc-400 text-sm">نوبت‌ها</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-purple-400 to-purple-600"><i class="fa-solid fa-box"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['orders'] ?></h3><p class="text-zinc-400 text-sm">سفارش‌ها</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-teal-400 to-teal-600"><i class="fa-solid fa-coins"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= priceFormat($stats['revenue']) ?></h3><p class="text-zinc-400 text-sm">درآمد کل</p></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <h3 class="font-bold text-lg mb-4">آخرین نوبت‌ها</h3>
                    <?php if (!empty($recentAppointments)) : ?>
                        <?php foreach ($recentAppointments as $a) : ?>
                        <div class="flex items-center gap-3 py-2.5 border-b border-rose-100 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-user"></i></div>
                            <div class="flex-1"><span class="font-semibold text-sm"><?= e($a['user_name']) ?></span><span class="text-zinc-400 text-xs mr-2"><?= e($a['service_title']) ?></span></div>
                            <span class="text-xs text-zinc-400"><?= jdate('Y/m/d', strtotime($a['appointment_date'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else :
                        ?><p class="text-zinc-400 text-sm text-center py-6">نوبتی ثبت نشده</p>
                    <?php endif; ?>
                </div>
                <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <h3 class="font-bold text-lg mb-4">آخرین سفارش‌ها</h3>
                    <?php if (!empty($recentOrders)) : ?>
                        <?php foreach ($recentOrders as $o) : ?>
                        <div class="flex items-center gap-3 py-2.5 border-b border-rose-100 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-bag-shopping"></i></div>
                            <div class="flex-1"><span class="font-semibold text-sm"><?= e($o['user_name']) ?></span><span class="text-zinc-400 text-xs mr-2"><?= priceFormat($o['total']) ?></span></div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $o['status'] === 'delivered' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' ?>"><?= e($o['status']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else :
                        ?><p class="text-zinc-400 text-sm text-center py-6">سفارشی ثبت نشده</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($section === 'settings') : ?>
            <?php $table = 'settings'; ?>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold">تنظیمات سایت</h2>
            </div>
            <?php
            $settingGroups = [
                'اطلاعات برند' => [
                    'brand_name' => 'نام برند',
                    'brand_phone' => 'تلفن برند',
                    'brand_address' => 'آدرس',
                    'brand_hours' => 'ساعت کاری',
                    'brand_email' => 'ایمیل',
                    'brand_instagram' => 'لینک اینستاگرام',
                    'brand_telegram' => 'لینک تلگرام',
                    'brand_linkedin' => 'لینک لینکدین',
                ],
                'رنگ‌ها' => [
                    'color_primary' => 'رنگ اصلی',
                    'color_primary_dark' => 'رنگ اصلی (تیره)',
                    'color_gold' => 'رنگ طلایی',
                ],
                'هدر صفحه اصلی' => [
                    'hero_title' => 'عنوان هدر',
                    'hero_description' => 'توضیحات هدر',
                ],
                'رزرو نوبت' => [
                    'booking_phone' => 'تلفن هماهنگی رزرو',
                ],
                'وبلاگ' => [
                    'blog_posts_per_page' => 'تعداد پست در صفحه وبلاگ',
                    'blog_default_author' => 'نویسنده پیش‌فرض وبلاگ',
                ],
                'تخفیف‌ها' => [
                    'discount_min_order_global' => 'حداقل مبلغ خرید برای اعمال تخفیف',
                    'discount_default_validity_days' => 'مدت اعتبار پیش‌فرض تخفیف (روز)',
                ],
                'صفحه تماس' => [
                    'contact_email' => 'ایمیل دریافت پیام‌های تماس',
                    'contact_map_location' => 'موقعیت مکانی (Google Maps Embed)',
                    'contact_header_text' => 'متن هدر صفحه تماس',
                ],
                'صفحه درباره ما' => [
                    'about_title' => 'عنوان صفحه درباره ما',
                    'about_content' => 'محتوای صفحه درباره ما',
                    'about_image' => 'تصویر صفحه درباره ما',
                ],
            ];
            $textareaKeys = ['hero_description', 'about_content', 'contact_map_location'];
            ?>
            <form action="/admin/settings/update" method="POST" class="space-y-6">
                <?= csrf() ?>
                <?php foreach ($settingGroups as $groupTitle => $fields) : ?>
                <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;"><?= e($groupTitle) ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($fields as $key => $label) :
                            $value = $settings[$key] ?? '';
                            ?>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5"><?= e($label) ?></label>
                            <?php if (in_array($key, $textareaKeys)) : ?>
                                <textarea name="setting_<?= e($key) ?>" rows="3" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all"><?= e($value) ?></textarea>
                            <?php elseif ($key === 'about_image') : ?>
                                <div class="flex gap-2 items-center">
                                    <input type="text" name="setting_<?= e($key) ?>" value="<?= e($value) ?>" class="flex-1 w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="مثال: about.jpg">
                                    <?php if (!empty($value)) : ?>
                                    <img src="/assets/images/<?= e($value) ?>" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" onerror="this.style.display='none'">
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <input type="text" name="setting_<?= e($key) ?>" value="<?= e($value) ?>" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <button type="submit" class="px-8 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ذخیره تنظیمات</button>
            </form>

            <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)] mt-6">

            <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)] mt-6">
                <h3 class="font-bold text-lg mb-1">تغییر رمز عبور ادمین</h3>
                <p class="text-zinc-400 text-sm mb-4">رمز عبور حساب مدیریت خود را تغییر دهید</p>
                <form action="/admin/password/change" method="POST" class="max-w-sm space-y-4">
                    <?= csrf() ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">رمز عبور فعلی</label>
                        <input type="password" name="current_password" required class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">رمز عبور جدید</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">تکرار رمز عبور جدید</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    </div>
                    <button type="submit" class="px-8 py-3 bg-zinc-800 text-white rounded-xl font-semibold text-sm hover:bg-zinc-900 transition-all">تغییر رمز عبور</button>
                </form>
            </div>

        <?php elseif ($section === 'captcha') : ?>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold">تنظیمات کپچا (کد امنیتی)</h2>
                <p class="text-zinc-400 text-sm">مدیریت کد امنیتی در بخش‌های مختلف سایت</p>
            </div>

            <form action="/admin/captcha/save" method="POST" class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                <?= csrf() ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <h3 class="font-bold text-lg mb-3">فعال‌سازی کپچا</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php
                            $captchaToggles = [
                                'captcha_enabled_admin' => ['ورود ادمین', 'فعال بودن کپچا برای ورود به پنل مدیریت'],
                                'captcha_enabled_booking' => ['رزرو نوبت', 'فعال بودن کپچا در فرم رزرو آنلاین'],
                                'captcha_enabled_newsletter' => ['خبرنامه', 'فعال بودن کپچا در فرم عضویت خبرنامه'],
                            ];
                            foreach ($captchaToggles as $key => $info) :
                                $val = $captcha_settings[$key] ?? '1';
                                ?>
                            <label class="flex items-center justify-between p-4 bg-zinc-50 rounded-xl cursor-pointer hover:bg-rose-50 transition-all">
                                <div>
                                    <div class="font-semibold text-sm"><?= $info[0] ?></div>
                                    <div class="text-xs text-zinc-400 mt-0.5"><?= $info[1] ?></div>
                                </div>
                                <select name="<?= $key ?>" class="px-3 py-1.5 bg-white border border-zinc-200 rounded-lg text-sm font-semibold">
                                    <option value="1" <?= $val === '1' ? 'selected' : '' ?>>فعال</option>
                                    <option value="0" <?= $val === '0' ? 'selected' : '' ?>>غیرفعال</option>
                                </select>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="font-bold text-lg mb-3">سطح دشواری</h3>
                        <div class="flex gap-3">
                            <?php $difficulty = $captcha_settings['captcha_difficulty'] ?? 'medium'; ?>
                            <?php foreach (['easy' => 'آسان', 'medium' => 'متوسط', 'hard' => 'سخت'] as $val => $label) : ?>
                            <label class="flex items-center gap-2 px-4 py-3 bg-zinc-50 rounded-xl cursor-pointer hover:bg-rose-50 transition-all has-[:checked]:bg-rose-100 has-[:checked]:text-rose-700">
                                <input type="radio" name="captcha_difficulty" value="<?= $val ?>" <?= $difficulty === $val ? 'checked' : '' ?> class="accent-rose-600">
                                <span class="text-sm font-medium"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="border-t border-zinc-100 pt-6">
                    <h3 class="font-bold text-lg mb-3">سوالات ثابت کپچا</h3>
                    <p class="text-sm text-zinc-400 mb-4">از این سوالات بعنوان سوالات امنیتی پیش‌فرض استفاده می‌شود.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php for ($i = 1; $i <= 10; $i++) :
                            $key = 'captcha_question_' . $i;
                            $val = $captcha_settings[$key] ?? '';
                            ?>
                        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-xl">
                            <span class="w-7 h-7 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"><?= faNum($i) ?></span>
                            <input type="text" name="<?= $key ?>" value="<?= e($val) ?>" placeholder="<?= e('مثال: 5 + 3') ?>" class="flex-1 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <button type="submit" class="mt-6 px-8 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ذخیره تنظیمات کپچا</button>
            </form>

        <?php elseif ($section === 'gallery') : ?>
            <?php require __DIR__ . '/gallery.php'; ?>
        <?php else : ?>
            <?php $table = $section;
            if (in_array($section, ['hair-models'])) {
                $table = 'hair_models';
            } ?>
            <div class="mb-6 flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h2 class="text-2xl font-extrabold">مدیریت <?= $sections[$section][1] ?></h2>
                    <?php if (isset($total)) :
                        ?><p class="text-zinc-400 text-sm"><?= faNum($total) ?> مورد</p><?php
                    endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="/admin/<?= e($section) ?>" class="flex items-center gap-2">
                        <input type="text" name="s" value="<?= e($_GET['s'] ?? '') ?>" placeholder="جستجو..." class="px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all w-44">
                        <button type="submit" class="px-3 py-2.5 bg-zinc-100 text-zinc-600 rounded-xl text-sm hover:bg-rose-50 hover:text-rose-600 transition-all"><i class="fa-solid fa-search"></i></button>
                        <?php if (!empty($_GET['s'])) : ?>
                        <a href="/admin/<?= e($section) ?>" class="px-3 py-2.5 bg-red-50 text-red-500 rounded-xl text-sm hover:bg-red-100 transition-all"><i class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </form>
                    <?php if (!in_array($section, ['orders', 'transactions', 'newsletter', 'contact-messages', 'reviews', 'blog-comments', 'enrollments', 'appointments', 'users'])) : ?>
                    <button onclick="showAddModal()" class="px-5 py-2.5 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">
                        <i class="fa-solid fa-plus ml-1"></i>افزودن جدید
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($section === 'orders' && !empty($orderStats)) : ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="text-xs text-zinc-400 mb-1">کل سفارش‌ها</div>
                    <div class="text-2xl font-extrabold"><?= faNum($orderStats['total_orders'] ?? 0) ?></div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="text-xs text-zinc-400 mb-1">مجموع فروش</div>
                    <div class="text-2xl font-extrabold text-green-600"><?= priceFormat($orderStats['total_revenue'] ?? 0) ?></div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="text-xs text-zinc-400 mb-1">فروش امروز</div>
                    <div class="text-lg font-extrabold text-amber-600"><?= priceFormat($orderStats['today_revenue'] ?? 0) ?></div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
                    <div class="text-xs text-zinc-400 mb-1">در انتظار تأیید</div>
                    <div class="text-2xl font-extrabold text-rose-600"><?= faNum($orderStats['pending_count'] ?? 0) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(225,29,72,0.06)] overflow-x-auto">
                <table class="w-full border-collapse admin-table">
                    <thead>
                        <tr class="bg-rose-50">
                            <?php foreach ($columns as $col) : ?>
                            <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm whitespace-nowrap"><?= $col['label'] ?></th>
                            <?php endforeach; ?>
                            <th class="text-center py-3.5 px-4 text-zinc-400 font-semibold text-sm">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)) : ?>
                            <?php foreach ($items as $item) : ?>
                            <tr class="border-b border-rose-100 hover:bg-rose-50/50 transition-all">
                                <?php foreach ($columns as $col) :
                                    $val = $item[$col['key']] ?? '';
                                    if ($col['type'] === 'image') : ?>
                                        <td class="py-3 px-4"><img src="/assets/images/<?= e($val) ?>" class="w-12 h-12 rounded-lg object-cover" onerror="this.style.display='none'"></td>
                                    <?php elseif ($col['type'] === 'price') : ?>
                                        <td class="py-3 px-4 font-bold"><?= priceFormat($val) ?></td>
                                    <?php elseif ($col['type'] === 'status') : ?>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= match ($val) {
                                            'active', 'confirmed', 'delivered', 'completed' => 'bg-green-50 text-green-700',
                                            'pending', 'processing', 'shipped' => 'bg-amber-50 text-amber-700',
                                            'cancelled', 'failed', 'rejected' => 'bg-red-50 text-red-500',
                                            default => 'bg-zinc-100 text-zinc-600',
                                                                                                                          } ?>"><?= e($val) ?></span></td>
                                    <?php elseif ($col['type'] === 'boolean') : ?>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $val ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-500' ?>"><?= $val ? 'بله' : 'خیر' ?></span></td>
                                    <?php elseif ($col['type'] === 'textarea') : ?>
                                        <td class="py-3 px-4 text-sm text-zinc-400 max-w-xs truncate"><?= e(strip_tags($val)) ?></td>
                                    <?php else : ?>
                                        <td class="py-3 px-4 text-sm"><?= e($val) ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex gap-1.5 justify-center">
                                        <button onclick="showEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-semibold hover:bg-rose-600 hover:text-white transition-all">ویرایش</button>
                                        <form action="/admin/<?= e($section) ?>/delete/<?= $item['id'] ?>" method="POST" class="inline" onsubmit="return confirm('آیتم حذف شود؟')">
                                            <?= csrf() ?>
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-semibold hover:bg-red-500 hover:text-white transition-all">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="<?= count($columns) + 1 ?>" class="text-center py-10 text-zinc-400">آیتمی یافت نشد</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($totalPages) && $totalPages > 1) : ?>
            <div class="flex justify-center items-center gap-2 mt-6">
                <?php if ($page > 1) : ?>
                <a href="?page=<?= $page - 1 ?>&s=<?= e($_GET['s'] ?? '') ?>" class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all text-sm">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <?php endif; ?>
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++) : ?>
                <a href="?page=<?= $i ?>&s=<?= e($_GET['s'] ?? '') ?>" class="w-10 h-10 rounded-full flex items-center justify-center font-medium text-sm transition-all <?= $i === $page ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'border border-zinc-300 text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600' ?>">
                    <?= faNum($i) ?>
                </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages) : ?>
                <a href="?page=<?= $page + 1 ?>&s=<?= e($_GET['s'] ?? '') ?>" class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all text-sm">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div id="itemModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden" onclick="closeItemModal(event)">
                <div class="bg-white rounded-[20px] p-6 w-full max-w-2xl mx-4 shadow-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-xl font-bold" id="modalTitle">افزودن جدید</h3>
                        <button onclick="closeItemModal()" class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-all text-sm"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form action="/admin/<?= e($section) ?>/save" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?= csrf() ?>
                        <input type="hidden" name="id" id="item-id" value="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="modal-fields">
                            <?php foreach ($columns as $col) :
                                if ($col['key'] === 'id') {
                                    continue;
                                }
                                ?>
                            <div class="<?= in_array($col['type'], ['textarea', 'image', 'file']) ? 'md:col-span-2' : '' ?>">
                                <label class="block text-sm font-semibold mb-1.5"><?= $col['label'] ?></label>
                                <?php if ($col['type'] === 'textarea') : ?>
                                    <?php if ($section === 'blog' && $col['key'] === 'content') : ?>
                                    <textarea name="<?= $col['key'] ?>" class="form-input w-full tinymce-editor" <?= ($col['required'] ?? false) ? 'required' : '' ?>></textarea>
                                    <?php else : ?>
                                    <textarea name="<?= $col['key'] ?>" rows="3" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" <?= ($col['required'] ?? false) ? 'required' : '' ?>></textarea>
                                    <?php endif; ?>
                                <?php elseif ($col['type'] === 'image') : ?>
                                    <div class="image-field-wrapper">
                                    <input type="file" name="<?= $col['key'] ?>" accept="image/*" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700">
                                    </div>
                                <?php elseif ($col['type'] === 'file') : ?>
                                    <div class="image-field-wrapper">
                                    <input type="file" name="<?= $col['key'] ?>" accept="<?= $col['accept'] ?? '*' ?>" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700">
                                    </div>
                                <?php elseif ($col['type'] === 'select') : ?>
                                    <select name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" <?= ($col['required'] ?? false) ? 'required' : '' ?>>
                                        <option value="">انتخاب کنید</option>
                                        <?php foreach (($col['options'] ?? []) as $opt) : ?>
                                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($col['type'] === 'boolean') : ?>
                                    <select name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                                        <option value="1">بله</option>
                                        <option value="0">خیر</option>
                                    </select>
                                <?php elseif ($col['type'] === 'status') : ?>
                                    <select name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                                        <?php
                                        $statusOptions = $col['options'] ?? ['pending', 'confirmed', 'processing', 'completed', 'delivered', 'shipped', 'cancelled', 'rejected', 'failed', 'active'];
                                        foreach ($statusOptions as $opt) : ?>
                                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($col['type'] === 'price') : ?>
                                    <input type="number" name="<?= $col['key'] ?>" step="1000" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" <?= ($col['required'] ?? false) ? 'required' : '' ?>>
                                <?php else : ?>
                                    <input type="<?= $col['type'] === 'password' ? 'password' : 'text' ?>" name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" <?= ($col['required'] ?? false) ? 'required' : '' ?>>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>

                            <?php if ($section === 'products') : ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold mb-1.5">گالری تصاویر</label>
                                <div id="gallery-preview" class="flex flex-wrap gap-2 mb-2"></div>
                                <input type="file" name="gallery_images[]" accept="image/*" multiple
                                    class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700">
                                <p class="text-xs text-zinc-400 mt-1">می‌توانید چند تصویر را هم‌زمان انتخاب کنید</p>
                                <input type="hidden" name="delete_gallery_ids" id="delete-gallery-ids" value="">
                            </div>
                            <?php endif; ?>

                            <?php if ($section === 'artists' && !empty($allServices)) : ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold mb-1.5">خدمات مرتبط</label>
                                <div class="grid grid-cols-2 gap-2" id="artist-services-cb">
                                    <?php foreach ($allServices as $svc) : ?>
                                    <label class="flex items-center gap-2 bg-rose-50 rounded-xl px-3 py-2 cursor-pointer hover:bg-rose-100 transition-all">
                                        <input type="checkbox" name="services[]" value="<?= $svc['id'] ?>" class="artist-service-cb">
                                        <span class="text-sm"><?= e($svc['title']) ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($section === 'orders') : ?>
                            <div class="md:col-span-2 bg-zinc-50 rounded-xl p-4" id="order-detail-panel">
                                <h4 class="font-bold text-sm mb-3 text-zinc-700">جزئیات سفارش</h4>
                                <div id="order-items-list" class="space-y-2 mb-3"></div>
                                <div id="order-address-info" class="text-sm text-zinc-600 border-t border-zinc-200 pt-3 mt-3"></div>
                                <div id="order-payment-info" class="text-sm text-zinc-600 pt-2"></div>
                            </div>
                            <script>
                            function renderOrderPanel(orderId) {
                                var items = (window._orderItems || {})[orderId] || [];
                                var detail = (window._orderData || {})[orderId] || {};
                                var listEl = document.getElementById('order-items-list');
                                var addrEl = document.getElementById('order-address-info');
                                var payEl = document.getElementById('order-payment-info');
                                if (!items.length) { listEl.innerHTML = '<p class="text-xs text-zinc-400">آیتمی یافت نشد</p>'; }
                                else {
                                    var html = '';
                                    items.forEach(function(it) {
                                        var img = it.product_image ? '<img src="/assets/images/' + it.product_image + '" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" onerror="this.style.display=\'none\'">' : '<div class="w-10 h-10 rounded-lg bg-zinc-200 flex items-center justify-center text-xs text-zinc-400 flex-shrink-0"><i class="fa-solid fa-box"></i></div>';
                                        html += '<div class="flex items-center gap-3 bg-white rounded-lg p-2.5 text-xs">' + img + '<div class="flex-1"><div class="font-medium text-zinc-800">' + (it.product_name || 'محصول') + '</div><div class="text-zinc-400">' + it.quantity + ' عدد × ' + Number(it.price).toLocaleString() + ' تومان</div></div></div>';
                                    });
                                    listEl.innerHTML = html;
                                }
                                var addrParts = [];
                                if (detail.address) addrParts.push(detail.address);
                                if (detail.postal_code) addrParts.push('کدپستی: ' + detail.postal_code);
                                addrEl.innerHTML = addrParts.length ? '<span class="font-medium">آدرس:</span> ' + addrParts.join(' — ') : '';
                                var payParts = [];
                                if (detail.payment_status) payParts.push('وضعیت: ' + detail.payment_status);
                                if (detail.payment_method) payParts.push(detail.payment_method);
                                if (detail.payment_id) payParts.push('کد پیگیری: ' + detail.payment_id);
                                if (detail.coupon_code) payParts.push('کد تخفیف: ' + detail.coupon_code + (detail.coupon_discount ? ' (' + Number(detail.coupon_discount).toLocaleString() + ' تومان)' : ''));
                                payEl.innerHTML = payParts.length ? '<span class="font-medium">پرداخت:</span> ' + payParts.join(' — ') : '';
                            }
                            </script>
                            <?php endif; ?>
                        <button type="submit" id="save-btn" class="w-full py-3.5 bg-gradient-to-l from-rose-600 to-rose-700 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" onclick="this.disabled=true;this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin ml-2\'></i>در حال ذخیره...';this.closest('form').submit();">ذخیره</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php if ($section === 'artists') : ?>
<script>window._artistServices = <?= $artistServicesJson ?? '{}' ?>;</script>
<?php endif; ?>
<?php if ($section === 'products') : ?>
<script>window._productGallery = <?= $productGalleryJson ?? '{}' ?>;</script>
<?php endif; ?>
<?php if ($section === 'orders') : ?>
    <?php
    $_orderDataMap = [];
    foreach ($items as $o) {
        $_orderDataMap[$o['id']] = [
        'address' => $o['address'] ?? '',
        'postal_code' => $o['postal_code'] ?? '',
        'payment_status' => $o['payment_status'] ?? '',
        'payment_method' => $o['payment_method'] ?? '',
        'payment_id' => $o['payment_id'] ?? '',
        'coupon_code' => $o['coupon_code'] ?? '',
        'coupon_discount' => $o['coupon_discount'] ?? 0,
        'discount' => $o['discount'] ?? 0,
        ];
    }
    ?>
<script>
window._orderItems = <?= json_encode($orderItems ?? [], JSON_UNESCAPED_UNICODE) ?>;
window._orderData = <?= json_encode($_orderDataMap, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php endif; ?>
<script>
function toggleVideoField() {
    var typeSelect = document.querySelector('select[name="video_type"]');
    var urlField = document.querySelector('input[name="video_url"]');
    if (!typeSelect || !urlField) return;
    if (typeSelect.value === 'upload') {
        urlField.type = 'file';
        urlField.accept = 'video/mp4,video/webm,video/ogg';
        urlField.placeholder = 'فایل ویدیو را انتخاب کنید';
    } else {
        urlField.type = 'text';
        urlField.accept = '';
        urlField.placeholder = typeSelect.value === 'youtube' ? 'لینک یوتیوب را وارد کنید' : 'لینک آپارات را وارد کنید';
    }
}
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'video_type') toggleVideoField();
});
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('itemModal');
    if (modal) {
        var observer = new MutationObserver(function() {
            if (!modal.classList.contains('hidden')) {
                setTimeout(toggleVideoField, 50);
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'افزودن جدید';
    document.getElementById('item-id').value = '';
    document.querySelectorAll('#modal-fields .form-input').forEach(el => el.value = '');
    document.querySelectorAll('.artist-service-cb').forEach(function(cb) { cb.checked = false; });
    var gp = document.getElementById('gallery-preview');
    if (gp) gp.innerHTML = '';
    var dg = document.getElementById('delete-gallery-ids');
    if (dg) dg.value = '';
    document.getElementById('itemModal').classList.remove('hidden');
}
function showEditModal(item) {
    document.getElementById('modalTitle').textContent = 'ویرایش';
    document.getElementById('item-id').value = item.id || '';
    document.querySelectorAll('#modal-fields .form-input').forEach(el => {
        if (el.type === 'file') {
            el.value = '';
            var wrapper = el.closest('.image-field-wrapper');
            if (wrapper) {
                var preview = wrapper.querySelector('.image-preview');
                if (preview) preview.remove();
            }
            if (item[el.name]) {
                var img = document.createElement('img');
                img.src = '/assets/images/' + item[el.name];
                img.className = 'image-preview w-16 h-16 rounded-lg object-cover mt-2 border';
                img.onerror = function() { this.remove(); };
                el.parentNode.insertBefore(img, el.nextSibling);
            }
        }
        else if (el.type === 'select-one') el.value = item[el.name] !== null && item[el.name] !== undefined ? item[el.name] : '';
        else el.value = item[el.name] !== null && item[el.name] !== undefined ? item[el.name] : '';
    });
    if (window._artistServices && item.id) {
        var assigned = window._artistServices[item.id] || [];
        document.querySelectorAll('.artist-service-cb').forEach(function(cb) {
            cb.checked = assigned.indexOf(parseInt(cb.value)) !== -1;
        });
    }
    if (window._productGallery && item.id) {
        var gallery = window._productGallery[item.id] || [];
        var container = document.getElementById('gallery-preview');
        var deleteInput = document.getElementById('delete-gallery-ids');
        if (container) {
            container.innerHTML = '';
            gallery.forEach(function(img) {
                var wrapper = document.createElement('div');
                wrapper.className = 'relative group';
                wrapper.dataset.id = img.id;
                var imgEl = document.createElement('img');
                imgEl.src = '/assets/images/' + img.image;
                imgEl.className = 'w-16 h-16 rounded-lg object-cover border border-zinc-200';
                imgEl.onerror = function() { this.remove(); };
                var delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition';
                delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                delBtn.onclick = function() {
                    wrapper.remove();
                    var ids = deleteInput.value ? deleteInput.value.split(',') : [];
                    ids.push(String(img.id));
                    deleteInput.value = ids.join(',');
                };
                wrapper.appendChild(imgEl);
                wrapper.appendChild(delBtn);
                container.appendChild(wrapper);
            });
        }
    }
    document.getElementById('itemModal').classList.remove('hidden');
}
function closeItemModal(e) {
    if (!e || e.target === document.getElementById('itemModal'))
        document.getElementById('itemModal').classList.add('hidden');
}
</script>

<?php if ($section === 'orders') : ?>
<script>
(function() {
    var _origShowEditOrder = window.showEditModal;
    window.showEditModal = function(item) {
        _origShowEditOrder(item);
        if (item && item.id) renderOrderPanel(item.id);
    };
})();
</script>
<?php endif; ?>

<?php if ($section === 'blog') : ?>
<script src="/assets/libs/tinymce/tinymce.min.js"></script>
<script>
function initBlogEditor(content) {
    if (tinymce.activeEditor) tinymce.remove();
    setTimeout(function() {
        tinymce.init({
            selector: '.tinymce-editor',
            height: 500,
            language: 'fa',
            directionality: 'rtl',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen media table wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline | forecolor backcolor | alignright aligncenter | bullist numlist | link image | code',
            branding: false,
            promotion: false,
            setup: function(editor) {
                if (content) editor.on('init', function() { editor.setContent(content); });
                editor.on('change', function() { editor.save(); });
            }
        });
    }, 200);
}
function destroyBlogEditor() {
    if (tinymce.activeEditor) tinymce.remove();
}

var _origShowAdd = showAddModal;
showAddModal = function() {
    _origShowAdd();
    <?php if ($section === 'blog') :
        ?>initBlogEditor('');<?php
    endif; ?>
};
var _origShowEdit = showEditModal;
showEditModal = function(item) {
    _origShowEdit(item);
    <?php if ($section === 'blog') :
        ?>initBlogEditor(item.content || '');<?php
    endif; ?>
};
var _origClose = closeItemModal;
closeItemModal = function(e) {
    _origClose(e);
    destroyBlogEditor();
};
</script>
<?php endif; ?>
