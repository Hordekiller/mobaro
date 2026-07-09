<div class="min-h-screen bg-[#FDF6F0] flex" dir="rtl">
    <aside class="w-72 bg-white shadow-[0_0_40px_rgba(183,110,121,0.1)] min-h-screen flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-[#efe5dc]">
            <h1 class="text-xl font-extrabold text-[#B76E79]"><i class="fa-solid fa-crown ml-2"></i>مدیریت موبارو</h1>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php
            $sections = [
                'dashboard' => ['fa-gauge-high', 'داشبورد'],
                'services' => ['fa-scissors', 'خدمات'],
                'appointments' => ['fa-calendar-check', 'نوبت‌ها'],
                'artists' => ['fa-user-tie', 'آرایشگران'],
                'products' => ['fa-box', 'محصولات'],
                'orders' => ['fa-truck', 'سفارش‌ها'],
                'users' => ['fa-users', 'کاربران'],
                'courses' => ['fa-graduation-cap', 'دوره‌ها'],
                'testimonials' => ['fa-comment', 'نظرات'],
                'hair-models' => ['fa-image', 'مدل مو'],
                'tutorials' => ['fa-video', 'آموزش‌ها'],
                'settings' => ['fa-gear', 'تنظیمات'],
            ];
            foreach ($sections as $key => $sec):
                $active = $section === $key ? 'bg-[#B76E79] text-white shadow-lg shadow-[#B76E79]/30' : 'text-gray-600 hover:bg-[#FDF6F0] hover:text-[#B76E79]';
            ?>
            <a href="/admin?section=<?= $key ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all <?= $active ?>">
                <i class="fa-solid <?= $sec[0] ?> w-5 text-center"></i>
                <span><?= $sec[1] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-4 border-t border-[#efe5dc]">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-gray-600 hover:bg-[#FDF6F0] hover:text-[#B76E79] transition-all">
                <i class="fa-solid fa-arrow-right w-5 text-center"></i>
                <span>بازگشت به سایت</span>
            </a>
            <a href="/logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-50 transition-all">
                <i class="fa-solid fa-sign-out w-5 text-center"></i>
                <span>خروج</span>
            </a>
        </div>
    </aside>

    <main class="flex-1 p-6 overflow-y-auto">
        <?php if ($section === 'dashboard'): ?>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold">داشبورد مدیریت</h2>
                <p class="text-[#9e9e9e] text-sm">خلاصه وضعیت</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#ec9ba4] to-[#B76E79]"><i class="fa-solid fa-users"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['users'] ?></h3><p class="text-[#9e9e9e] text-sm">کاربران</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#e8c86a] to-[#D4AF37]"><i class="fa-solid fa-calendar-check"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['appointments'] ?></h3><p class="text-[#9e9e9e] text-sm">نوبت‌ها</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#b39ddb] to-[#7e57c2]"><i class="fa-solid fa-box"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= $stats['orders'] ?></h3><p class="text-[#9e9e9e] text-sm">سفارش‌ها</p></div>
                </div>
                <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#80cbc4] to-[#26a69a]"><i class="fa-solid fa-coins"></i></div>
                    <div><h3 class="text-2xl font-extrabold"><?= priceFormat($stats['revenue']) ?></h3><p class="text-[#9e9e9e] text-sm">درآمد کل</p></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <h3 class="font-bold text-lg mb-4">آخرین نوبت‌ها</h3>
                    <?php if (!empty($recentAppointments)): ?>
                        <?php foreach ($recentAppointments as $a): ?>
                        <div class="flex items-center gap-3 py-2.5 border-b border-[#efe5dc] last:border-0">
                            <div class="w-9 h-9 rounded-full bg-[#FDF6F0] text-[#B76E79] flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-user"></i></div>
                            <div class="flex-1"><span class="font-semibold text-sm"><?= e($a['user_name']) ?></span><span class="text-[#9e9e9e] text-xs mr-2"><?= e($a['service_title']) ?></span></div>
                            <span class="text-xs text-[#9e9e9e]"><?= e($a['appointment_date']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?><p class="text-[#9e9e9e] text-sm text-center py-6">نوبتی ثبت نشده</p>
                    <?php endif; ?>
                </div>
                <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                    <h3 class="font-bold text-lg mb-4">آخرین سفارش‌ها</h3>
                    <?php if (!empty($recentOrders)): ?>
                        <?php foreach ($recentOrders as $o): ?>
                        <div class="flex items-center gap-3 py-2.5 border-b border-[#efe5dc] last:border-0">
                            <div class="w-9 h-9 rounded-full bg-[#FDF6F0] text-[#D4AF37] flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-bag-shopping"></i></div>
                            <div class="flex-1"><span class="font-semibold text-sm"><?= e($o['user_name']) ?></span><span class="text-[#9e9e9e] text-xs mr-2"><?= priceFormat($o['total']) ?></span></div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $o['status'] === 'delivered' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' ?>"><?= e($o['status']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?><p class="text-[#9e9e9e] text-sm text-center py-6">سفارشی ثبت نشده</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($section === 'settings'): ?>
            <?php $table = 'settings'; ?>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold">تنظیمات سایت</h2>
            </div>
            <form action="/admin/settings/update" method="POST" class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
                <?= csrf() ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($settings as $key => $value): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5"><?= e($key) ?></label>
                        <input type="text" name="setting_<?= e($key) ?>" value="<?= e($value) ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all">
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="mt-5 px-8 py-3 bg-[#B76E79] text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ذخیره تنظیمات</button>
            </form>

        <?php else: ?>
            <?php $table = $section; if (in_array($section, ['hair-models'])) $table = 'hair_models'; ?>
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-extrabold">مدیریت <?= $sections[$section][1] ?></h2>
                </div>
                <button onclick="showAddModal()" class="px-5 py-2.5 bg-[#B76E79] text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">
                    <i class="fa-solid fa-plus ml-1"></i>افزودن جدید
                </button>
            </div>

            <div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(183,110,121,0.06)] overflow-x-auto">
                <table class="w-full border-collapse admin-table">
                    <thead>
                        <tr class="bg-[#FDF6F0]">
                            <?php foreach ($columns as $col): ?>
                            <th class="text-right py-3.5 px-4 text-[#9e9e9e] font-semibold text-sm whitespace-nowrap"><?= $col['label'] ?></th>
                            <?php endforeach; ?>
                            <th class="text-center py-3.5 px-4 text-[#9e9e9e] font-semibold text-sm">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                            <tr class="border-b border-[#efe5dc] hover:bg-[#FDF6F0]/50 transition-all">
                                <?php foreach ($columns as $col):
                                    $val = $item[$col['key']] ?? '';
                                    if ($col['type'] === 'image'): ?>
                                        <td class="py-3 px-4"><img src="/assets/images/<?= e($val) ?>" class="w-12 h-12 rounded-lg object-cover" onerror="this.style.display='none'"></td>
                                    <?php elseif ($col['type'] === 'price'): ?>
                                        <td class="py-3 px-4 font-bold"><?= priceFormat($val) ?></td>
                                    <?php elseif ($col['type'] === 'status'): ?>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $val === 'active' || $val === 'confirmed' || $val === 'delivered' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' ?>"><?= e($val) ?></span></td>
                                    <?php elseif ($col['type'] === 'boolean'): ?>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $val ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-500' ?>"><?= $val ? 'بله' : 'خیر' ?></span></td>
                                    <?php elseif ($col['type'] === 'textarea'): ?>
                                        <td class="py-3 px-4 text-sm text-[#9e9e9e] max-w-xs truncate"><?= e(strip_tags($val)) ?></td>
                                    <?php else: ?>
                                        <td class="py-3 px-4 text-sm"><?= e($val) ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex gap-1.5 justify-center">
                                        <button onclick="showEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)" class="px-3 py-1.5 bg-[#FDF6F0] text-[#B76E79] rounded-lg text-xs font-semibold hover:bg-[#B76E79] hover:text-white transition-all">ویرایش</button>
                                        <form action="/admin/<?= e($section) ?>/delete/<?= $item['id'] ?>" method="POST" class="inline" onsubmit="return confirm('آیتم حذف شود؟')">
                                            <?= csrf() ?>
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-semibold hover:bg-red-500 hover:text-white transition-all">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="<?= count($columns) + 1 ?>" class="text-center py-10 text-[#9e9e9e]">آیتمی یافت نشد</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="itemModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden" onclick="closeItemModal(event)">
                <div class="bg-white rounded-[20px] p-6 w-full max-w-2xl mx-4 shadow-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-xl font-bold" id="modalTitle">افزودن جدید</h3>
                        <button onclick="closeItemModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all text-sm"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form action="/admin/<?= e($section) ?>/save" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?= csrf() ?>
                        <input type="hidden" name="id" id="item-id" value="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="modal-fields">
                            <?php foreach ($columns as $col):
                                if ($col['key'] === 'id') continue;
                            ?>
                            <div class="<?= in_array($col['type'], ['textarea', 'image']) ? 'md:col-span-2' : '' ?>">
                                <label class="block text-sm font-semibold mb-1.5"><?= $col['label'] ?></label>
                                <?php if ($col['type'] === 'textarea'): ?>
                                    <textarea name="<?= $col['key'] ?>" rows="3" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" <?= $col['required'] ? 'required' : '' ?>></textarea>
                                <?php elseif ($col['type'] === 'image'): ?>
                                    <input type="file" name="<?= $col['key'] ?>" accept="image/*" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#B76E79] file:text-white hover:file:bg-[#9c5761]">
                                <?php elseif ($col['type'] === 'select'): ?>
                                    <select name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" <?= $col['required'] ? 'required' : '' ?>>
                                        <option value="">انتخاب کنید</option>
                                        <?php foreach (($col['options'] ?? []) as $opt): ?>
                                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($col['type'] === 'boolean'): ?>
                                    <select name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all">
                                        <option value="1">بله</option>
                                        <option value="0">خیر</option>
                                    </select>
                                <?php elseif ($col['type'] === 'price'): ?>
                                    <input type="number" name="<?= $col['key'] ?>" step="1000" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" <?= $col['required'] ? 'required' : '' ?>>
                                <?php else: ?>
                                    <input type="<?= $col['type'] === 'password' ? 'password' : 'text' ?>" name="<?= $col['key'] ?>" class="form-input w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" <?= $col['required'] ? 'required' : '' ?>>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">ذخیره</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'افزودن جدید';
    document.getElementById('item-id').value = '';
    document.querySelectorAll('#modal-fields .form-input').forEach(el => el.value = '');
    document.getElementById('itemModal').classList.remove('hidden');
}
function showEditModal(item) {
    document.getElementById('modalTitle').textContent = 'ویرایش';
    document.getElementById('item-id').value = item.id || '';
    document.querySelectorAll('#modal-fields .form-input').forEach(el => {
        if (el.type === 'file') el.value = '';
        else if (el.type === 'select-one') el.value = item[el.name] || '';
        else el.value = item[el.name] || '';
    });
    document.getElementById('itemModal').classList.remove('hidden');
}
function closeItemModal(e) {
    if (!e || e.target === document.getElementById('itemModal'))
        document.getElementById('itemModal').classList.add('hidden');
}
</script>
