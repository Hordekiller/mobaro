<?php $title = 'فروشگاه | موبارو'; ?>
<div class="min-h-screen bg-[#FDF6F0] pt-24">
    <div class="max-w-screen-2xl mx-auto px-8 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-zinc-800">فروشگاه محصولات</h1>
            <p class="text-zinc-500 mt-3">محصولات حرفه‌ای مراقبت از مو و زیبایی</p>
        </div>

        <div class="flex flex-wrap gap-3 justify-center mb-10">
            <a href="/shop" class="px-6 py-2.5 rounded-full text-sm font-semibold transition-all <?= $category === 'all' ? 'bg-[#B76E79] text-white shadow-md' : 'bg-white text-zinc-600 hover:bg-[#FDF6F0] border border-zinc-200' ?>">همه</a>
            <?php foreach ($categories as $cat): ?>
            <a href="/shop?category=<?= urlencode($cat) ?>" class="px-6 py-2.5 rounded-full text-sm font-semibold transition-all <?= $category === $cat ? 'bg-[#B76E79] text-white shadow-md' : 'bg-white text-zinc-600 hover:bg-[#FDF6F0] border border-zinc-200' ?>"><?= e($cat) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 text-zinc-400">
                <i class="fa-solid fa-box-open text-6xl mb-4"></i>
                <p>محصولی یافت نشد</p>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(183,110,121,0.06)] hover:-translate-y-1 hover:shadow-lg transition-all">
                <div class="relative">
                    <img src="/assets/images/<?= e($product['image'] ?? '') ?>" class="w-full h-52 object-cover" onerror="this.src='https://picsum.photos/seed/p<?= $product['id'] ?>/400/400'">
                    <span class="absolute top-3 right-3 bg-white/90 text-xs font-semibold px-3 py-1 rounded-full"><?= e($product['category'] ?? '') ?></span>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-zinc-800"><?= e($product['name']) ?></h3>
                    <p class="text-xs text-zinc-400 mt-1 line-clamp-2"><?= e($product['description'] ?? '') ?></p>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-[#B76E79] font-bold text-lg"><?= priceFormat($product['price']) ?></span>
                        <button onclick="quickAddToCart(<?= $product['id'] ?>)" class="w-10 h-10 bg-[#FDF6F0] rounded-full flex items-center justify-center text-[#B76E79] hover:bg-[#B76E79] hover:text-white transition-all">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
