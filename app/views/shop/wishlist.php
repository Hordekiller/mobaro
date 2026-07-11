<?php $title = 'علاقه‌مندی‌ها | موبارو'; ?>

<div class="max-w-screen-2xl mx-auto px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold">علاقه‌مندی‌ها</h1>
        <p class="text-zinc-500 text-sm mt-2">محصولاتی که به آنها علاقه‌مند شده‌اید</p>
    </div>

    <?php if (!empty($products)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
        <div class="bg-white rounded-[18px] overflow-hidden shadow-[0_4px_20px_rgba(183,110,121,0.06)] hover:-translate-y-1 hover:shadow-lg transition-all group">
            <div class="relative">
                <a href="/product/<?= $product['id'] ?>">
                    <img src="/assets/images/<?= e($product['image']) ?>" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='/media/400/300/<?= $product['id'] ?>'">
                </a>
                <button onclick="removeWishlist(<?= $product['id'] ?>)" class="absolute top-3 left-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                    <i class="fa-solid fa-heart text-sm"></i>
                </button>
                <?php if ($product['is_sale']): ?>
                <span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">حراج</span>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <a href="/product/<?= $product['id'] ?>" class="font-semibold text-sm line-clamp-1 group-hover:text-rose-600 transition-colors"><?= e($product['name']) ?></a>
                <div class="text-xs text-zinc-400 mt-1"><?= e($product['category']) ?></div>
                <div class="flex items-center justify-between mt-4">
                    <div>
                        <?php if ($product['is_sale'] && $product['old_price'] > $product['price']): ?>
                        <span class="text-zinc-400 text-xs line-through"><?= number_format($product['old_price']) ?></span>
                        <?php endif; ?>
                        <span class="font-bold text-sm"><?= number_format($product['price']) ?> <span class="text-xs text-zinc-400">تومان</span></span>
                    </div>
                    <button onclick="quickAddToCart(<?= $product['id'] ?>)" class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all">
                        <i class="fa-solid fa-cart-plus text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-20">
        <i class="fa-regular fa-heart text-6xl text-zinc-300 mb-4"></i>
        <p class="text-zinc-500 text-lg">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید</p>
        <a href="/shop" class="inline-block mt-6 px-8 py-3 bg-rose-600 text-white rounded-2xl font-semibold hover:bg-rose-700 transition-all">مشاهده فروشگاه</a>
    </div>
    <?php endif; ?>
</div>

<script>
function removeWishlist(productId) {
    fetch('/shop/wishlist/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId + '&' + csrfParam()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('از علاقه‌مندی‌ها حذف شد');
            setTimeout(function() { location.reload(); }, 800);
        }
    })
    .catch(function() { showToast('خطا در حذف', 'error'); });
}
</script>
