<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">علاقه‌مندی‌ها</h1>
    <p class="text-[#9e9e9e] text-sm">محصولات نشان شده</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <?php if (!empty($wishlist)): ?>
        <?php foreach ($wishlist as $item): ?>
        <div class="bg-white rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(183,110,121,0.06)] hover:-translate-y-1 hover:shadow-lg transition-all relative">
            <img src="/assets/images/<?= e($item['image']) ?>"
                 class="w-full h-44 object-cover"
                 onerror="this.src='https://picsum.photos/seed/w<?= e($item['product_id']) ?>/280/280'">
            <button onclick="removeWishlist(<?= $item['product_id'] ?>)" class="absolute top-2.5 left-2.5 w-8 h-8 bg-white rounded-full flex items-center justify-center text-red-400 shadow hover:bg-red-400 hover:text-white transition-all">
                <i class="fa-solid fa-heart"></i>
            </button>
            <div class="p-3.5">
                <h4 class="font-semibold text-sm"><?= e($item['name']) ?></h4>
                <div class="text-[#B76E79] font-bold mt-2"><?= priceFormat($item['price']) ?></div>
                <button onclick="quickAddToCart(<?= $item['product_id'] ?>, '<?= e($item['name']) ?>', <?= $item['price'] ?>, '/assets/images/<?= e($item['image']) ?>', '<?= e($item['category']) ?>')"
                        class="w-full mt-3 py-2.5 bg-[#FDF6F0] text-[#B76E79] rounded-xl font-semibold text-sm hover:bg-[#B76E79] hover:text-white transition-all">
                    افزودن به سبد
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12 text-[#9e9e9e]">
            <i class="fa-solid fa-heart text-5xl mb-4"></i>
            <p>محصولی در لیست علاقه‌مندی‌ها نیست</p>
        </div>
    <?php endif; ?>
</div>

<script>
function removeWishlist(productId) {
    fetch('/dashboard/wishlist/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId
    }).then(r => r.json()).then(d => {
        showToast(d.message, 'success');
        setTimeout(() => location.reload(), 1000);
    });
}
</script>
