<?php $title = 'فروشگاه | موبارو'; ?>
<?php
function filterUrl(array $overrides = []): string
{
    $params = [];
    $current = [
        'category', 'brand', 'search', 'sort',
        'price_min', 'price_max', 'rating',
        'is_sale', 'is_new', 'in_stock',
    ];
    foreach ($current as $key) {
        $val = $_GET[$key] ?? null;
        if ($val !== null && $val !== '' && $val !== 'all') {
            if ($key === 'sort' && $val === 'newest') continue;
            $params[$key] = $val;
        }
    }
    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    if (empty($params)) return '/shop';
    return '/shop?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}
?>

<div class="min-h-screen bg-zinc-50">
    <!-- Breadcrumb -->
    <div class="bg-gradient-to-r from-rose-50 to-pink-50 py-6">
        <div class="max-w-screen-2xl mx-auto px-8">
            <div class="flex items-center gap-2 text-sm text-zinc-600">
                <a href="/" class="hover:text-rose-500 transition">خانه</a>
                <i class="fa-solid fa-chevron-left text-xs text-rose-400"></i>
                <span class="text-rose-500 font-medium">فروشگاه</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold mt-3 text-zinc-800">محصولات آرایشی و بهداشتی</h2>
            <p class="text-zinc-500 mt-2">بهترین محصولات با کیفیت برتر</p>
        </div>
    </div>

    <div class="max-w-screen-2xl mx-auto px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28 sidebar-filter max-h-[calc(100vh-150px)] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg text-zinc-800">فیلترها</h3>
                        <a href="/shop" class="text-rose-500 text-sm hover:underline">حذف همه</a>
                    </div>

                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-zinc-700 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-rose-400"></i>
                            دسته‌بندی‌ها
                        </h4>
                        <div class="space-y-3">
                            <a href="<?= filterUrl(['category' => null]) ?>" class="flex items-center justify-between group <?= $category === 'all' ? 'text-rose-500 font-medium' : 'text-zinc-600' ?>">
                                <span class="group-hover:text-rose-500 transition">همه محصولات</span>
                                <span class="text-xs text-zinc-400"><?= $allTotal ?></span>
                            </a>
                            <?php foreach ($categoryRows as $cat): ?>
                            <a href="<?= filterUrl(['category' => $cat['category']]) ?>" class="flex items-center justify-between group <?= $category === $cat['category'] ? 'text-rose-500 font-medium' : 'text-zinc-600' ?>">
                                <span class="group-hover:text-rose-500 transition"><?= e($cat['category']) ?></span>
                                <span class="text-xs text-zinc-400"><?= $cat['cnt'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="border-zinc-100 my-6">

                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-zinc-700 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-tag text-rose-400"></i>
                            محدوده قیمت
                        </h4>
                        <input type="range" min="0" max="5000000" value="<?= min($priceMax ?: 2500000, 5000000) ?>" class="range-slider mb-4" id="priceRange">
                        <div class="flex justify-between text-sm text-zinc-600">
                            <span>۰ تومان</span>
                            <span id="priceValue" class="font-medium text-rose-500"><?= $priceMax ? number_format($priceMax) : '۲,۵۰۰,۰۰۰' ?> تومان</span>
                        </div>
                        <button onclick="applyPriceFilter()" class="mt-3 w-full py-2 bg-rose-100 text-rose-600 rounded-xl text-sm font-semibold hover:bg-rose-200 transition">اعمال فیلتر قیمت</button>
                    </div>

                    <hr class="border-zinc-100 my-6">

                    <!-- Brands -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-zinc-700 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-star text-rose-400"></i>
                            برندها
                        </h4>
                        <div class="space-y-3">
                            <?php foreach ($brandRows as $br): ?>
                            <a href="<?= filterUrl(['brand' => $br['brand']]) ?>" class="flex items-center justify-between group <?= $brand === $br['brand'] ? 'text-rose-500 font-medium' : 'text-zinc-600' ?>">
                                <span class="group-hover:text-rose-500 transition"><?= e($br['brand']) ?></span>
                                <span class="text-xs text-zinc-400"><?= $br['cnt'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="border-zinc-100 my-6">

                    <!-- Rating -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-zinc-700 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-star text-rose-400"></i>
                            امتیاز
                        </h4>
                        <div class="space-y-3">
                            <?php foreach ([5,4,3] as $r): ?>
                            <a href="<?= filterUrl(['rating' => $r]) ?>" class="flex items-center gap-3 group <?= ($rating ?? 0) == $r ? 'text-rose-500' : 'text-zinc-600' ?>">
                                <div class="star-rating text-amber-400 text-sm">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $r ? 'fa-solid' : 'fa-regular text-zinc-300' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-xs group-hover:text-rose-500 transition">و بالاتر</span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="border-zinc-100 my-6">

                    <!-- Extra Filters -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-zinc-700 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-filter text-rose-400"></i>
                            فیلترهای بیشتر
                        </h4>
                        <div class="space-y-3">
                            <a href="<?= filterUrl(['is_sale' => $isSale ? null : 1]) ?>" class="flex items-center gap-3 group <?= $isSale ? 'text-rose-500' : 'text-zinc-600' ?>">
                                <span class="w-5 h-5 rounded border-2 flex items-center justify-center text-xs <?= $isSale ? 'bg-rose-500 border-rose-500 text-white' : 'border-zinc-300' ?>">
                                    <?php if ($isSale): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                                </span>
                                <span class="group-hover:text-rose-500 transition">حراج و تخفیف‌دار</span>
                            </a>
                            <a href="<?= filterUrl(['is_new' => $isNew ? null : 1]) ?>" class="flex items-center gap-3 group <?= $isNew ? 'text-rose-500' : 'text-zinc-600' ?>">
                                <span class="w-5 h-5 rounded border-2 flex items-center justify-center text-xs <?= $isNew ? 'bg-rose-500 border-rose-500 text-white' : 'border-zinc-300' ?>">
                                    <?php if ($isNew): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                                </span>
                                <span class="group-hover:text-rose-500 transition">جدیدترین محصولات</span>
                            </a>
                            <a href="<?= filterUrl(['in_stock' => $inStock ? null : 1]) ?>" class="flex items-center gap-3 group <?= $inStock ? 'text-rose-500' : 'text-zinc-600' ?>">
                                <span class="w-5 h-5 rounded border-2 flex items-center justify-center text-xs <?= $inStock ? 'bg-rose-500 border-rose-500 text-white' : 'border-zinc-300' ?>">
                                    <?php if ($inStock): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                                </span>
                                <span class="group-hover:text-rose-500 transition">فقط محصولات موجود</span>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Products Section -->
            <div class="flex-1">
                <!-- Sort and View Options -->
                <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-zinc-600">نمایش <span class="font-bold text-rose-500" id="productCountDisplay"><?= count($products) ?></span> محصول</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-zinc-600 text-sm">مرتب‌سازی:</span>
                            <a href="<?= filterUrl(['sort' => 'newest']) ?>" class="border border-zinc-200 rounded-lg px-4 py-2 text-zinc-700 <?= $sort === 'newest' ? 'bg-rose-50 border-rose-300 text-rose-500' : '' ?>">جدیدترین</a>
                            <a href="<?= filterUrl(['sort' => 'popular']) ?>" class="border border-zinc-200 rounded-lg px-4 py-2 text-zinc-700 <?= $sort === 'popular' ? 'bg-rose-50 border-rose-300 text-rose-500' : '' ?>">پرفروش‌ترین</a>
                            <a href="<?= filterUrl(['sort' => 'price_asc']) ?>" class="border border-zinc-200 rounded-lg px-4 py-2 text-zinc-700 <?= $sort === 'price_asc' ? 'bg-rose-50 border-rose-300 text-rose-500' : '' ?>">ارزان‌ترین</a>
                            <a href="<?= filterUrl(['sort' => 'price_desc']) ?>" class="border border-zinc-200 rounded-lg px-4 py-2 text-zinc-700 <?= $sort === 'price_desc' ? 'bg-rose-50 border-rose-300 text-rose-500' : '' ?>">گران‌ترین</a>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="/shop" class="relative mb-6" id="searchForm">
                    <input type="hidden" name="category" value="<?= e($category) ?>">
                    <input type="hidden" name="brand" value="<?= e($brand) ?>">
                    <input type="hidden" name="sort" value="<?= e($sort) ?>">
                    <?php if ($priceMin > 0): ?><input type="hidden" name="price_min" value="<?= $priceMin ?>"><?php endif; ?>
                    <?php if ($priceMax > 0): ?><input type="hidden" name="price_max" value="<?= $priceMax ?>"><?php endif; ?>
                    <?php if ($rating > 0): ?><input type="hidden" name="rating" value="<?= $rating ?>"><?php endif; ?>
                    <?php if ($isSale): ?><input type="hidden" name="is_sale" value="1"><?php endif; ?>
                    <?php if ($isNew): ?><input type="hidden" name="is_new" value="1"><?php endif; ?>
                    <?php if ($inStock): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="جستجوی محصولات..." 
                           class="w-full py-4 px-6 pr-14 rounded-2xl border-2 border-rose-200 focus:border-rose-400 focus:outline-none transition bg-white shadow-sm">
                    <i class="fa-solid fa-search absolute right-5 top-1/2 -translate-y-1/2 text-rose-400 text-lg"></i>
                </form>

                <!-- Products Grid -->
                <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                    <?php 
                        $item = $product;
                        $discount = 0;
                        if (!empty($item['old_price']) && $item['old_price'] > $item['price']) {
                            $discount = round((($item['old_price'] - $item['price']) / $item['old_price']) * 100);
                        }
                    ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover transition-all duration-300 product-card-shop">
                        <div class="relative product-image overflow-hidden">
                            <a href="/product/<?= $item['id'] ?>">
                                <img src="/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-64 object-cover transition-transform duration-500" onerror="this.src='/media/400/400/<?= $item['id'] ?>'">
                            </a>
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                <?php if ($discount > 0): ?>
                                <span class="bg-gradient-to-l from-red-500 to-red-600 text-white text-xs px-3 py-1 rounded-full font-bold"><?= $discount ?>% تخفیف</span>
                                <?php endif; ?>
                                <?php if (!empty($item['is_new'])): ?>
                                <span class="bg-gradient-to-l from-emerald-500 to-emerald-600 text-white text-xs px-3 py-1 rounded-full font-bold">جدید</span>
                                <?php endif; ?>
                            </div>
                            <div class="absolute top-4 left-4">
                                <button onclick="toggleWishlistItem(<?= $item['id'] ?>)" class="heart-btn w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg transition <?= in_array($item['id'], $wishlist) ? 'active' : '' ?>">
                                    <i class="<?= in_array($item['id'], $wishlist) ? 'fa-solid' : 'fa-regular' ?> fa-heart text-rose-500"></i>
                                </button>
                            </div>
                            <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                                <button onclick="openQuickView(<?= $item['id'] ?>)" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-zinc-700 hover:bg-rose-500 hover:text-white transition">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                <button onclick="addToCart(<?= $item['id'] ?>, this)" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-zinc-700 hover:bg-rose-500 hover:text-white transition">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-5">
                            <span class="text-xs text-rose-500 font-medium"><?= e($item['brand'] ?? '') ?></span>
                            <a href="/product/<?= $item['id'] ?>">
                                <h3 class="font-bold text-zinc-800 mt-1 mb-2 line-clamp-1 hover:text-rose-500 transition-colors"><?= e($item['name']) ?></h3>
                            </a>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="star-rating text-amber-400 text-sm">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($item['rating'] ?? 0)): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php elseif ($i - 0.5 <= ($item['rating'] ?? 0)): ?>
                                            <i class="fa-solid fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-star text-zinc-300"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-zinc-400 text-sm">(<?= number_format($item['reviews'] ?? 0) ?>)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <?php if (!empty($item['old_price']) && $item['old_price'] > $item['price']): ?>
                                    <span class="text-zinc-400 text-sm line-through ml-2"><?= number_format($item['old_price']) ?> تومان</span>
                                    <?php endif; ?>
                                    <span class="text-lg font-bold text-rose-500"><?= number_format($item['price']) ?> تومان</span>
                                </div>
                                <button onclick="addToCart(<?= $item['id'] ?>, this)" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm transition-all">
                                    <i class="fa-solid fa-plus ml-1"></i>افزودن
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                    <div class="col-span-full text-center py-16">
                        <i class="fa-regular fa-box text-6xl text-zinc-300 mb-4"></i>
                        <p class="text-zinc-500 text-lg">محصولی یافت نشد</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 mt-10" id="pagination">
                    <?php if ($page > 1): ?>
                    <a href="<?= filterUrl(['page' => $page - 1]) ?>" class="w-10 h-10 rounded-lg border border-zinc-200 text-zinc-500 hover:border-rose-400 hover:text-rose-500 transition flex items-center justify-center">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= filterUrl(['page' => $i]) ?>" class="w-10 h-10 rounded-lg flex items-center justify-center font-medium transition <?= $i === $page ? 'bg-rose-600 text-white' : 'border border-zinc-200 text-zinc-600 hover:border-rose-400 hover:text-rose-500' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="<?= filterUrl(['page' => $page + 1]) ?>" class="w-10 h-10 rounded-lg border border-zinc-200 text-zinc-500 hover:border-rose-400 hover:text-rose-500 transition flex items-center justify-center">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var range = document.getElementById('priceRange');
    var display = document.getElementById('priceValue');
    if (range && display) {
        function updateDisplay() {
            var val = parseInt(range.value);
            display.textContent = val.toLocaleString('fa-IR') + ' تومان';
        }
        range.addEventListener('input', updateDisplay);
        updateDisplay();
    }
});
function applyPriceFilter() {
    var range = document.getElementById('priceRange');
    if (range) {
        var val = parseInt(range.value);
        if (val > 0 && val < 5000000) {
            window.location.href = '<?= filterUrl(['price_max' => '__VAL__', 'page' => null]) ?>'.replace('__VAL__', val);
        } else if (val >= 5000000) {
            window.location.href = '<?= filterUrl(['price_max' => null, 'page' => null]) ?>';
        }
    }
}
const shopProducts = <?= json_encode(array_map(function($p) {
    return [
        'id' => (int)$p['id'],
        'name' => $p['name'],
        'brand' => $p['brand'] ?? '',
        'category' => $p['category'] ?? '',
        'price' => (int)$p['price'],
        'old_price' => (int)($p['old_price'] ?? 0),
        'discount' => (!empty($p['old_price']) && $p['old_price'] > $p['price']) ? round((($p['old_price'] - $p['price']) / $p['old_price']) * 100) : 0,
        'rating' => (float)($p['rating'] ?? 0),
        'reviews' => (int)($p['reviews'] ?? 0),
        'image' => '/assets/images/' . e($p['image']),
        'is_new' => (bool)($p['is_new'] ?? 0),
        'is_sale' => (!empty($p['old_price']) && $p['old_price'] > $p['price']),
        'description' => $p['description'] ?? '',
    ];
}, $products), JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Quick View Modal -->
<div id="quickViewModal" class="quick-view-modal">
    <div class="bg-white rounded-3xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 md:p-8">
            <div class="flex justify-end mb-4">
                <button onclick="closeQuickView()" class="text-zinc-400 hover:text-zinc-600">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <div class="grid md:grid-cols-2 gap-8" id="quickViewContent">
            </div>
        </div>
    </div>
</div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="cart-sidebar hidden">
    <div class="absolute inset-0 bg-black/50" onclick="toggleCart()"></div>
    <div class="absolute left-0 top-0 h-full w-full max-w-md bg-white shadow-2xl transform transition-transform duration-300">
        <div class="p-6 h-full flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-zinc-800">سبد خرید</h3>
                <button onclick="toggleCart()" class="text-zinc-400 hover:text-zinc-600">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div id="cartItems" class="flex-1 overflow-y-auto">
            </div>
            <div class="border-t pt-6 mt-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-zinc-600">جمع کل:</span>
                    <span id="cartTotal" class="text-xl font-bold text-rose-500">۰ تومان</span>
                </div>
                <button onclick="window.location.href='/cart'" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-medium mb-3 transition-all">
                    تکمیل خرید
                </button>
                <button onclick="toggleCart()" class="w-full border border-zinc-200 text-zinc-600 py-3 rounded-xl font-medium hover:bg-zinc-50 transition">
                    ادامه خرید
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Wishlist Sidebar -->
<div id="wishlistSidebar" class="cart-sidebar hidden">
    <div class="absolute inset-0 bg-black/50" onclick="toggleWishlistSidebar()"></div>
    <div class="absolute left-0 top-0 h-full w-full max-w-md bg-white shadow-2xl">
        <div class="p-6 h-full flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-zinc-800">لیست علاقه‌مندی‌ها</h3>
                <button onclick="toggleWishlistSidebar()" class="text-zinc-400 hover:text-zinc-600">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div id="wishlistItems" class="flex-1 overflow-y-auto">
            </div>
        </div>
    </div>
</div>
