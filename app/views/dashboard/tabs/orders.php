<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">سفارش‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">پیگیری سفارش‌های ثبت شده</p>
</div>

<?php if (!empty($orders)): ?>
    <?php foreach ($orders as $order):
        $trackingSteps = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
        $currentStep = $trackingSteps[$order['status']] ?? 0;
        $stepLabels = ['در انتظار', 'در حال پردازش', 'ارسال شده', 'تحویل شده'];
    ?>
    <div class="bg-white rounded-xl p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-3.5">
        <div class="flex justify-between items-center pb-3.5 border-b border-dashed border-[#efe5dc] mb-3.5 flex-wrap gap-2.5">
            <div>
                <span class="font-bold">کد سفارش: <?= e($order['tracking_code']) ?></span>
                <span class="text-[#9e9e9e] text-sm mr-3"><?= jdate('Y/m/d', strtotime($order['created_at'])) ?></span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold
                <?= match($order['status']) {
                    'pending' => 'bg-amber-50 text-amber-700',
                    'processing' => 'bg-blue-50 text-blue-700',
                    'shipped' => 'bg-purple-50 text-purple-700',
                    'delivered' => 'bg-green-50 text-green-700',
                    'cancelled' => 'bg-red-50 text-red-600',
                    default => 'bg-gray-100 text-gray-600',
                } ?>">
                <?= $stepLabels[$currentStep] ?? 'لغو شده' ?>
            </span>
        </div>

        <div class="flex gap-2 mb-3.5 flex-wrap">
            <?php if (!empty($order['items_list'])): ?>
                <p class="text-sm text-[#9e9e9e]"><?= e($order['items_list']) ?></p>
            <?php endif; ?>
        </div>

        <div class="tracking flex justify-between relative my-5">
            <div class="absolute top-[15px] right-[15px] left-[15px] h-0.5 bg-[#efe5dc] z-0"></div>
            <?php foreach ($stepLabels as $i => $label): ?>
            <div class="flex flex-col items-center gap-1.5 relative z-10 flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                    <?= $i < $currentStep ? 'bg-[#B76E79] border-[#B76E79] text-white' : ($i === $currentStep ? 'bg-[#D4AF37] border-[#D4AF37] text-white shadow-[0_0_0_4px_rgba(212,175,55,0.2)]' : 'bg-white border-2 border-[#efe5dc] text-[#9e9e9e]') ?>">
                    <i class="fa-solid <?= $i < $currentStep ? 'fa-check' : ($i === $currentStep ? 'fa-spinner fa-spin' : 'fa-circle') ?>"></i>
                </div>
                <span class="text-xs <?= $i <= $currentStep ? 'text-zinc-800 font-semibold' : 'text-[#9e9e9e]' ?>"><?= $label ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-between items-center gap-2.5 flex-wrap">
            <div class="text-lg font-bold text-[#B76E79]"><?= priceFormat($order['total']) ?></div>
            <button class="px-4 py-2 bg-[#B76E79] text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">جزئیات سفارش</button>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center py-12 text-[#9e9e9e]">
        <i class="fa-solid fa-truck text-5xl mb-4"></i>
        <p>سفارشی ثبت نشده است</p>
        <a href="/shop" class="inline-block mt-4 px-6 py-3 bg-rose-600 text-white rounded-xl text-sm font-semibold">فروشگاه</a>
    </div>
<?php endif; ?>
