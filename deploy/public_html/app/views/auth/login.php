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
                <?php if ($err = flashError('rate_limit')) : ?>
                    <p class="text-red-500 text-xs text-center bg-red-50 py-3 rounded-2xl"><?= e($err) ?></p>
                <?php endif; ?>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">شماره تلفن همراه</label>
                    <div class="flex border rounded-3xl px-6 items-center focus-within:border-rose-500 transition-colors">
                        <span class="text-zinc-400">+۹۸</span>
                        <input name="phone" type="tel" placeholder="۹۱۲۳۴۵۶۷۸۹"
                               class="flex-1 py-6 outline-none text-lg placeholder:text-zinc-300 px-4 bg-transparent"
                               value="<?= e(old('phone')) ?>">
                    </div>
                    <?php if ($err = flashError('phone')) : ?>
                        <p class="text-red-500 text-xs mt-2"><?= e($err) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">رمز عبور</label>
                    <input name="password" type="password" placeholder="••••••••"
                           class="w-full border rounded-3xl px-7 py-6 outline-none focus:border-rose-500 transition-colors">
                    <?php if ($err = flashError('password')) : ?>
                        <p class="text-red-500 text-xs mt-2"><?= e($err) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">کد امنیتی</label>
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-zinc-700" id="captcha-question"><?= e($captchaQuestion ?? '۵ + ۳') ?> = ?</span>
                        <button type="button" onclick="refreshLoginCaptcha()" class="text-xs text-rose-500 hover:underline">تغییر</button>
                    </div>
                    <input name="captcha" type="text" inputmode="numeric" placeholder="پاسخ"
                           class="w-full border rounded-3xl px-7 py-5 outline-none focus:border-rose-500 transition-colors mt-2 text-center text-lg font-bold"
                           required>
                    <?php if ($err = flashError('captcha')) : ?>
                        <p class="text-red-500 text-xs mt-2"><?= e($err) ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="w-full py-7 bg-rose-600 hover:bg-rose-700 transition-all rounded-3xl text-white font-semibold text-lg shadow-inner">
                    ورود به حساب
                </button>
                <?php if (GoogleAuth::isConfigured()) : ?>
                <div class="flex items-center gap-3 my-2">
                    <div class="flex-1 h-px bg-zinc-200"></div>
                    <span class="text-xs text-zinc-400">یا</span>
                    <div class="flex-1 h-px bg-zinc-200"></div>
                </div>
                <a href="/auth/google" class="w-full py-5 bg-white border-2 border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition-all rounded-3xl text-zinc-700 font-semibold text-sm flex items-center justify-center gap-3">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    ورود با گوگل
                </a>
                <?php endif; ?>
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
                <div>
                    <label class="text-xs text-zinc-500 block mb-2">کد امنیتی</label>
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-zinc-700" id="forgot-captcha-question"><?= e($captchaQuestion ?? '۵ + ۳') ?> = ?</span>
                        <button type="button" onclick="refreshLoginCaptcha('forgot-captcha-question')" class="text-xs text-rose-500 hover:underline">تغییر</button>
                    </div>
                    <input name="captcha" type="text" inputmode="numeric" placeholder="پاسخ"
                           class="w-full border rounded-3xl px-8 py-5 text-center text-lg font-bold focus:border-rose-500 outline-none mt-2"
                           required>
                </div>
                <button type="submit" class="w-full py-7 bg-amber-400 text-zinc-900 font-bold rounded-3xl">ارسال درخواست</button>
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
function refreshLoginCaptcha(targetId) {
    var body = '_csrf=' + encodeURIComponent(document.querySelector('meta[name="csrf"]').content);
    fetch('/booking/captcha/refresh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        var el = document.getElementById(targetId || 'captcha-question');
        if (el && d.question) el.textContent = d.question + ' = ?';
    })
    .catch(function() {});
}
</script>
<?php clearFlashErrors(); ?>
