<?php $title = 'ورود | موبارو'; ?>
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
                <h2 class="text-2xl font-bold mb-2">ورود به حساب</h2>
                <p class="text-zinc-500 text-sm mb-8">برای استفاده از پنل کاربری وارد شوید</p>
            </div>

            <form method="POST" action="/login" class="px-8 pb-8 space-y-6">
                <?= csrf() ?>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">شماره تلفن همراه</label>
                    <div class="flex border rounded-3xl px-6 items-center focus-within:border-rose-500 transition-colors">
                        <span class="text-zinc-400">+۹۸</span>
                        <input name="phone" type="tel" placeholder="۹۱۲۳۴۵۶۷۸۹"
                               class="flex-1 py-6 outline-none text-lg placeholder:text-zinc-300 px-4 bg-transparent"
                               value="<?= e(old('phone')) ?>">
                    </div>
                    <?php if ($err = flashError('phone')): ?>
                        <p class="text-red-500 text-xs mt-2"><?= e($err) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">رمز عبور</label>
                    <input name="password" type="password" placeholder="••••••••"
                           class="w-full border rounded-3xl px-7 py-6 outline-none focus:border-rose-500 transition-colors">
                    <?php if ($err = flashError('password')): ?>
                        <p class="text-red-500 text-xs mt-2"><?= e($err) ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="w-full py-7 bg-rose-600 hover:bg-rose-700 transition-all rounded-3xl text-white font-semibold text-lg shadow-inner">
                    ورود به حساب
                </button>
                <div class="flex items-center justify-between text-xs">
                    <a href="/register" class="text-rose-500 hover:underline">ثبت‌نام</a>
                    <span onclick="showForgotSection()" class="text-rose-500 cursor-pointer hover:underline">فراموشی رمز؟</span>
                </div>
            </form>
        </div>

        <div id="forgot-section" class="hidden bg-white rounded-3xl shadow-2xl overflow-hidden mt-6">
            <form method="POST" action="/auth/forgot" class="p-8 space-y-6">
                <?= csrf() ?>
                <div class="text-center">
                    <i class="fa-solid fa-key text-5xl text-amber-400 mb-4"></i>
                    <h3 class="font-semibold text-xl">فراموشی رمز عبور</h3>
                    <p class="text-xs text-zinc-500 mt-2">شماره تلفن خود را وارد کنید</p>
                </div>
                <input name="phone" type="tel" placeholder="شماره تلفن"
                       class="w-full border rounded-3xl px-8 py-6 text-center text-xl focus:border-rose-500 outline-none">
                <button type="submit" class="w-full py-7 bg-amber-400 text-zinc-900 font-bold rounded-3xl">دریافت کد تأیید</button>
                <div onclick="hideForgotSection()" class="text-center text-xs text-rose-500 cursor-pointer">بازگشت به ورود</div>
            </form>
        </div>
    </div>
</div>

<script>
function showForgotSection() {
    document.querySelector('form[action="/login"]').classList.add('hidden');
    document.getElementById('forgot-section').classList.remove('hidden');
}
function hideForgotSection() {
    document.querySelector('form[action="/login"]').classList.remove('hidden');
    document.getElementById('forgot-section').classList.add('hidden');
}
</script>
<?php clearFlashErrors(); ?>
