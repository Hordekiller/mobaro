<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">سفارش‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">پیگیری سفارش‌های ثبت شده</p>
</div>

<?php
$statusFilter = $_GET['status'] ?? '';
$filteredOrders = $orders ?? [];
if ($statusFilter !== '') {
    $filteredOrders = array_filter($orders ?? [], fn($o) => $o['status'] === $statusFilter);
}
$statusTabs = [
    '' => 'همه',
    'pending' => 'در انتظار',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل شده',
    'cancelled' => 'لغو شده',
];
$statusCounts = [];
foreach ($statusTabs as $k => $label) {
    $statusCounts[$k] = $k === '' ? count($orders ?? []) : count(array_filter($orders ?? [], fn($o) => $o['status'] === $k));
}
?>
<div class="flex gap-2 mb-4 flex-wrap">
    <?php foreach ($statusTabs as $st => $stLabel): ?>
    <a href="?tab=orders<?= $st ? '&status=' . $st : '' ?>"
       class="px-4 py-2 rounded-full text-xs font-semibold transition-all <?= $statusFilter === $st ? 'bg-[#B76E79] text-white shadow-md' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' ?>">
        <?= $stLabel ?>
        <span class="mr-1 opacity-70">(<?= $statusCounts[$st] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($filteredOrders)): ?>
    <?php foreach ($filteredOrders as $order):
        $isCancelled = $order['status'] === 'cancelled';
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
                <?= match($order['status']) {
                    'pending' => 'در انتظار',
                    'processing' => 'در حال پردازش',
                    'shipped' => 'ارسال شده',
                    'delivered' => 'تحویل شده',
                    'cancelled' => 'لغو شده',
                    default => $order['status'],
                } ?>
            </span>
        </div>

        <div class="flex gap-2 mb-3.5 flex-wrap items-center">
            <?php if (!empty($order['items_images'])): ?>
                <?php $imgs = explode('||', $order['items_images']); ?>
                <?php foreach ($imgs as $i => $img): if (empty($img)) continue; ?>
                    <img src="/assets/images/<?= e($img) ?>"
                         class="w-[60px] h-[60px] rounded-xl object-cover border-2 border-[#efe5dc]"
                         onerror="this.src='/avatar/P/120'"
                         alt="product">
                <?php endforeach; ?>
            <?php elseif (!empty($order['items_list'])): ?>
                <p class="text-sm text-[#9e9e9e]"><?= e($order['items_list']) ?></p>
            <?php endif; ?>
            <?php if (!empty($order['item_count'])): ?>
                <span class="text-xs text-zinc-400 mr-auto"><?= faNum($order['item_count']) ?> قلم</span>
            <?php endif; ?>
        </div>

        <?php if ($isCancelled): ?>
        <div class="my-5 py-4 text-center">
            <span class="inline-flex items-center gap-2 px-5 py-2 bg-red-50 text-red-600 rounded-full text-sm font-semibold">
                <i class="fa-solid fa-ban"></i> این سفارش لغو شده است
            </span>
        </div>
        <?php else: ?>
        <?php
        $trackingSteps = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
        $currentStep = $trackingSteps[$order['status']] ?? 0;
        $stepLabels = ['در انتظار', 'در حال پردازش', 'ارسال شده', 'تحویل شده'];
        ?>
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
        <?php endif; ?>

        <div class="flex justify-between items-center gap-2.5 flex-wrap">
            <div>
                <div class="text-lg font-bold text-[#B76E79]"><?= priceFormat($order['total']) ?></div>
                <?php if (!empty($order['payment_status'])): ?>
                <div class="text-xs mt-1">
                    <span class="text-zinc-400">پرداخت: </span>
                    <span class="<?= $order['payment_status'] === 'paid' ? 'text-green-600' : 'text-amber-600' ?> font-semibold">
                        <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : ($order['payment_status'] === 'pending' ? 'در انتظار پرداخت' : $order['payment_status']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['payment_method'])): ?>
                <div class="text-xs text-zinc-400">روش پرداخت: <?= e($order['payment_method']) ?></div>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <button onclick="window.location.href='/dashboard/order/detail?id=<?= $order['id'] ?>'" class="px-4 py-2 bg-[#B76E79] text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">جزئیات سفارش</button>
                <?php if ($order['status'] === 'pending'): ?>
                <button onclick="cancelOrder(<?= $order['id'] ?>)" class="px-4 py-2 bg-red-50 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-500 hover:text-white transition-all">لغو</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (($orderTotalPages ?? 1) > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-6">
        <?php if (($orderPage ?? 1) > 1): ?>
        <a href="?tab=orders&page=<?= ($orderPage ?? 1) - 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>"
           class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white transition-all text-sm">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php endif; ?>
        <?php
        $startP = max(1, ($orderPage ?? 1) - 2);
        $endP = min($orderTotalPages ?? 1, ($orderPage ?? 1) + 2);
        for ($i = $startP; $i <= $endP; $i++): ?>
        <a href="?tab=orders&page=<?= $i ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>"
           class="w-10 h-10 rounded-full flex items-center justify-center font-medium text-sm transition-all <?= $i === ($orderPage ?? 1) ? 'bg-rose-600 text-white shadow-lg' : 'border border-zinc-300 text-zinc-600 hover:bg-rose-600 hover:text-white' ?>">
            <?= faNum($i) ?>
        </a>
        <?php endfor; ?>
        <?php if (($orderPage ?? 1) < ($orderTotalPages ?? 1)): ?>
        <a href="?tab=orders&page=<?= ($orderPage ?? 1) + 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>"
           class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white transition-all text-sm">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="text-center py-12 text-[#9e9e9e]">
        <i class="fa-solid fa-truck text-5xl mb-4"></i>
        <p>سفارشی ثبت نشده است</p>
        <a href="/shop" class="inline-block mt-4 px-6 py-3 bg-rose-600 text-white rounded-xl text-sm font-semibold">فروشگاه</a>
    </div>
<?php endif; ?>
<script>
function cancelOrder(id) {
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