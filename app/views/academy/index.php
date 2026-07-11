<?php $title = 'آکادمی | موبارو'; ?>

<section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-rose-600 text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-screen-2xl mx-auto px-8 py-20 md:py-28">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-x-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm mb-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    جدیدترین دوره‌ها منتشر شد
                </div>
                <h1 class="text-4xl md:text-6xl font-bold leading-tight tracking-tight">
                    حرفه‌ای شدن در<br>
                    <span class="text-amber-300">آرایش و زیبایی</span>
                </h1>
                <p class="mt-6 text-lg text-white/80 leading-relaxed max-w-lg">
                    بیش از <?= number_format($totalStudents) ?> دانشجو در دوره‌های آموزشی ما شرکت کرده‌اند. از مبتدی تا حرفه‌ای، مسیر یادگیری خود را پیدا کنید.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="#courses-section" class="bg-white text-indigo-700 px-8 py-3.5 rounded-2xl font-semibold hover:shadow-lg hover:shadow-white/25 transition-all active:scale-95">
                        مشاهده دوره‌ها
                    </a>
                    <a href="#free-section" class="border-2 border-white/40 px-8 py-3.5 rounded-2xl font-semibold hover:bg-white/10 transition-all">
                        دوره‌های رایگان
                    </a>
                </div>
                <div class="mt-8 flex items-center gap-4">
                    <div class="flex -space-x-3 space-x-reverse">
                        <div class="w-10 h-10 rounded-full bg-amber-400 border-2 border-indigo-600 flex items-center justify-center text-xs font-bold text-indigo-800">س</div>
                        <div class="w-10 h-10 rounded-full bg-rose-400 border-2 border-indigo-600 flex items-center justify-center text-xs font-bold text-indigo-800">ن</div>
                        <div class="w-10 h-10 rounded-full bg-emerald-400 border-2 border-indigo-600 flex items-center justify-center text-xs font-bold text-indigo-800">م</div>
                    </div>
                    <div class="text-sm text-white/70">
                        <span class="font-bold text-white"><?= number_format($totalStudents) ?>+</span> دانشجو فعال
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="relative bg-white/10 backdrop-blur-sm rounded-3xl p-6 border border-white/20">
                    <img src="/assets/images/cache/600x340_1015.svg" alt="دوره ویژه" class="w-full h-56 object-cover rounded-2xl">
                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-white/60">دوره ویژه هفته</div>
                            <div class="font-semibold text-lg mt-1">تکنیک‌های حرفه‌ای رنگ مو</div>
                        </div>
                        <div class="text-amber-300 text-2xl font-bold">۴.۸</div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm text-white/60">
                        <i class="fa-solid fa-clock"></i>
                        <span>۱۲ ساعت آموزش</span>
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-user"></i>
                        <span>۳۴۲ دانشجو</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-screen-2xl mx-auto px-8 py-6">
    <div id="courses-section" class="flex flex-wrap items-center gap-3">
        <button onclick="filterByCategory('all')" class="category-chip px-5 py-2.5 rounded-full text-sm font-medium border transition-all <?= $category === 'all' ? 'bg-rose-600 text-white border-rose-600 scale-105' : 'bg-white text-zinc-600 border-zinc-200 hover:border-rose-300' ?>">
            <i class="fa-solid fa-layer-group ml-1.5 text-xs"></i>همه
        </button>
        <?php foreach ($categories as $cat) : ?>
        <button onclick="filterByCategory('<?= e($cat['category']) ?>')" class="category-chip px-5 py-2.5 rounded-full text-sm font-medium border transition-all <?= $category === $cat['category'] ? 'bg-rose-600 text-white border-rose-600 scale-105' : 'bg-white text-zinc-600 border-zinc-200 hover:border-rose-300' ?>">
            <?= e($cat['category']) ?>
            <span class="mr-1 text-xs opacity-60">(<?= $cat['cnt'] ?>)</span>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="max-w-screen-2xl mx-auto px-8 pb-8">
    <div class="flex items-center gap-8 border-b border-zinc-200 mb-8">
        <button onclick="switchTab('newest')" class="tab-btn pb-4 text-sm font-medium border-b-2 transition-all <?= $tab === 'newest' ? 'border-rose-600 text-rose-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' ?>">
            <i class="fa-solid fa-fire-flame-curved ml-1.5"></i>جدیدترین
        </button>
        <button onclick="switchTab('popular')" class="tab-btn pb-4 text-sm font-medium border-b-2 transition-all <?= $tab === 'popular' ? 'border-rose-600 text-rose-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' ?>">
            <i class="fa-solid fa-graduation-cap ml-1.5"></i>محبوب‌ترین
        </button>
        <button onclick="switchTab('free')" class="tab-btn pb-4 text-sm font-medium border-b-2 transition-all <?= $tab === 'free' ? 'border-rose-600 text-rose-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' ?>">
            <i class="fa-solid fa-leaf ml-1.5"></i>رایگان
        </button>
    </div>

    <?php if (empty($courses)) : ?>
    <div class="text-center py-20 text-zinc-400">
        <i class="fa-solid fa-graduation-cap text-6xl mb-4 opacity-30"></i>
        <p class="text-lg">هنوز دوره‌ای اضافه نشده است</p>
    </div>
    <?php else : ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($courses as $course) : ?>
        <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>" class="course-card bg-white border border-transparent hover:border-rose-200 rounded-3xl overflow-hidden group">
            <div class="relative">
                <img src="/assets/images/<?= e($course['image']) ?>"
                     class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500"
                     onerror="this.src='/media/600/340/<?= e($course['id']) ?>'">
                <?php if ($course['is_free']) : ?>
                <div class="absolute top-4 left-4 bg-emerald-500 text-white text-[11px] font-semibold px-3 py-1 rounded-full">رایگان</div>
                <?php endif; ?>
                <?php if (!$course['is_free'] && ($course['old_price'] ?? 0) > $course['price']) : ?>
                <div class="absolute top-4 left-4 bg-rose-500 text-white text-[11px] font-semibold px-3 py-1 rounded-full">
                    <?= round((1 - $course['price'] / ($course['old_price'] ?? 1)) * 100) ?>% تخفیف
                </div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <h3 class="font-semibold leading-tight text-base line-clamp-2 h-10 group-hover:text-rose-600 transition-colors"><?= e($course['title']) ?></h3>
                <div class="flex items-center gap-x-2 text-xs text-zinc-400 mt-3">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    <span><?= e($course['teacher']) ?></span>
                </div>
                <div class="flex justify-between items-center mt-5">
                    <div class="text-xs flex items-center gap-x-px text-amber-400">
                        <?= str_repeat('★', (int) round($course['rating'])) ?><?= str_repeat('☆', 5 - (int) round($course['rating'])) ?>
                        <span class="text-zinc-400 mr-2"><?= number_format($course['rating'], 1) ?></span>
                    </div>
                    <div>
                        <?php if ($course['is_free']) : ?>
                        <span class="text-emerald-600 text-sm font-semibold">رایگان</span>
                        <?php else : ?>
                        <div class="text-right">
                            <?php if (($course['old_price'] ?? 0) > $course['price']) : ?>
                            <div class="text-[11px] text-zinc-400 line-through"><?= number_format($course['old_price'] ?? 0) ?></div>
                            <?php endif; ?>
                            <div class="font-semibold text-sm"><?= number_format($course['price']) ?> <span class="text-xs text-zinc-400">تومان</span></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-3 text-[11px] text-zinc-400">
                    <span><i class="fa-solid fa-clock ml-1"></i><?= e($course['duration']) ?></span>
                    <span><i class="fa-solid fa-signal ml-1"></i><?= e($course['level']) ?></span>
                    <span><i class="fa-solid fa-user-group ml-1"></i><?= number_format($course['students']) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tab) {
    window.location.href = '/academy?tab=' + tab + '&category=<?= e($category) ?>';
}
function filterByCategory(cat) {
    window.location.href = '/academy?tab=<?= e($tab) ?>&category=' + encodeURIComponent(cat);
}
</script>
