<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">آدرس‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">مدیریت آدرس‌های تحویل</p>
</div>

<button onclick="showAddressModal()" class="w-full block text-center py-4 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold mb-5 hover:shadow-lg transition-all">
    <i class="fa-solid fa-plus ml-2"></i>افزودن آدرس جدید
</button>

<div id="addresses-list" class="grid grid-cols-1 gap-3.5">
    <?php if (!empty($addresses)): ?>
        <?php foreach ($addresses as $addr): ?>
        <div class="bg-white rounded-xl p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] flex items-start gap-4 <?= $addr['is_default'] ? 'border-2 border-[#D4AF37]' : '' ?>">
            <div class="w-[44px] h-[44px] rounded-xl bg-[#FDF6F0] text-[#B76E79] flex items-center justify-center text-lg flex-shrink-0 mt-1">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h4 class="font-bold text-sm"><?= e($addr['title'] ?: 'آدرس') ?></h4>
                    <?php if ($addr['is_default']): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D4AF37]/10 text-[#D4AF37]">پیش‌فرض</span>
                    <?php endif; ?>
                </div>
                <p class="text-[#9e9e9e] text-sm leading-relaxed"><?= e($addr['address']) ?></p>
                <div class="flex gap-4 text-xs text-[#9e9e9e] mt-2">
                    <?php if ($addr['phone']): ?><span><i class="fa-regular fa-phone text-[#B76E79] ml-1"></i><?= e($addr['phone']) ?></span><?php endif; ?>
                    <?php if (!empty($addr['zip_code'])): ?><span><i class="fa-regular fa-envelope text-[#B76E79] ml-1"></i><?= e($addr['zip_code']) ?></span><?php endif; ?>
                </div>
            </div>
            <button onclick="deleteAddress(<?= $addr['id'] ?>)" class="w-8 h-8 rounded-full bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-400 hover:text-white transition-all flex-shrink-0">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-12 text-[#9e9e9e]">
            <i class="fa-solid fa-map-location-dot text-5xl mb-4"></i>
            <p>آدرسی ثبت نشده است</p>
        </div>
    <?php endif; ?>
</div>

<div id="addressModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden" onclick="closeAddressModal(event)">
    <div class="bg-white rounded-[20px] p-6 w-full max-w-lg mx-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold">آدرس جدید</h3>
            <button onclick="closeAddressModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="/dashboard/address/add" method="POST" class="space-y-4">
            <?= csrf() ?>
            <input type="hidden" name="is_default" value="0">
            <div>
                <label class="block text-sm font-semibold mb-1.5">عنوان آدرس</label>
                <input type="text" name="title" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" placeholder="مثلاً: منزل، محل کار" value="خانه">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">آدرس کامل</label>
                <textarea name="address" rows="3" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" placeholder="استان، شهر، خیابان، کوچه، پلاک" required></textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">شهر</label>
                    <input type="text" name="city" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" placeholder="تهران">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">کد پستی</label>
                    <input type="text" name="zip_code" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">تلفن</label>
                    <input type="text" name="phone" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" placeholder="اختیاری">
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_default" value="1">
                <span class="text-sm font-medium">آدرس پیش‌فرض</span>
            </label>
            <button type="submit" class="w-full py-3.5 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">ذخیره آدرس</button>
        </form>
    </div>
</div>

<script>
function showAddressModal() { document.getElementById('addressModal').classList.remove('hidden'); }
function closeAddressModal(e) { if (!e || e.target === document.getElementById('addressModal')) document.getElementById('addressModal').classList.add('hidden'); }
function deleteAddress(id) {
    if (!confirm('آدرس حذف شود؟')) return;
    fetch('/dashboard/address/delete/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: csrfParam()
    }).then(r => r.json()).then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.reload(), 800);
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
