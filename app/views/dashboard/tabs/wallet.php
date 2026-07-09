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

<div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
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
                        <td class="py-3.5 px-3 text-sm"><?= e($t['created_at']) ?></td>
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
