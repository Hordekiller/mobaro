<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">دوره‌های من</h1>
    <p class="text-[#9e9e9e] text-sm">دوره‌های آموزشی که ثبت‌نام کرده‌اید</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (!empty($enrollments)): ?>
        <?php foreach ($enrollments as $enrollment): ?>
        <div class="bg-white rounded-[18px] overflow-hidden shadow-[0_4px_20px_rgba(183,110,121,0.06)] hover:-translate-y-1 hover:shadow-lg transition-all">
            <div class="relative">
                <img src="/assets/images/<?= e($enrollment['image']) ?>"
                     class="w-full h-40 object-cover"
                     onerror="this.src='https://picsum.photos/seed/c<?= e($enrollment['course_id']) ?>/400/200'">
                <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold text-white <?= $enrollment['type'] === 'online' ? 'bg-purple-500/90' : 'bg-rose-500/90' ?>">
                    <?= $enrollment['type'] === 'online' ? 'آنلاین' : 'حضوری' ?>
                </span>
            </div>
            <div class="p-4">
                <h4 class="font-bold text-lg"><?= e($enrollment['title']) ?></h4>
                <p class="text-[#9e9e9e] text-sm">مدرس: <?= e($enrollment['teacher']) ?></p>
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-[#9e9e9e] mb-1.5">
                        <span>پیشرفت</span>
                        <span><?= e($enrollment['progress']) ?>%</span>
                    </div>
                    <div class="h-2 bg-[#FDF6F0] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-l from-[#B76E79] to-[#D4AF37] rounded-full transition-all duration-700" style="width: <?= e($enrollment['progress']) ?>%"></div>
                    </div>
                </div>
                <div class="text-xs text-[#9e9e9e] mt-3">
                    <i class="fa-regular fa-clock text-[#B76E79] ml-1"></i><?= e($enrollment['duration']) ?>
                </div>
                <button class="w-full mt-4 py-3 bg-[#B76E79] text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ادامه دوره</button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12 text-[#9e9e9e]">
            <i class="fa-solid fa-graduation-cap text-5xl mb-4"></i>
            <p>دوره‌ای ثبت‌نام نکرده‌اید</p>
        </div>
    <?php endif; ?>
</div>
