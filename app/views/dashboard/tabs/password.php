<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">تغییر رمز عبور</h1>
    <p class="text-[#9e9e9e] text-sm">برای امنیت بیشتر رمز عبور خود را تغییر دهید</p>
</div>

<form action="/dashboard/change-password" method="POST" class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)] max-w-lg">
    <?= csrf() ?>
    <div class="mb-4">
        <label class="block text-sm font-semibold mb-1.5">رمز عبور فعلی</label>
        <div class="relative">
            <input type="password" name="current_password" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all pl-10" required>
            <i class="fa-regular fa-eye absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9e9e9e] cursor-pointer"></i>
        </div>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-semibold mb-1.5">رمز عبور جدید</label>
        <div class="relative">
            <input type="password" name="new_password" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all pl-10" required minlength="6">
            <i class="fa-regular fa-eye absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9e9e9e] cursor-pointer"></i>
        </div>
    </div>
    <div class="mb-5">
        <label class="block text-sm font-semibold mb-1.5">تکرار رمز عبور جدید</label>
        <div class="relative">
            <input type="password" name="new_password_confirm" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all pl-10" required minlength="6">
            <i class="fa-regular fa-eye absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9e9e9e] cursor-pointer"></i>
        </div>
    </div>
    <button type="submit" class="w-full py-3.5 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">تغییر رمز عبور</button>
</form>

<script>
document.querySelectorAll('.fa-eye').forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.closest('.relative').querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});
</script>
