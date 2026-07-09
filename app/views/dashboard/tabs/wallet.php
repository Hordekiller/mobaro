<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">کیف پول و امتیازات</h1>
    <p class="text-[#9e9e9e] text-sm">مدیریت موجودی و امتیازات</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    <div class="bg-gradient-to-br from-[#B76E79] to-[#D4AF37] text-white p-6 rounded-[20px] relative overflow-hidden">
        <div class="absolute -top-12 -left-12 w-[200px] h-[200px] bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-20 -right-8 w-[180px] h-[180px] bg-white/8 rounded-full"></div>
        <p class="text-sm opacity-90 mb-2 relative">موجودی کیف پول</p>
        <h2 class="text-3xl font-extrabold mb-4 relative"><?= priceFormat($user['wallet']) ?></h2>
        <button class="px-4 py-2.5 bg-white/25 text-white rounded-xl font-semibold backdrop-blur hover:bg-white/40 transition-all relative">افزایش موجودی</button>
    </div>
    <div class="bg-white p-6 rounded-[20px] border-2 border-[#D4AF37]">
        <h2 class="text-3xl font-extrabold text-[#D4AF37] mb-1"><?= e(number_format($user['points'])) ?></h2>
        <p class="text-[#9e9e9e] mb-3.5">امتیازات شما</p>
        <p class="text-sm leading-relaxed">با هر خرید و رزرو نوبت امتیاز جمع کنید و از تخفیف‌های ویژه بهره‌مند شوید.</p>
    </div>
</div>

<div class="mt-5">
    <h3 class="font-bold text-lg mb-4">تخفیف‌های من</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        <div class="bg-gradient-to-l from-[#FDF6F0] to-white border-2 border-dashed border-[#B76E79] rounded-[14px] p-4 flex justify-between items-center gap-2.5">
            <div>
                <h5 class="font-bold mb-1">تخفیف نوبت اول</h5>
                <p class="text-xs text-[#9e9e9e]">۲۰٪ تخفیف برای اولین رزرو</p>
            </div>
            <button onclick="copyCode('MOBARO20')" class="px-3.5 py-2 bg-[#B76E79] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all whitespace-nowrap">MOBARO20</button>
        </div>
        <div class="bg-gradient-to-l from-[#FDF6F0] to-white border-2 border-dashed border-[#B76E79] rounded-[14px] p-4 flex justify-between items-center gap-2.5">
            <div>
                <h5 class="font-bold mb-1">تخفیف محصولات</h5>
                <p class="text-xs text-[#9e9e9e]">۱۵٪ تخفیف خرید اول</p>
            </div>
            <button onclick="copyCode('WELCOME15')" class="px-3.5 py-2 bg-[#B76E79] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all whitespace-nowrap">WELCOME15</button>
        </div>
    </div>
</div>

<div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mt-5">
    <h3 class="font-bold text-lg mb-4">تراکنش‌های اخیر</h3>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="text-right py-3.5 px-3 text-[#9e9e9e] font-semibold text-sm bg-[#FDF6F0] rounded-r-xl">تاریخ</th>
                    <th class="text-right py-3.5 px-3 text-[#9e9e9e] font-semibold text-sm bg-[#FDF6F0]">توضیحات</th>
                    <th class="text-left py-3.5 px-3 text-[#9e9e9e] font-semibold text-sm bg-[#FDF6F0] rounded-l-xl">مبلغ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr class="border-b border-[#efe5dc]">
                        <td class="py-3.5 px-3 text-sm"><?= jdate('Y/m/d', strtotime($t['created_at'])) ?></td>
                        <td class="py-3.5 px-3 text-sm"><?= e($t['description']) ?></td>
                        <td class="py-3.5 px-3 text-sm text-left font-bold <?= in_array($t['type'], ['wallet_deposit', 'points_earn']) ? 'text-green-600' : 'text-red-500' ?>">
                            <?= in_array($t['type'], ['wallet_deposit', 'points_earn']) ? '+' : '-' ?>
                            <?= e(number_format($t['amount'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center py-8 text-[#9e9e9e]">تراکنشی یافت نشد</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('کد تخفیف کپی شد: ' + code, 'success');
    }).catch(() => {
        showToast('خطا در کپی کد', 'error');
    });
}
</script>
