<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">نوبت‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">مدیریت نوبت‌های رزرو شده</p>
</div>

<a href="/#booking" class="w-full block text-center py-4 bg-gradient-to-l from-[#e11d48] to-[#be123c] text-white rounded-xl font-bold mb-5 hover:shadow-lg transition-all">
    <i class="fa-solid fa-plus ml-2"></i>رزرو نوبت جدید
</a>

<div class="flex gap-2 bg-white p-1.5 rounded-xl shadow-[0_4px_20px_rgba(225,29,72,0.06)] mb-5 overflow-x-auto" id="appointment-tabs">
    <button onclick="filterApts(this, 'future')" class="tab-btn active px-5 py-2.5 rounded-lg bg-[#e11d48] text-white font-medium text-sm whitespace-nowrap transition-all">نوبت‌های آینده</button>
    <button onclick="filterApts(this, 'past')" class="tab-btn px-5 py-2.5 rounded-lg bg-transparent text-[#9e9e9e] font-medium text-sm whitespace-nowrap transition-all">نوبت‌های گذشته</button>
    <button onclick="filterApts(this, 'cancelled')" class="tab-btn px-5 py-2.5 rounded-lg bg-transparent text-[#9e9e9e] font-medium text-sm whitespace-nowrap transition-all">لغو شده</button>
</div>

<div id="appointments-list">
    <?php if (!empty($appointments)) : ?>
        <?php foreach ($appointments as $apt) :
            $statusClass = match ($apt['status']) {
                'confirmed' => 'bg-green-50 text-green-700',
                'pending' => 'bg-amber-50 text-amber-700',
                'done' => 'bg-gray-100 text-gray-600',
                'cancelled' => 'bg-red-50 text-red-600',
                default => 'bg-gray-100 text-gray-600',
            };
            $statusLabel = match ($apt['status']) {
                'confirmed' => 'تأیید شده',
                'pending' => 'در انتظار',
                'done' => 'انجام شده',
                'cancelled' => 'لغو شده',
                default => $apt['status'],
            };
    ?>
        <div class="bg-white rounded-xl p-5 shadow-[0_4px_20px_rgba(225,29,72,0.06)] mb-3.5 hover:-translate-y-0.5 hover:shadow-lg transition-all"
             data-status="<?= e($apt['status']) ?>" data-date="<?= e($apt['appointment_date']) ?>">
            <div class="grid grid-cols-[auto_1fr_auto] gap-4 items-center">
                <div class="w-[60px] h-[60px] bg-gradient-to-br from-[#fff1f2] to-white border-2 border-[#fda4af] rounded-[14px] flex items-center justify-center text-[#e11d48] text-xl flex-shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 mb-1.5 flex-wrap">
                        <h4 class="font-bold"><?= e($apt['service_title']) ?></h4>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </div>
                    <div class="flex gap-4 text-[#9e9e9e] text-sm flex-wrap">
                        <span><i class="fa-regular fa-user text-[#e11d48] ml-1"></i><?= e($apt['artist_name'] ?? 'نامشخص') ?></span>
                        <?php if (!empty($apt['hair_length_title'])) : ?>
                        <span><i class="fa-solid fa-ruler-vertical text-[#e11d48] ml-1"></i><?= e($apt['hair_length_title']) ?></span>
                        <?php endif; ?>
                        <span><i class="fa-regular fa-calendar text-[#e11d48] ml-1"></i><?= jdate('Y/m/d', strtotime($apt['appointment_date'])) ?></span>
                        <span><i class="fa-regular fa-clock text-[#e11d48] ml-1"></i><?= e(substr($apt['appointment_time'], 0, 5)) ?></span>
                    </div>
                    <div class="font-bold text-[#e11d48] mt-1.5"><?= priceFormat($apt['price']) ?></div>
                </div>
                <?php if ($apt['status'] === 'confirmed' || $apt['status'] === 'pending') : ?>
                <div class="flex flex-col gap-1.5">
                    <button onclick="showReschedule(<?= $apt['id'] ?>)" class="px-3 py-1.5 bg-[#e11d48] text-white rounded-lg text-xs font-semibold">تغییر</button>
                    <button onclick="cancelAppointment(<?= $apt['id'] ?>)" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-semibold">لغو</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="text-center py-12 text-[#9e9e9e]">
            <i class="fa-solid fa-calendar-xmark text-5xl mb-4"></i>
            <p>نوبتی ثبت نشده است</p>
        </div>
    <?php endif; ?>
</div>

<div id="rescheduleModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden" onclick="closeRescheduleModal(event)">
    <div class="bg-white rounded-[20px] p-6 w-full max-w-sm mx-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold">تغییر زمان نوبت</h3>
            <button onclick="closeRescheduleModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="space-y-4">
            <input type="hidden" id="reschedule-id">
            <div>
                <label class="block text-sm font-semibold mb-1.5">تاریخ جدید</label>
                <input type="date" id="reschedule-date" min="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#e11d48] focus:ring-0 outline-none transition-all" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">ساعت جدید</label>
                <input type="time" id="reschedule-time" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#e11d48] focus:ring-0 outline-none transition-all" required>
            </div>
            <button onclick="submitReschedule()" class="w-full py-3.5 bg-gradient-to-l from-[#e11d48] to-[#be123c] text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">ذخیره تغییر</button>
        </div>
    </div>
</div>

<script>
function filterApts(btn, filter) {
    document.querySelectorAll('#appointment-tabs .tab-btn').forEach(b => {
        b.classList.remove('bg-[#e11d48]', 'text-white');
        b.classList.add('bg-transparent', 'text-[#9e9e9e]');
    });
    btn.classList.add('bg-[#e11d48]', 'text-white');
    btn.classList.remove('bg-transparent', 'text-[#9e9e9e]');

    document.querySelectorAll('#appointments-list > div').forEach(card => {
        const status = card.dataset.status;
        const date = card.dataset.date;
        const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Tehran' });
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

function cancelAppointment(id) {
    if (!confirm('آیا از لغو نوبت مطمئن هستید؟')) return;
    fetch('/dashboard/appointment/cancel', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'appointment_id=' + id + '&' + csrfParam()
    }).then(r => r.json()).then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

let rescheduleId = null;

function showReschedule(id) {
    rescheduleId = id;
    document.getElementById('reschedule-id').value = id;
    document.getElementById('reschedule-date').value = '';
    document.getElementById('reschedule-time').value = '';
    document.getElementById('rescheduleModal').classList.remove('hidden');
}

function closeRescheduleModal(e) {
    if (!e || e.target === document.getElementById('rescheduleModal'))
        document.getElementById('rescheduleModal').classList.add('hidden');
}

function submitReschedule() {
    const id = document.getElementById('reschedule-id').value;
    const newDate = document.getElementById('reschedule-date').value;
    const newTime = document.getElementById('reschedule-time').value;
    if (!newDate || !newTime) {
        showToast('تاریخ و ساعت را وارد کنید', 'error');
        return;
    }
    fetch('/dashboard/appointment/reschedule', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'appointment_id=' + id + '&new_date=' + encodeURIComponent(newDate) + '&new_time=' + encodeURIComponent(newTime) + '&' + csrfParam()
    }).then(r => r.json()).then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
