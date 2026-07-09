<section id="shop" class="py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="font-semibold text-5xl tracking-tighter">فروشگاه محصولات آرایشی</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-6 gap-y-10" id="shop-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card bg-white rounded-3xl overflow-hidden border border-transparent hover:border-zinc-200">
                <div class="relative">
                    <img src="/assets/images/<?= e($product['image']) ?>"
                         class="w-full aspect-square object-cover"
                         onerror="this.src='https://picsum.photos/seed/p<?= e($product['id']) ?>/280/280'">
                    <button onclick="quickAddToCart(<?= $product['id'] ?>, '<?= e($product['name']) ?>', <?= $product['price'] ?>, '/assets/images/<?= e($product['image']) ?>', '<?= e($product['category']) ?>')"
                            class="absolute top-4 left-4 bg-white h-8 w-8 rounded-2xl flex items-center justify-center shadow text-rose-500 text-lg leading-none pt-px">🛒</button>
                </div>
                <div class="p-5">
                    <div class="text-xs text-zinc-400"><?= e($product['category']) ?></div>
                    <div class="font-medium text-base mt-1 line-clamp-2"><?= e($product['name']) ?></div>
                    <div class="flex justify-between items-baseline mt-6">
                        <div class="font-bold text-rose-500"><?= number_format($product['price']) ?> تومان</div>
                        <button onclick="addToCartFromShop(<?= $product['id'] ?>, '<?= e($product['name']) ?>', <?= $product['price'] ?>, '/assets/images/<?= e($product['image']) ?>', '<?= e($product['category']) ?>')"
                                class="text-xs border border-zinc-300 hover:bg-zinc-50 px-5 py-3 rounded-3xl">اضافه به سبد</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
