<?php if (!empty($order)) : ?>
<div class="mb-6">
    <a href="/dashboard/orders" class="inline-flex items-center gap-2 text-[#B76E79] font-semibold hover:underline">
        <i class="fa-solid fa-arrow-right"></i> بازگشت به سفارش‌ها
    </a>
</div>

<div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-5">
    <div class="flex justify-between items-center pb-4 border-b border-[#efe5dc] mb-4 flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-extrabold">سفارش <?= e($order['tracking_code']) ?></h2>
            <p class="text-[#9e9e9e] text-sm mt-1"><?= jdate('Y/m/d H:i', strtotime($order['created_at'])) ?></p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold
            <?= match ($order['status']) {
                'pending' => 'bg-amber-50 text-amber-700',
                'processing' => 'bg-blue-50 text-blue-700',
                'shipped' => 'bg-purple-50 text-purple-700',
                'delivered' => 'bg-green-50 text-green-700',
                'cancelled' => 'bg-red-50 text-red-600',
                default => 'bg-gray-100 text-gray-600',
            } ?>">
            <?= match ($order['status']) {
                'pending' => 'در انتظار',
                'processing' => 'در حال پردازش',
                'shipped' => 'ارسال شده',
                'delivered' => 'تحویل شده',
                'cancelled' => 'لغو شده',
                default => $order['status'],
            } ?>
        </span>
    </div>

    <div class="tracking flex justify-between relative my-5">
        <div class="absolute top-[15px] right-[15px] left-[15px] h-0.5 bg-[#efe5dc] z-0"></div>
        <?php
        $stepLabels = ['در انتظار', 'در حال پردازش', 'ارسال شده', 'تحویل شده'];
        $trackingSteps = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
        $currentStep = $trackingSteps[$order['status']] ?? 0;
        foreach ($stepLabels as $i => $label) : ?>
        <div class="flex flex-col items-center gap-1.5 relative z-10 flex-1">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                <?= $i < $currentStep ? 'bg-[#B76E79] border-[#B76E79] text-white' : ($i === $currentStep ? 'bg-[#D4AF37] border-[#D4AF37] text-white shadow-[0_0_0_4px_rgba(212,175,55,0.2)]' : 'bg-white border-2 border-[#efe5dc] text-[#9e9e9e]') ?>">
                <i class="fa-solid <?= $i < $currentStep ? 'fa-check' : ($i === $currentStep ? 'fa-spinner fa-spin' : 'fa-circle') ?>"></i>
            </div>
            <span class="text-xs <?= $i <= $currentStep ? 'text-zinc-800 font-semibold' : 'text-[#9e9e9e]' ?>"><?= $label ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

    <?php if (!empty($items)) : ?>
<div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-5">
    <h3 class="font-bold text-lg mb-4">اقلام سفارش</h3>
        <?php foreach ($items as $item) : ?>
    <div class="flex items-center gap-4 py-3 border-b border-[#efe5dc] last:border-b-0">
        <img src="/assets/images/<?= e($item['image'] ?? '') ?>" class="w-16 h-16 rounded-xl object-cover" onerror="this.src='/media/80/80/<?= $item['product_id'] ?>'">
        <div class="flex-1">
            <div class="font-semibold"><?= e($item['product_name']) ?></div>
            <div class="text-[#9e9e9e] text-sm">تعداد: <?= $item['quantity'] ?></div>
        </div>
        <div class="font-bold text-[#B76E79]"><?= priceFormat($item['price'] * $item['quantity']) ?></div>
    </div>
        <?php endforeach; ?>
</div>
    <?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
        <h3 class="font-bold text-lg mb-3">اطلاعات ارسال</h3>
        <div class="space-y-2 text-sm">
            <?php if (!empty($order['address'])) : ?>
            <div><span class="text-[#9e9e9e]">آدرس:</span> <?= e($order['address']) ?></div>
            <?php endif; ?>
            <?php if (!empty($order['postal_code'])) : ?>
            <div><span class="text-[#9e9e9e]">کد پستی:</span> <?= e($order['postal_code']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
        <h3 class="font-bold text-lg mb-3">خلاصه پرداخت</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-[#9e9e9e]">جمع سفارش</span>
                <span><?= priceFormat($order['total']) ?></span>
            </div>
            <?php if (!empty($order['payment_status'])) : ?>
            <div class="flex justify-between">
                <span class="text-[#9e9e9e]">وضعیت پرداخت</span>
                <span class="font-semibold <?= $order['payment_status'] === 'paid' ? 'text-green-600' : 'text-amber-600' ?>">
                    <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : ($order['payment_status'] === 'pending' ? 'در انتظار پرداخت' : e($order['payment_status'])) ?>
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($order['payment_method'])) : ?>
            <div class="flex justify-between">
                <span class="text-[#9e9e9e]">روش پرداخت</span>
                <span><?= e($order['payment_method']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($order['payment_id'])) : ?>
            <div class="flex justify-between">
                <span class="text-[#9e9e9e]">کد پیگیری پرداخت</span>
                <span dir="ltr" class="text-xs"><?= e($order['payment_id']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($order['discount']) && $order['discount'] > 0) : ?>
            <div class="flex justify-between text-green-600">
                <span>تخفیف</span>
                <span>- <?= priceFormat($order['discount']) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between pt-2 border-t border-[#efe5dc] font-bold text-lg text-[#B76E79]">
                <span>مبلغ نهایی</span>
                <span><?= priceFormat($order['total'] - ($order['discount'] ?? 0)) ?></span>
            </div>
        </div>
    </div>
    <?php if ($order['status'] === 'pending') : ?>
    <div class="mt-5 text-center">
        <button onclick="cancelOrderDetail(<?= $order['id'] ?>)" class="px-6 py-3 bg-red-50 text-red-500 rounded-xl font-semibold hover:bg-red-500 hover:text-white transition-all">
            <i class="fa-solid fa-xmark ml-2"></i>لغو سفارش
        </button>
    </div>
    <?php endif; ?>
</div>
<?php else : ?>
<div class="text-center py-12 text-[#9e9e9e]">
    <p>سفارش یافت نشد.</p>
</div>
<?php endif; ?>
<script>
function cancelOrderDetail(id) {
    if (!confirm('آیا از لغو سفارش مطمئن هستید؟')) return;
    fetch('/dashboard/order/cancel', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_id=' + id + '&' + csrfParam()
    }).then(r => r.json()).then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
