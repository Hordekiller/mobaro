<?php $title = 'مدل‌های مو | ' . ($settings['brand_name'] ?? 'موبارو'); ?>

<section class="relative overflow-hidden bg-gradient-to-br from-rose-600 via-pink-600 to-fuchsia-600 text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-screen-2xl mx-auto px-8 py-20 md:py-28">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-x-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm mb-6">
                <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                مدل‌های جدید
            </div>
            <h1 class="text-4xl md:text-6xl font-bold leading-tight tracking-tight">
                مدل‌های مو و <span class="text-amber-300">آرایش</span>
            </h1>
            <p class="mt-6 text-lg text-white/80 leading-relaxed">
                بیش از <?= number_format($totalModels) ?> مدل زیبا برای انتخاب بهترین سبک مو و آرایش
            </p>
        </div>
    </div>
</section>

<div class="max-w-screen-2xl mx-auto px-8 py-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="/models" class="px-5 py-2.5 rounded-full text-sm font-medium border transition-all <?= $category === 'all' ? 'bg-rose-600 text-white border-rose-600 scale-105' : 'bg-white text-zinc-600 border-zinc-200 hover:border-rose-300' ?>">
            <i class="fa-solid fa-layer-group ml-1.5 text-xs"></i>همه
        </a>
        <?php foreach ($categories as $cat) : ?>
        <a href="/models?category=<?= urlencode($cat['category']) ?>" class="px-5 py-2.5 rounded-full text-sm font-medium border transition-all <?= $category === $cat['category'] ? 'bg-rose-600 text-white border-rose-600 scale-105' : 'bg-white text-zinc-600 border-zinc-200 hover:border-rose-300' ?>">
            <?= e($cat['category']) ?>
            <span class="mr-1 text-xs opacity-60">(<?= $cat['cnt'] ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="max-w-screen-2xl mx-auto px-8 pb-16">
    <?php if (empty($models)) : ?>
    <div class="text-center py-20 text-zinc-400">
        <i class="fa-solid fa-image text-6xl mb-4 opacity-30"></i>
        <p class="text-lg">هنوز مدلی اضافه نشده است</p>
    </div>
    <?php else : ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php foreach ($models as $model) : ?>
        <div class="model-card bg-white border border-zinc-100 rounded-3xl overflow-hidden cursor-pointer hover:shadow-lg transition-shadow">
            <div class="relative">
                <img src="/assets/images/<?= e($model['image']) ?>"
                     alt="<?= e($model['title']) ?>"
                     class="w-full h-48 sm:h-56 md:h-72 object-cover"
                     data-fallback="/media/400/520/<?= e($model['id']) ?>">
                <div class="absolute top-4 right-4 text-[10px] bg-white/90 backdrop-blur px-4 py-1 rounded-3xl font-medium"><?= e($model['category']) ?></div>
            </div>
            <div class="px-5 py-6 relative">
                <div class="font-semibold"><?= e($model['title']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

        <?php if ($totalPages > 1) : ?>
    <div class="flex justify-center items-center gap-2 mt-10">
            <?php if ($page > 1) : ?>
        <a href="/models?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="w-10 h-10 rounded-lg border border-zinc-200 text-zinc-500 hover:border-rose-400 hover:text-rose-500 transition flex items-center justify-center">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
        <a href="/models?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="w-10 h-10 rounded-lg flex items-center justify-center font-medium transition <?= $i === $page ? 'bg-rose-600 text-white' : 'border border-zinc-200 text-zinc-600 hover:border-rose-400 hover:text-rose-500' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages) : ?>
        <a href="/models?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="w-10 h-10 rounded-lg border border-zinc-200 text-zinc-500 hover:border-rose-400 hover:text-rose-500 transition flex items-center justify-center">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
            <?php endif; ?>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
