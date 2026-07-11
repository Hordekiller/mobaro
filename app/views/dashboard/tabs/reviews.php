<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">نظرات من</h1>
    <p class="text-[#9e9e9e] text-sm">نظراتی که برای محصولات ثبت کرده‌اید</p>
</div>

<?php if (!empty($reviews)): ?>
    <?php foreach ($reviews as $review): ?>
    <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-3.5">
        <div class="flex items-start gap-4">
            <div class="w-[52px] h-[52px] rounded-xl bg-[#FDF6F0] flex items-center justify-center text-[#B76E79] text-lg flex-shrink-0">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                    <div>
                        <a href="/product/<?= $review['product_id'] ?>" class="font-bold hover:text-[#B76E79] transition-colors"><?= e($review['product_name']) ?></a>
                        <span class="text-[#9e9e9e] text-xs mr-2"><?= jdate('Y/m/d', strtotime($review['created_at'])) ?></span>
                    </div>
                    <div class="flex gap-0.5 text-sm" dir="ltr">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star <?= $i <= $review['rating'] ? 'text-[#D4AF37]' : 'text-[#efe5dc]' ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php if (!empty($review['text'])): ?>
                <p class="text-zinc-600 text-sm leading-relaxed"><?= e($review['text']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center py-12 text-[#9e9e9e]">
        <i class="fa-solid fa-star text-5xl mb-4"></i>
        <p>نظری ثبت نکرده‌اید</p>
        <a href="/shop" class="inline-block mt-4 px-6 py-3 bg-[#B76E79] text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">مشاهده محصولات</a>
    </div>
<?php endif; ?>
