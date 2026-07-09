<section id="models" class="py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="flex justify-between items-center mb-10">
            <div class="text-4xl font-semibold tracking-tighter">مدل‌های مو و آرایش</div>
            <div class="text-rose-500 flex items-center gap-x-2 cursor-pointer hover:text-rose-600">
                <span class="text-sm font-medium">مشاهده همه</span>
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6" id="models-grid">
            <?php foreach ($hairModels as $model): ?>
            <div class="model-card bg-white border border-zinc-100 rounded-3xl overflow-hidden cursor-pointer">
                <div class="relative">
                    <img src="/assets/images/<?= e($model['image']) ?>"
                         class="w-full h-48 sm:h-56 md:h-72 object-cover hair-model"
                         onerror="this.src='https://picsum.photos/seed/<?= e($model['id']) ?>/400/520'">
                    <div class="absolute top-4 right-4 text-[10px] bg-white/90 backdrop-blur px-4 py-1 rounded-3xl font-medium"><?= e($model['category']) ?></div>
                </div>
                <div class="px-5 py-6 relative">
                    <div class="font-semibold"><?= e($model['title']) ?></div>
                    <div onclick="likeModel(<?= $model['id'] ?>)" class="absolute bottom-6 left-6 text-rose-400 text-xl">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
