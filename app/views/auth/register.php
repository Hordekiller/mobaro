<?php $title = 'ثبت‌نام | موبارو'; ?>
<div class="min-h-screen flex items-center justify-center px-4 py-20 bg-gradient-to-br from-rose-50 to-white">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-8 text-center">
                <a href="/" class="inline-flex items-center gap-x-3 mb-8">
                    <div class="w-11 h-11 bg-rose-600 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-spa text-white text-2xl"></i>
                    </div>
                    <span class="logo-font text-3xl font-bold text-rose-600">موبارو</span>
                </a>
                <h2 class="text-2xl font-bold mb-2">ثبت‌نام رایگان</h2>
                <p class="text-zinc-500 text-sm mb-8">به جمع مشتریان موبارو بپیوندید</p>
            </div>

            <form method="POST" action="/register" class="px-8 pb-8 space-y-6">
                <?= csrf() ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-500 block mb-2">نام</label>
                        <input name="name" type="text" placeholder="سارا"
                               class="w-full border rounded-3xl px-6 py-6 outline-none focus:border-rose-500 transition-colors"
                               value="<?= e(old('name')) ?>">
                        <?php if ($err = flashError('name')): ?><p class="text-red-500 text-xs mt-1"><?= e($err) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 block mb-2">نام خانوادگی</label>
                        <input name="family" type="text" placeholder="احمدی"
                               class="w-full border rounded-3xl px-6 py-6 outline-none focus:border-rose-500 transition-colors"
                               value="<?= e(old('family')) ?>">
                        <?php if ($err = flashError('family')): ?><p class="text-red-500 text-xs mt-1"><?= e($err) ?></p><?php endif; ?>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">شماره تلفن</label>
                    <div class="flex border rounded-3xl px-6 items-center focus-within:border-rose-500 transition-colors">
                        <span class="text-zinc-400">+۹۸</span>
                        <input name="phone" type="tel" placeholder="۹۱۲۳۴۵۶۷۸۹"
                               class="flex-1 py-6 outline-none text-lg px-4 bg-transparent"
                               value="<?= e(old('phone')) ?>">
                    </div>
                    <?php if ($err = flashError('phone')): ?><p class="text-red-500 text-xs mt-2"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">رمز عبور</label>
                    <input name="password" type="password" placeholder="حداقل ۶ کاراکتر"
                           class="w-full border rounded-3xl px-7 py-6 outline-none focus:border-rose-500 transition-colors">
                    <?php if ($err = flashError('password')): ?><p class="text-red-500 text-xs mt-2"><?= e($err) ?></p><?php endif; ?>
                </div>
                <button type="submit" class="w-full py-7 bg-zinc-900 hover:bg-black transition-all rounded-3xl text-white font-semibold text-lg">
                    ثبت‌نام رایگان
                </button>
                <p class="text-center text-[10px] text-zinc-400">
                    ثبت‌نام شما به معنای پذیرش <a href="#" class="underline">شرایط و قوانین</a> است
                </p>
                <div class="text-center text-xs">
                    <span class="text-zinc-400">قبلاً ثبت‌نام کرده‌اید؟</span>
                    <a href="/login" class="text-rose-500 hover:underline mr-1">ورود</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php clearFlashErrors(); ?>
