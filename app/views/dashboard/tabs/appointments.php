<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">نوبت‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">مدیریت نوبت‌های رزرو شده</p>
</div>

<a href="/#booking" class="w-full block text-center py-4 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold mb-5 hover:shadow-lg transition-all">
    <i class="fa-solid fa-plus ml-2"></i>رزرو نوبت جدید
</a>

<div class="flex gap-2 bg-white p-1.5 rounded-xl shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-5 overflow-x-auto" id="appointment-tabs">
    <button onclick="filterApts(this, 'future')" class="tab-btn active px-5 py-2.5 rounded-lg bg-[#B76E79] text-white font-medium text-sm whitespace-nowrap transition-all">نوبت‌های آینده</button>
    <button onclick="filterApts(this, 'past')" class="tab-btn px-5 py-2.5 rounded-lg bg-transparent text-[#9e9e9e] font-medium text-sm whitespace-nowrap transition-all">نوبت‌های گذشته</button>
    <button onclick="filterApts(this, 'cancelled')" class="tab-btn px-5 py-2.5 rounded-lg bg-transparent text-[#9e9e9e] font-medium text-sm whitespace-nowrap transition-all">لغو شده</button>
</div>

<div id="appointments-list">
    <?php if (!empty($appointments)): ?>
        <?php foreach ($appointments as $apt):
            $statusClass = match($apt['status']) {
                'confirmed' => 'bg-green-50 text-green-700',
                'pending' => 'bg-amber-50 text-amber-700',
                'done' => 'bg-gray-100 text-gray-600',
                'cancelled' => 'bg-red-50 text-red-600',
                default => 'bg-gray-100 text-gray-600',
            };
            $statusLabel = match($apt['status']) {
                'confirmed' => 'تأیید شده',
                'pending' => 'در انتظار',
                'done' => 'انجام شده',
                'cancelled' => 'لغو شده',
                default => $apt['status'],
            };
        ?>
        <div class="bg-white rounded-xl p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-3.5 hover:-translate-y-0.5 hover:shadow-lg transition-all"
             data-status="<?= e($apt['status']) ?>" data-date="<?= e($apt['appointment_date']) ?>">
            <div class="grid grid-cols-[auto_1fr_auto] gap-4 items-center">
                <div class="w-[60px] h-[60px] bg-gradient-to-br from-[#FDF6F0] to-white border-2 border-[#d18d97] rounded-[14px] flex items-center justify-center text-[#B76E79] text-xl flex-shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 mb-1.5 flex-wrap">
                        <h4 class="font-bold"><?= e($apt['service_title']) ?></h4>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </div>
                    <div class="flex gap-4 text-[#9e9e9e] text-sm flex-wrap">
                        <span><i class="fa-regular fa-user text-[#B76E79] ml-1"></i><?= e($apt['artist_name'] ?? 'نامشخص') ?></span>
                        <span><i class="fa-regular fa-calendar text-[#B76E79] ml-1"></i><?= e($apt['appointment_date']) ?></span>
                        <span><i class="fa-regular fa-clock text-[#B76E79] ml-1"></i><?= e($apt['appointment_time']) ?></span>
                    </div>
                    <div class="font-bold text-[#B76E79] mt-1.5"><?= priceFormat($apt['price']) ?></div>
                </div>
                <?php if ($apt['status'] === 'confirmed' || $apt['status'] === 'pending'): ?>
                <div class="flex flex-col gap-1.5">
                    <button class="px-3 py-1.5 bg-[#B76E79] text-white rounded-lg text-xs font-semibold">تغییر</button>
                    <button class="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-semibold">لغو</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-12 text-[#9e9e9e]">
            <i class="fa-solid fa-calendar-xmark text-5xl mb-4"></i>
            <p>نوبتی ثبت نشده است</p>
        </div>
    <?php endif; ?>
</div>

<script>
function filterApts(btn, filter) {
    document.querySelectorAll('#appointment-tabs .tab-btn').forEach(b => {
        b.classList.remove('bg-[#B76E79]', 'text-white');
        b.classList.add('bg-transparent', 'text-[#9e9e9e]');
    });
    btn.classList.add('bg-[#B76E79]', 'text-white');
    btn.classList.remove('bg-transparent', 'text-[#9e9e9e]');

    document.querySelectorAll('#appointments-list > div').forEach(card => {
        const status = card.dataset.status;
        const date = card.dataset.date;
        const today = new Date().toISOString().split('T')[0];
        if (filter === 'future') {
            card.style.display = (status !== 'cancelled' && date >= today) ? '' : 'none';
        } else if (filter === 'past') {
            card.style.display = (status === 'done' || (status !== 'cancelled' && date < today)) ? '' : 'none';
        } else {
            card.style.display = status === 'cancelled' ? '' : 'none';
        }
    });
}
// Initial filter
setTimeout(() => {
    const firstBtn = document.querySelector('#appointment-tabs .tab-btn');
    if (firstBtn) filterApts(firstBtn, 'future');
}, 100);
</script>
