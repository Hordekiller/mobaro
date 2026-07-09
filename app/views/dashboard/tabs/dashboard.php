<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">خوش آمدید، <?= e($user['name']) ?> 👋</h1>
    <p class="text-[#9e9e9e] text-sm">به پنل کاربری موبارو خوش آمدید</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] cursor-pointer hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(183,110,121,0.15)] transition-all">
        <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#ec9ba4] to-[#B76E79]">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <h3 class="text-2xl font-extrabold leading-none mb-1"><?= $stats['upcoming_appointments'] ?></h3>
            <p class="text-[#9e9e9e] text-sm">نوبت آینده</p>
        </div>
    </div>
    <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] cursor-pointer hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(183,110,121,0.15)] transition-all">
        <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#e8c86a] to-[#D4AF37]">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <h3 class="text-2xl font-extrabold leading-none mb-1"><?= $stats['active_courses'] ?></h3>
            <p class="text-[#9e9e9e] text-sm">دوره فعال</p>
        </div>
    </div>
    <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] cursor-pointer hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(183,110,121,0.15)] transition-all">
        <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#b39ddb] to-[#7e57c2]">
            <i class="fa-solid fa-truck"></i>
        </div>
        <div>
            <h3 class="text-2xl font-extrabold leading-none mb-1"><?= $stats['active_orders'] ?></h3>
            <p class="text-[#9e9e9e] text-sm">سفارش در جریان</p>
        </div>
    </div>
    <div class="bg-white rounded-[18px] p-5 flex items-center gap-3.5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] cursor-pointer hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(183,110,121,0.15)] transition-all">
        <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center text-white text-xl flex-shrink-0 bg-gradient-to-br from-[#80cbc4] to-[#26a69a]">
            <i class="fa-solid fa-star"></i>
        </div>
        <div>
            <h3 class="text-2xl font-extrabold leading-none mb-1"><?= e(number_format($user['points'])) ?></h3>
            <p class="text-[#9e9e9e] text-sm">امتیاز شما</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-5 mb-5">
    <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">نوبت بعدی</h3>
            <span class="text-sm text-[#B76E79] font-medium">مشاهده همه</span>
        </div>
        <?php if ($nextAppointment): ?>
        <div class="flex items-center gap-4 py-3">
            <div class="w-[60px] h-[60px] rounded-full bg-gradient-to-br from-[#ec9ba4] to-[#B76E79] flex items-center justify-center text-white text-xl flex-shrink-0">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="flex-1">
                <div class="font-bold"><?= e($nextAppointment['service_title']) ?></div>
                <div class="text-[#9e9e9e] text-sm">با <?= e($nextAppointment['artist_name'] ?? '') ?></div>
                <div class="flex gap-3 text-sm text-[#B76E79] font-semibold mt-1">
                    <span><i class="fa-regular fa-calendar"></i> <?= jdate('Y/m/d', strtotime($nextAppointment['appointment_date'])) ?></span>
                    <span><i class="fa-regular fa-clock"></i> <?= e(substr($nextAppointment['appointment_time'], 0, 5)) ?></span>
                </div>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button class="px-4 py-2 bg-[#B76E79] text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">تغییر نوبت</button>
            <button class="px-4 py-2 bg-red-50 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-500 hover:text-white transition-all">لغو نوبت</button>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-[#9e9e9e]">
            <i class="fa-solid fa-calendar-xmark text-4xl mb-3"></i>
            <p>نوبتی ثبت نشده است</p>
            <a href="/#booking" class="inline-block mt-4 px-6 py-3 bg-rose-600 text-white rounded-xl text-sm font-semibold">رزرو نوبت</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-gradient-to-br from-[#B76E79] to-[#D4AF37] rounded-[18px] p-6 text-white relative overflow-hidden">
        <div class="absolute -top-8 -left-8 w-[120px] h-[120px] bg-white/10 rounded-full"></div>
        <h3 class="text-xl font-bold mb-2 relative">تخفیف ویژه 🎉</h3>
        <p class="text-sm opacity-90 mb-4 relative">اولین نوبت شما با ۲۰٪ تخفیف</p>
        <div class="inline-block bg-white/20 px-4 py-2 rounded-xl font-bold tracking-wider backdrop-blur relative">MOBARO20</div>
    </div>
</div>

<div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)]">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg">آخرین فعالیت‌ها</h3>
    </div>
    <?php if (!empty($recentActivities)): ?>
        <?php foreach ($recentActivities as $activity): ?>
        <div class="flex gap-3 py-3 border-b border-[#efe5dc] last:border-b-0">
            <div class="w-[38px] h-[38px] rounded-xl bg-[#FDF6F0] text-[#B76E79] flex items-center justify-center flex-shrink-0">
                <i class="fa-solid <?= $activity['type'] === 'appointment' ? 'fa-calendar-check' : ($activity['type'] === 'order' ? 'fa-truck' : 'fa-coins') ?>"></i>
            </div>
            <div class="flex-1">
                <div class="font-medium text-sm"><?= e($activity['title']) ?></div>
                <div class="text-[#9e9e9e] text-xs"><?= timeAgo($activity['created_at']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-[#9e9e9e] text-sm text-center py-6">هنوز فعالیتی ثبت نشده است</p>
    <?php endif; ?>
</div>
