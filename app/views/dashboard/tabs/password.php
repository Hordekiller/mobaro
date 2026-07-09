<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">تغییر رمز عبور</h1>
    <p class="text-[#9e9e9e] text-sm">برای امنیت بیشتر رمز عبور خود را تغییر دهید</p>
</div>

<form action="/dashboard/password/change" method="POST" class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)] max-w-lg">
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
            <input type="password" name="new_password" id="new_password" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all pl-10" required minlength="6" oninput="checkPasswordStrength(this.value)">
            <i class="fa-regular fa-eye absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9e9e9e] cursor-pointer"></i>
        </div>
        <div class="mt-2 flex gap-1.5" id="pwd-strength">
            <div class="flex-1 h-1.5 bg-[#efe5dc] rounded-full transition-all"></div>
            <div class="flex-1 h-1.5 bg-[#efe5dc] rounded-full transition-all"></div>
            <div class="flex-1 h-1.5 bg-[#efe5dc] rounded-full transition-all"></div>
            <div class="flex-1 h-1.5 bg-[#efe5dc] rounded-full transition-all"></div>
        </div>
        <p class="mt-1 text-xs text-[#9e9e9e]" id="pwd-label">حداقل ۶ کاراکتر</p>
    </div>
    <div class="mb-5">
        <label class="block text-sm font-semibold mb-1.5">تکرار رمز عبور جدید</label>
        <div class="relative">
            <input type="password" name="confirm_password" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all pl-10" required minlength="6">
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

function checkPasswordStrength(pwd) {
    const bars = document.querySelectorAll('#pwd-strength div');
    const label = document.getElementById('pwd-label');
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.length >= 10) score++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd) && /[^a-zA-Z0-9]/.test(pwd)) score++;

    const colors = ['#efe5dc', '#E57373', '#FFA726', '#D4AF37', '#4CAF50'];
    const texts = ['حداقل ۶ کاراکتر', 'ضعیف', 'متوسط', 'قوی', 'بسیار قوی'];
    bars.forEach((bar, i) => {
        bar.style.backgroundColor = i < score ? colors[score] : '#efe5dc';
    });
    label.textContent = texts[score];
    label.style.color = score > 1 ? colors[score] : '#9e9e9e';
}
</script>
