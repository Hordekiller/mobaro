<?php
$title = e($product['name']) . ' | موبارو';
$discount = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
    $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
}
$inWishlist = Auth::check()
    ? (bool) Database::fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [Auth::id(), $product['id']])
    : in_array($product['id'], $_SESSION['wishlist'] ?? []);
?>

<div class="min-h-screen bg-zinc-50">
    <!-- Breadcrumb -->
    <div class="bg-gradient-to-r from-rose-50 to-pink-50 py-6">
        <div class="max-w-screen-2xl mx-auto px-8">
            <div class="flex items-center gap-2 text-sm text-zinc-600">
                <a href="/" class="hover:text-rose-500 transition">خانه</a>
                <i class="fa-solid fa-chevron-left text-xs text-rose-400"></i>
                <a href="/shop" class="hover:text-rose-500 transition">فروشگاه</a>
                <i class="fa-solid fa-chevron-left text-xs text-rose-400"></i>
                <span class="text-rose-500 font-medium"><?= e($product['name']) ?></span>
            </div>
        </div>
    </div>

    <div class="max-w-screen-2xl mx-auto px-8 py-8">
        <!-- Product Detail -->
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
            <div class="grid md:grid-cols-2 gap-8 p-6 md:p-10">
                <!-- Image Gallery -->
                <div>
                    <div class="relative rounded-2xl overflow-hidden bg-zinc-50 mb-4 product-image">
                        <img id="mainImage" src="/assets/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-96 md:h-[500px] object-cover" onerror="this.src='/media/600/600/<?= e($product['id']) ?>'">
                        <?php if ($discount > 0) : ?>
                        <span class="absolute top-4 right-4 bg-gradient-to-l from-red-500 to-red-600 text-white text-sm px-4 py-2 rounded-full font-bold shadow-lg"><?= $discount ?>% تخفیف</span>
                        <?php endif; ?>
                        <?php if (!empty($product['is_new'])) : ?>
                        <span class="absolute top-4 left-4 bg-gradient-to-l from-emerald-500 to-emerald-600 text-white text-sm px-4 py-2 rounded-full font-bold shadow-lg">جدید</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-3 overflow-x-auto" id="thumbnails">
                        <img src="/assets/images/<?= e($product['image']) ?>" class="w-20 h-20 rounded-xl object-cover cursor-pointer border-2 border-rose-500 opacity-100 hover:opacity-80 transition-all thumb-img" onclick="changeImage(this)" onerror="this.src='/media/200/200/<?= e($product['id']) ?>'">
                        <?php if (!empty($gallery)) : ?>
                            <?php foreach ($gallery as $gi) : ?>
                        <img src="/assets/images/<?= e($gi['image']) ?>" class="w-20 h-20 rounded-xl object-cover cursor-pointer border-2 border-transparent opacity-70 hover:opacity-100 hover:border-rose-300 transition-all thumb-img" onclick="changeImage(this)" onerror="this.style.display='none'">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($product['video_url'])) : ?>
                    <div class="mt-4 rounded-2xl overflow-hidden bg-zinc-900">
                        <?php if ($product['video_type'] === 'upload' && !empty($productMedia)) : ?>
                        <video controls class="w-full max-h-[400px]" poster="/assets/images/<?= e($product['image']) ?>">
                            <source src="/media/stream/<?= $productMedia['id'] ?>" type="video/mp4">
                            مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند
                        </video>
                        <?php elseif ($product['video_type'] !== 'upload') : ?>
                        <div class="relative w-full" style="padding-bottom:56.25%">
                            <?php if ($product['video_type'] === 'youtube') : ?>
                            <iframe class="absolute inset-0 w-full h-full" src="<?= e($product['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
                            <?php elseif ($product['video_type'] === 'aparat') : ?>
                            <iframe class="absolute inset-0 w-full h-full" src="<?= e($product['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div>
                    <?php if (!empty($product['brand'])) : ?>
                    <span class="inline-block text-xs font-bold text-rose-500 bg-rose-50 px-4 py-1.5 rounded-full mb-4"><?= e($product['brand']) ?></span>
                    <?php endif; ?>

                    <h1 class="text-3xl md:text-4xl font-bold text-zinc-800 mb-4"><?= e($product['name']) ?></h1>

                    <div class="flex items-center gap-3 mb-6">
                        <div class="star-rating text-amber-400 text-lg">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <?php if ($i <= floor($product['rating'] ?? 0)) : ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php elseif ($i - 0.5 <= ($product['rating'] ?? 0)) : ?>
                                    <i class="fa-solid fa-star-half-alt"></i>
                                <?php else : ?>
                                    <i class="fa-regular fa-star text-zinc-300"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="text-zinc-500">(<?= number_format($product['reviews'] ?? 0) ?> نظر)</span>
                    </div>

                    <div class="mb-6">
                        <?php if ($discount > 0) : ?>
                        <span class="text-zinc-400 text-xl line-through ml-3"><?= number_format($product['old_price']) ?> تومان</span>
                        <?php endif; ?>
                        <span class="text-4xl font-bold text-rose-500"><?= number_format($product['price']) ?> تومان</span>
                    </div>

                    <?php $desc = $product['description'] ?? ''; ?>
                    <?php if (!empty($desc)) : ?>
                    <p class="text-zinc-600 leading-relaxed mb-8 whitespace-pre-line"><?= e($desc) ?></p>
                    <?php endif; ?>

                    <!-- Qty + Add to Cart -->
                    <div class="flex items-center gap-4 mb-8">
                        <div class="flex items-center border-2 border-zinc-200 rounded-xl">
                            <button onclick="detailQtyChg(-1)" class="px-5 py-3 text-zinc-500 hover:text-rose-500 text-lg transition">-</button>
                            <span id="qtyDisplay" class="px-5 py-3 border-x-2 border-zinc-200 font-bold text-zinc-800 min-w-[60px] text-center">۱</span>
                            <button onclick="detailQtyChg(1)" class="px-5 py-3 text-zinc-500 hover:text-rose-500 text-lg transition">+</button>
                        </div>
                        <button onclick="addToCartDetail(<?= $product['id'] ?>)" class="flex-1 bg-rose-600 hover:bg-rose-700 transition-all text-white py-4 rounded-xl font-medium text-lg shadow-lg shadow-rose-200">
                            <i class="fa-solid fa-bag-shopping ml-2"></i>افزودن به سبد خرید
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-6 text-zinc-500">
                        <button onclick="toggleWishlistItem(<?= $product['id'] ?>)" class="heart-btn-detail flex items-center gap-2 hover:text-rose-500 transition <?= $inWishlist ? 'active text-rose-500' : '' ?>">
                            <i class="<?= $inWishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                            <span class="text-sm"><?= $inWishlist ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' ?></span>
                        </button>
                        <button class="flex items-center gap-2 hover:text-rose-500 transition" onclick="shareProduct()">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span class="text-sm">اشتراک‌گذاری</span>
                        </button>
                    </div>

                    <!-- Meta -->
                    <div class="mt-8 pt-8 border-t border-zinc-100 space-y-3 text-sm">
                        <?php if (!empty($product['category'])) : ?>
                        <div class="flex items-center gap-2">
                            <span class="text-zinc-400">دسته‌بندی:</span>
                            <a href="/shop?category=<?= urlencode($product['category']) ?>" class="text-rose-500 hover:underline"><?= e($product['category']) ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($product['brand'])) : ?>
                        <div class="flex items-center gap-2">
                            <span class="text-zinc-400">برند:</span>
                            <span class="text-zinc-700 font-medium"><?= e($product['brand']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($product['stock'] ?? 0 > 0) : ?>
                        <div class="flex items-center gap-2">
                            <span class="text-zinc-400">موجودی:</span>
                            <span class="text-emerald-600 font-medium"><?= $product['stock'] ?> عدد</span>
                        </div>
                        <?php else : ?>
                        <div class="flex items-center gap-2">
                            <span class="text-zinc-400">موجودی:</span>
                            <span class="text-red-500 font-medium">ناموجود</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div class="mt-12 bg-white rounded-3xl shadow-lg p-6 md:p-10">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-zinc-800">نظرات کاربران</h2>
                    <p class="text-zinc-400 text-sm mt-1"><?= faNum($reviewCount) ?> نظر • میانگین امتیاز <?= number_format($avgRating, 1) ?></p>
                </div>
                <button onclick="document.getElementById('review-form').scrollIntoView({behavior:'smooth'})" class="px-5 py-2.5 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">
                    <i class="fa-solid fa-plus ml-1"></i>ثبت نظر
                </button>
            </div>

            <div id="reviews-list" class="space-y-4">
                <?php if (!empty($reviews)) : ?>
                    <?php foreach ($reviews as $rev) : ?>
                    <div class="border border-zinc-100 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center text-xs font-bold"><?= e(mb_substr($rev['user_name'], 0, 1)) ?></div>
                                <div>
                                    <div class="font-medium text-sm text-zinc-800"><?= e($rev['user_name']) ?></div>
                                    <div class="text-amber-400 text-xs">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <i class="fa-<?= $i <= $rev['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-zinc-400"><?= jdate('Y/m/d', strtotime($rev['created_at'])) ?></span>
                        </div>
                        <p class="text-sm text-zinc-600 leading-relaxed"><?= e($rev['text']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="text-center py-10 text-zinc-400 text-sm">هنوز نظری ثبت نشده است. اولین نفر باشید!</div>
                <?php endif; ?>
            </div>

            <?php if (Auth::check()) : ?>
            <div id="review-form" class="mt-8 bg-zinc-50 rounded-2xl p-6">
                <h3 class="font-bold text-zinc-800 mb-4">ثبت نظر شما</h3>
                <form onsubmit="submitReview(event, <?= $product['id'] ?>)" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-2">امتیاز شما</label>
                        <div class="flex gap-1 text-2xl text-zinc-300" id="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <i class="fa-regular fa-star cursor-pointer hover:text-amber-400 transition-colors star-select" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-2">متن نظر</label>
                        <textarea name="text" id="review-text" rows="3" class="w-full px-4 py-3 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="نظر خود را بنویسید..." required></textarea>
                    </div>
                    <button type="submit" class="px-8 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ثبت نظر</button>
                </form>
            </div>
            <?php else : ?>
            <div class="mt-8 bg-zinc-50 rounded-2xl p-6 text-center">
                <p class="text-zinc-500 text-sm">برای ثبت نظر <a href="/login" class="text-rose-500 font-semibold hover:underline">وارد شوید</a></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)) : ?>
        <div class="mt-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-zinc-800">محصولات مشابه</h2>
                <a href="/shop?category=<?= urlencode($product['category']) ?>" class="text-rose-500 text-sm font-medium hover:underline">مشاهده همه ←</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($related as $rel) : ?>
                    <?php
                    $relDiscount = 0;
                    if (!empty($rel['old_price']) && $rel['old_price'] > $rel['price']) {
                        $relDiscount = round((($rel['old_price'] - $rel['price']) / $rel['old_price']) * 100);
                    }
                    ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover transition-all duration-300">
                    <div class="relative product-image overflow-hidden">
                        <a href="/product/<?= $rel['id'] ?>">
                            <img src="/assets/images/<?= e($rel['image']) ?>" class="w-full h-52 object-cover transition-transform duration-500" onerror="this.src='/media/400/400/<?= $rel['id'] ?>'">
                        </a>
                        <?php if ($relDiscount > 0) : ?>
                        <span class="absolute top-3 right-3 bg-gradient-to-l from-red-500 to-red-600 text-white text-xs px-2 py-1 rounded-full"><?= $relDiscount ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <span class="text-xs text-rose-500 font-medium"><?= e($rel['brand'] ?? '') ?></span>
                        <h3 class="font-bold text-zinc-800 mt-1"><a href="/product/<?= $rel['id'] ?>" class="hover:text-rose-500 transition-colors"><?= e($rel['name']) ?></a></h3>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-bold text-rose-500"><?= number_format($rel['price']) ?> تومان</span>
                            <button onclick="addToCart(<?= $rel['id'] ?>)" class="w-9 h-9 bg-rose-50 rounded-full flex items-center justify-center text-rose-500 hover:bg-rose-600 hover:text-white transition-all">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let detailQty = 1;
function detailQtyChg(delta) {
    detailQty = Math.max(1, detailQty + delta);
    document.getElementById('qtyDisplay').textContent = detailQty.toLocaleString('fa-IR');
}
function changeImage(el) {
    document.getElementById('mainImage').src = el.src;
    document.querySelectorAll('.thumb-img').forEach(t => {
        t.classList.remove('border-rose-500', 'opacity-100');
        t.classList.add('border-transparent', 'opacity-70');
    });
    el.classList.remove('border-transparent', 'opacity-70');
    el.classList.add('border-rose-500', 'opacity-100');
}
function addToCartDetail(productId) {
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = d.cart_count || d.count;
            showToast(d.message || 'به سبد خرید اضافه شد');
        } else {
            showToast(d.error || d.message || 'خطا', 'error');
        }
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
function shareProduct() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({ url: url });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showToast('لینک محصول کپی شد');
        });
    }
}
function setRating(val) {
    document.getElementById('rating-value').value = val;
    document.querySelectorAll('.star-select').forEach(function(el) {
        var starVal = parseInt(el.dataset.value);
        el.className = (starVal <= val ? 'fa-solid' : 'fa-regular') + ' fa-star cursor-pointer hover:text-amber-400 transition-colors star-select' + (starVal <= val ? ' text-amber-400' : ' text-zinc-300');
    });
}
function submitReview(e, productId) {
    e.preventDefault();
    var rating = document.getElementById('rating-value').value;
    var text = document.getElementById('review-text').value;
    if (rating === '0') { showToast('لطفاً امتیاز دهید', 'error'); return; }
    if (text.trim() === '') { showToast('لطفاً متن نظر را وارد کنید', 'error'); return; }
    var btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-2"></i>در حال ثبت...';
    var body = 'rating=' + rating + '&text=' + encodeURIComponent(text.trim()) + '&' + csrfParam();
    fetch('/product/' + productId + '/review', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            showToast(d.message || 'نظر شما ثبت شد!');
            document.getElementById('review-text').value = '';
            setRating(0);
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            showToast(d.error || 'خطا', 'error');
        }
    })
    .catch(function() { showToast('خطا در ارتباط با سرور', 'error'); })
    .finally(function() { btn.disabled = false; btn.textContent = 'ثبت نظر'; });
}
</script>
