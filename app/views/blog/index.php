<style>
    .gradient-text {
        background: linear-gradient(135deg, #be123c 0%, #d4a843 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
    }
    .image-zoom {
        overflow: hidden;
    }
    .image-zoom img {
        transition: transform 0.5s ease;
    }
    .image-zoom:hover img {
        transform: scale(1.1);
    }
    .category-pill {
        transition: all 0.3s ease;
    }
    .category-pill:hover {
        background-color: #be123c;
        color: white;
    }
    .sidebar-sticky {
        position: sticky;
        top: 100px;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .blog-content p {
        font-size: 1.1rem;
        line-height: 1.85;
    }
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.2);
    }
</style>

<section class="pt-28 pb-16 bg-gradient-to-b from-rose-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="inline-block px-4 py-1 rounded-full bg-rose-100 text-rose-700 text-sm font-medium mb-4">مجله زیبایی موبارو</span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                رازهای <span class="gradient-text">زیبایی و سلامت</span><br>
                را با ما کشف کنید
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                جدیدترین ترندهای آرایشی، آموزش‌های تخصصی مراقبت از پوست و مو، و نکات طلایی برای حفظ جوانی و شادابی
            </p>

            <form method="GET" action="/blog" class="flex flex-wrap justify-center gap-3 mt-8">
                <button type="submit" name="category" value=""
                    class="category-pill px-6 py-2 rounded-full text-sm font-medium shadow-sm <?= empty($category) ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:shadow-md' ?>">
                    همه مطالب
                </button>
                <?php foreach ($categories as $cat): ?>
                <button type="submit" name="category" value="<?= e($cat['category']) ?>"
                    class="category-pill px-6 py-2 rounded-full text-sm font-medium shadow-sm <?= $category === $cat['category'] ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:shadow-md' ?>">
                    <?= e($cat['category']) ?>
                </button>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="/blog/<?= e($featured['slug']) ?>" class="bg-white rounded-3xl overflow-hidden shadow-xl card-hover cursor-pointer group block">
            <div class="grid md:grid-cols-2 gap-0">
                <div class="image-zoom h-64 md:h-auto">
                    <img src="/assets/images/<?= e($featured['image']) ?>" alt="<?= e($featured['title']) ?>" class="w-full h-full object-cover">
                </div>
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-gold-100 text-gold-600 rounded-full text-sm font-medium" style="background:#fae8b8;color:#b88d2e;">ویژه</span>
                        <span class="text-gray-500 text-sm"><i class="far fa-calendar-alt ml-1"></i> <?= jdate('Y/m/d', strtotime($featured['published_at'])) ?></span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4 group-hover:text-rose-600 transition-colors">
                        <?= e($featured['title']) ?>
                    </h2>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        <?= e($featured['excerpt']) ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="/avatar/<?= urlencode($featured['author']) ?>/80" class="w-10 h-10 rounded-full border-2 border-rose-200" alt="<?= e($featured['author']) ?>">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?= e($featured['author']) ?></p>
                                <p class="text-xs text-gray-500">نویسنده</p>
                            </div>
                        </div>
                        <span class="text-rose-600 font-medium flex items-center gap-2 group-hover:gap-3 transition-all">
                            ادامه مطلب <i class="fas fa-arrow-left"></i>
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
</section>
<?php endif; ?>

<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                <?php if (!empty($search)): ?>
                <div class="bg-rose-50 rounded-2xl p-4 text-center">
                    <p class="text-gray-700">نتایج جستجو برای: <span class="font-bold text-rose-600"><?= e($search) ?></span></p>
                </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-500">مطلبی یافت نشد</h3>
                    <p class="text-gray-400 mt-2">در حال حاضر مقاله‌ای در این دسته وجود ندارد.</p>
                </div>
                <?php else: ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-2xl overflow-hidden shadow-lg card-hover group">
                        <a href="/blog/<?= e($post['slug']) ?>">
                            <div class="image-zoom h-48 relative">
                                <img src="/assets/images/<?= e($post['image'] ?: 'placeholder.svg') ?>" alt="<?= e($post['title']) ?>" class="w-full h-full object-cover">
                                <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-bold text-rose-600 shadow-md">
                                    <?= e($post['category']) ?>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-rose-600 transition-colors line-clamp-2">
                                    <?= e($post['title']) ?>
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    <?= e($post['excerpt']) ?>
                                </p>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <span class="text-xs text-gray-500"><i class="far fa-clock ml-1"></i> <?= e($post['reading_time']) ?> دقیقه مطالعه</span>
                                    <span class="text-rose-600 text-sm font-medium hover:underline">بیشتر بخوانید</span>
                                </div>
                            </div>
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-12 gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&category=<?= e($category) ?>&s=<?= e($search) ?>" class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&category=<?= e($category) ?>&s=<?= e($search) ?>"
                       class="w-10 h-10 rounded-full flex items-center justify-center font-medium transition-all <?= $i === $page ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'border border-gray-300 text-gray-600 hover:bg-rose-600 hover:text-white hover:border-rose-600' ?>">
                        <?= faNum($i) ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&category=<?= e($category) ?>&s=<?= e($search) ?>" class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>

            <aside class="space-y-8">
                <div class="sidebar-sticky space-y-8">

                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">درباره ما</h4>
                        <div class="text-center">
                            <img src="/assets/images/cache/400x300_1562322140.svg" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-rose-100" alt="Salon">
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                سالن زیبایی موبارو با بیش از ۱۰ سال تجربه، ارائه دهنده خدمات تخصصی آرایش و زیبایی با جدیدترین متدهای روز دنیا.
                            </p>
                            <div class="flex justify-center gap-3">
                                <a href="<?= e($settings['brand_instagram'] ?? '#') ?>" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-colors">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="<?= e($settings['brand_telegram'] ?? '#') ?>" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-colors">
                                    <i class="fab fa-telegram"></i>
                                </a>
                                <a href="#" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-colors">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">جستجو</h4>
                        <form method="GET" action="/blog" class="relative">
                            <input type="text" name="s" value="<?= e($search) ?>" placeholder="جستجو..." class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:outline-none search-input">
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">دسته‌بندی‌ها</h4>
                        <ul class="space-y-3">
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="/blog?category=<?= e($cat['category']) ?>" class="flex justify-between items-center text-gray-700 hover:text-rose-600 transition-colors group">
                                    <span><?= e($cat['category']) ?></span>
                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs group-hover:bg-rose-100 group-hover:text-rose-600 transition-colors"><?= faNum($cat['cnt']) ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">محبوب‌ترین مطالب</h4>
                        <div class="space-y-4">
                            <?php foreach ($popularPosts as $pp): ?>
                            <a href="/blog/<?= e($pp['slug']) ?>" class="flex gap-3 group">
                                <img src="/assets/images/<?= e($pp['image'] ?: 'placeholder.svg') ?>" class="w-20 h-20 rounded-lg object-cover flex-shrink-0" alt="<?= e($pp['title']) ?>">
                                <div>
                                    <h5 class="text-sm font-bold text-gray-900 group-hover:text-rose-600 transition-colors line-clamp-2 mb-1">
                                        <?= e($pp['title']) ?>
                                    </h5>
                                    <span class="text-xs text-gray-500"><?= jdate('Y/m/d', strtotime($pp['published_at'])) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($latestCourse)): ?>
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">جدیدترین دوره آموزشی</h4>
                        <a href="/course/<?= e($latestCourse['slug'] ?? $latestCourse['id']) ?>" class="block group">
                            <img src="/assets/images/<?= e($latestCourse['image'] ?? 'placeholder.svg') ?>" class="w-full h-40 rounded-xl object-cover mb-3" alt="<?= e($latestCourse['title']) ?>" onerror="this.src='/media/400/200/<?= e($latestCourse['id']) ?>'">
                            <h5 class="font-bold text-gray-900 group-hover:text-rose-600 transition-colors line-clamp-2 mb-1"><?= e($latestCourse['title']) ?></h5>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                <span><?= e($latestCourse['teacher']) ?></span>
                                <?php if (!empty($latestCourse['rating'])): ?>
                                <span>•</span>
                                <span class="text-amber-500">★ <?= e($latestCourse['rating']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($latestCourse['is_free'])): ?>
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">رایگان</span>
                            <?php else: ?>
                            <span class="text-rose-500 font-bold text-sm"><?= number_format($latestCourse['price']) ?> تومان</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($allTags)): ?>
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-rose-50">
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="border-right:4px solid #e11d48;padding-right:12px;">برچسب‌ها</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($allTags as $tag): ?>
                            <a href="/blog?s=<?= e($tag) ?>" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs hover:bg-rose-600 hover:text-white transition-colors"><?= e($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="bg-gradient-to-br from-rose-600 to-rose-700 rounded-2xl p-6 shadow-lg text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
                        <h4 class="text-lg font-bold mb-2">عضویت در خبرنامه</h4>
                        <p class="text-rose-100 text-sm mb-4">از جدیدترین مقالات و تخفیف‌های ویژه مطلع شوید</p>
                        <form class="space-y-3" onsubmit="event.preventDefault();blogSubscribeNewsletter()">
                            <input id="nl-email-blog" type="email" placeholder="ایمیل خود را وارد کنید" class="w-full px-4 py-3 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-rose-300 text-sm">
                            <button type="submit" class="w-full" style="background:#d4a843;color:white;padding:12px 20px;border-radius:12px;font-weight:bold;transition:all 0.3s;">عضو شوید</button>
                        </form>
                        <script>
                        function blogSubscribeNewsletter() {
                            var email = document.getElementById('nl-email-blog');
                            if (!email || !email.value.trim()) { if (typeof showToast === 'function') showToast('لطفا ایمیل خود را وارد کنید', 'error'); return; }
                            var body = 'contact=' + encodeURIComponent(email.value.trim()) + '&' + csrfParam();
                            fetch('/newsletter/subscribe', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
                            .then(function(r) { return r.json(); })
                            .then(function(d) { if (typeof showToast === 'function') showToast(d.message || d.error, d.success ? 'success' : 'error'); if (d.success) email.value = ''; })
                            .catch(function() { if (typeof showToast === 'function') showToast('خطا در ارتباط با سرور', 'error'); });
                        }
                        </script>
                    </div>

                </div>
            </aside>

        </div>
    </div>
</section>
