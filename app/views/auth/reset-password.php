<?php $title = 'تغییر رمز عبور | موبارو'; ?>
<div class="min-h-screen bg-gradient-to-br from-rose-50 to-white flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-rose-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-key text-rose-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-zinc-800">تغییر رمز عبور</h1>
            <p class="text-zinc-500 mt-2">رمز عبور جدید خود را وارد کنید</p>
        </div>

        <?php if ($errors = $_SESSION['flash_errors'] ?? []): ?>
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
                <?php foreach ($errors as $err): ?>
                    <p class="text-red-600 text-sm"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php unset($_SESSION['flash_errors']); endif; ?>

        <form method="POST" action="/reset-password" class="bg-white rounded-3xl shadow-xl p-8">
            <input type="hidden" name="_csrf" value="<?= e($_SESSION['_csrf'] ?? '') ?>">

            <div class="mb-4">
                <label for="reset-password" class="block text-sm font-semibold text-zinc-700 mb-2">رمز عبور جدید</label>
                <input id="reset-password" type="password" name="password" minlength="6"
                    class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none transition-all"
                    placeholder حداقل ۶ کاراکتر" required>
            </div>

            <div class="mb-6">
                <label for="reset-password-confirm" class="block text-sm font-semibold text-zinc-700 mb-2">تکرار رمز عبور</label>
                <input id="reset-password-confirm" type="password" name="password_confirm" minlength="6"
                    class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none transition-all"
                    placeholder="رمز عبور را دوباره وارد کنید" required>
            </div>

            <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold transition-all">
                <i class="fa-solid fa-check ml-2"></i>تغییر رمز عبور
            </button>

            <div class="text-center mt-6">
                <a href="/login" class="text-sm text-zinc-500 hover:text-rose-600 transition-colors">
                    <i class="fa-solid fa-arrow-right ml-1"></i>بازگشت به صفحه ورود
                </a>
            </div>
        </form>
    </div>
</div>
