<style>
    .blog-content p {
        font-size: 1.1rem;
        line-height: 1.85;
    }
    .blog-content h2 {
        font-size: 1.65rem;
        position: relative;
        margin-top: 3rem;
        margin-bottom: 1rem;
    }
    .blog-content h2:after {
        content: '';
        position: absolute;
        width: 60px;
        height: 3px;
        background: #e11d48;
        bottom: -8px;
        right: 0;
    }
    .nav-link {
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nav-link:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        right: 0;
        background-color: #e11d48;
        transition: width 0.3s ease;
    }
    .nav-link:hover:after {
        width: 100%;
        right: auto;
        left: 0;
    }
    .featured-image {
        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .article-card:hover .featured-image {
        transform: scale(1.08);
    }
    .prose a {
        color: #e11d48;
        text-decoration: underline;
        text-underline-offset: 4px;
        transition: all 0.2s ease;
    }
    .prose a:hover {
        color: #be123c;
    }
    .scroll-progress {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px;
        background: linear-gradient(to right, #e11d48, #d4a843);
        z-index: 60;
    }
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.2);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .sidebar-sticky {
        position: sticky;
        top: 100px;
    }
</style>

<div id="progress-bar" class="scroll-progress w-0"></div>

<div class="max-w-screen-2xl mx-auto px-8 pt-6">
    <div class="flex items-center gap-x-2 text-xs text-rose-700 font-medium">
        <a href="/" class="hover:underline">خانه</a>
        <span class="text-rose-300">/</span>
        <a href="/blog" class="hover:underline">وبلاگ</a>
        <span class="text-rose-300">/</span>
        <span class="text-rose-900 line-clamp-1"><?= e($post['title']) ?></span>
    </div>
</div>

<header class="max-w-screen-2xl mx-auto px-8 pt-8 pb-12">
    <div class="max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-x-2 bg-white shadow text-rose-600 text-xs font-medium px-5 h-7 rounded-3xl mb-6">
            <div class="w-2 h-2 bg-rose-400 animate-pulse rounded-full"></div>
            <?= e($post['category']) ?>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 tracking-tighter text-center leading-tight">
            <?= e($post['title']) ?>
        </h1>
        <div class="flex flex-wrap justify-center items-center gap-x-8 gap-y-4 mt-10 text-sm">
            <div class="flex items-center gap-x-3">
                <div class="w-9 h-9 bg-rose-200 rounded-2xl overflow-hidden">
                    <img src="/avatar/<?= urlencode($post['author']) ?>/72" alt="<?= e($post['author'])?>" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="font-semibold text-gray-700"><?= e($post['author']) ?></div>
                    <div class="text-xs text-gray-500 -mt-0.5">نویسنده</div>
                </div>
            </div>
            <div class="h-5 w-px bg-gray-200"></div>
            <div class="flex items-center gap-x-2 text-gray-500">
                <i class="fa-solid fa-calendar text-xs"></i>
                <span class="text-sm"><?= jdate('Y/m/d', strtotime($post['published_at'])) ?></span>
            </div>
            <div class="flex items-center gap-x-2 text-gray-500">
                <i class="fa-solid fa-clock text-xs"></i>
                <span class="text-sm"><?= e($post['reading_time']) ?> دقیقه مطالعه</span>
            </div>
            <div class="flex items-center gap-x-5 text-gray-400">
                <button onclick="shareArticle('twitter')" class="hover:text-sky-400 transition-colors"><i class="fa-brands fa-x-twitter"></i></button>
                <button onclick="shareArticle('instagram')" class="hover:text-pink-500 transition-colors"><i class="fa-brands fa-instagram"></i></button>
                <button onclick="shareArticle('whatsapp')" class="hover:text-emerald-500 transition-colors"><i class="fa-brands fa-whatsapp"></i></button>
            </div>
        </div>
    </div>
</header>

<div class="max-w-screen-2xl mx-auto px-8">
    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-rose-100">
        <img src="/assets/images/<?= e($post['image'] ?: 'placeholder.svg') ?>"
             alt="<?= e($post['title']) ?>"
             class="featured-image w-full h-auto max-h-[520px] object-cover">
        <div class="absolute bottom-6 right-6 bg-white/90 backdrop-blur-md px-5 py-3 rounded-2xl shadow flex items-center gap-x-3 text-xs">
            <div class="flex -space-x-4">
                <div class="w-6 h-6 bg-rose-300 border-2 border-white rounded-2xl overflow-hidden">
                    <img src="/avatar/<?= urlencode($post['author']) ?>/48" class="object-cover">
                </div>
            </div>
            <div class="text-gray-500 text-[10px] leading-none">
                عکس از<br>
                <span class="font-medium text-gray-700">تیم موبارو</span>
            </div>
        </div>
        <?php if ($post['is_featured']) : ?>
        <div class="absolute top-8 left-8 bg-white rounded-3xl shadow-xl px-6 py-2 flex items-center gap-x-2 text-sm font-medium">
            <i class="fa-solid fa-star text-gold-500" style="color:#d4a843;"></i>
            <span>مقاله ویژه</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-screen-2xl mx-auto px-8 pt-16 pb-24 grid grid-cols-12 gap-10">

    <div class="col-span-12 lg:col-span-8">
        <div class="max-w-2xl mx-auto blog-content prose prose-zinc">

            <div class="blog-content">
                <?= $post['content'] ?>
            </div>

            <?php if (!empty($tags)) : ?>
            <div class="flex flex-wrap gap-2 mt-20">
                <?php foreach ($tags as $tag) :
                    $tag = trim($tag);
                    if (empty($tag)) {
                        continue;
                    } ?>
                <a href="/blog?s=<?= e($tag)?>" class="text-xs bg-white border border-rose-200 hover:bg-rose-50 transition-colors px-5 py-2 rounded-3xl">#<?= e($tag) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="mt-24 border-t border-b py-8 flex gap-6">
                <div class="w-16 h-16 bg-rose-200 rounded-3xl overflow-hidden flex-shrink-0">
                    <img src="/avatar/<?= urlencode($post['author']) ?>/80" alt="<?= e($post['author']) ?>" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex justify-between">
                        <div>
                            <span class="font-medium"><?= e($post['author']) ?></span>
                            <span class="block text-xs text-rose-500">نویسنده و متخصص حوزه زیبایی</span>
                        </div>
                    </div>
                    <p class="text-xs leading-relaxed text-gray-500 mt-5">
                        <?= e($post['author']) ?> با سال‌ها تجربه در زمینه آرایش و زیبایی، مطالب تخصصی و کاربردی را برای شما آماده می‌کند.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between border-b pb-8 mt-8">
                <div class="text-xs font-medium text-gray-400">این مقاله را به اشتراک بگذارید</div>
                <div class="flex items-center gap-x-6 text-2xl text-gray-300">
                    <button onclick="shareArticle('twitter')" class="hover:text-sky-400 transition-colors"><i class="fa-brands fa-x-twitter"></i></button>
                    <button onclick="shareArticle('facebook')" class="hover:text-blue-600 transition-colors"><i class="fa-brands fa-facebook-f"></i></button>
                    <button onclick="shareArticle('whatsapp')" class="hover:text-emerald-500 transition-colors"><i class="fa-brands fa-whatsapp"></i></button>
                </div>
            </div>

            <div class="mt-16">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="text-xl font-medium">نظرات</div>
                        <span class="text-sm text-zinc-400">(<?= faNum($commentCount) ?>)</span>
                    </div>
                    <button onclick="document.getElementById('comment-textarea').focus()"
                            class="flex items-center gap-x-2 text-sm font-medium bg-white shadow px-5 h-9 rounded-3xl">
                        <i class="fa-regular fa-comment"></i>
                        <span>نظر بدهید</span>
                    </button>
                </div>

                <div id="comments-list">
                <?php if (!empty($comments)) : ?>
                    <?php foreach ($comments as $comment) : ?>
                    <div class="bg-white rounded-3xl p-6 mb-4">
                        <div class="flex gap-x-3">
                            <div class="w-8 h-8 bg-rose-100 text-rose-500 rounded-2xl flex items-center justify-center text-xs font-bold"><?= e(mb_substr($comment['name'], 0, 1)) ?></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium text-sm"><?= e($comment['name']) ?></div>
                                    <div class="text-[10px] text-gray-400"><?= jdate('Y/m/d', strtotime($comment['created_at'])) ?></div>
                                </div>
                                <p class="text-sm text-gray-600 mt-2"><?= e($comment['text']) ?></p>
                                <button onclick="likeComment(<?= $comment['id'] ?>, this)"
                                        class="text-xs flex items-center gap-x-1 text-gray-400 hover:text-red-400 mt-5">
                                    <i class="fa-solid fa-heart"></i>
                                    <span class="like-count"><?= faNum($comment['likes']) ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="text-center py-10 text-zinc-400 text-sm">هنوز نظری ثبت نشده است. اولین نفری باشید که نظر می‌دهید!</div>
                <?php endif; ?>
                </div>

                <div class="mt-8 bg-gray-100 rounded-3xl p-2">
                    <textarea id="comment-textarea" rows="3"
                              class="w-full bg-white focus:outline-none rounded-3xl px-6 py-5 text-sm resize-none"
                              placeholder="نظر خود را بنویسید..."></textarea>
                    <div class="flex justify-between items-center px-4 pb-4 mt-2">
                        <input type="text" id="comment-name" placeholder="نام شما (در صورت عدم ورود)" class="px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 outline-none w-48 hidden">
                        <div></div>
                        <button onclick="postComment()"
                                class="bg-rose-600 text-white px-10 py-3.5 text-sm font-semibold rounded-3xl active:scale-95 transition-transform">
                            ارسال نظر
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-4 space-y-10">
        <div class="sidebar-sticky space-y-10">

            <div class="bg-white border border-rose-200 rounded-3xl p-7">
                <div class="text-center">
                    <span class="px-4 py-1 text-xs bg-rose-100 text-rose-600 rounded-3xl">خبرنامه موبارو</span>
                </div>
                <div class="text-2xl font-bold text-center mt-5 leading-none text-gray-800">
                    آخرین نکات زیبایی را دریافت کنید
                </div>
                <div class="mt-8">
                    <input id="newsletter-email" type="email"
                           class="w-full border border-transparent focus:border-rose-300 bg-zinc-50 rounded-3xl px-6 py-6 outline-none text-sm"
                           placeholder="ایمیل شما">
                </div>
                <button onclick="subscribeNewsletter()"
                        class="mt-4 w-full h-14 bg-gradient-to-r from-rose-600 to-rose-700 text-white rounded-3xl text-sm font-semibold tracking-wider shadow-inner">
                    مشترک شدن
                </button>
                <div class="text-[10px] text-center text-gray-400 mt-6">
                    هفته‌ای دو بار • بدون اسپم
                </div>
            </div>

            <div>
                <div class="flex items-baseline justify-between mb-5 px-1">
                    <span class="uppercase text-xs font-semibold tracking-widest text-gray-400">مقالات محبوب</span>
                </div>
                <?php foreach ($popularPosts as $pp) : ?>
                <a href="/blog/<?= e($pp['slug']) ?>" class="article-card group flex gap-4 cursor-pointer mb-8">
                    <div class="w-20 h-20 bg-cover bg-center rounded-2xl flex-shrink-0 shadow-inner"
                         style="background-image: url('/assets/images/<?= e($pp['image'] ?: 'placeholder.svg') ?>')"></div>
                    <div class="flex-1">
                        <div class="line-clamp-2 text-sm font-medium leading-tight group-hover:text-rose-600 transition-colors">
                            <?= e($pp['title']) ?>
                        </div>
                        <div class="text-rose-400 text-xs mt-4"><?= jdate('Y/m/d', strtotime($pp['published_at'])) ?> • <?= e($pp['reading_time']) ?> دقیقه</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($latestCourse)) : ?>
            <div class="bg-white border border-rose-200 rounded-3xl p-7">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-graduation-cap text-rose-500"></i>
                    <span class="text-sm font-bold text-gray-800">جدیدترین دوره آموزشی</span>
                </div>
                <a href="/course/<?= e($latestCourse['slug'] ?? $latestCourse['id']) ?>" class="block group">
                    <img src="/assets/images/<?= e($latestCourse['image'] ?? 'placeholder.svg') ?>" class="w-full h-36 rounded-2xl object-cover mb-3" alt="<?= e($latestCourse['title']) ?>" onerror="this.src='/media/400/200/<?= e($latestCourse['id']) ?>'">
                    <h5 class="font-bold text-gray-900 group-hover:text-rose-600 transition-colors line-clamp-2 mb-1"><?= e($latestCourse['title']) ?></h5>
                    <div class="text-xs text-gray-500 mb-2"><?= e($latestCourse['teacher']) ?></div>
                    <?php if (!empty($latestCourse['is_free'])) : ?>
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">رایگان</span>
                    <?php else : ?>
                    <span class="text-rose-500 font-bold text-sm"><?= number_format($latestCourse['price']) ?> تومان</span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="bg-gradient-to-br from-rose-50 to-white border border-dashed border-rose-300 rounded-3xl p-8">
                <div class="flex justify-center -mt-2">
                    <i class="fa-solid fa-spa text-6xl text-rose-300"></i>
                </div>
                <div class="text-center mt-6 text-xl font-medium">خدمات ویژه موبارو</div>
                <ul class="mt-7 space-y-6 text-sm">
                    <li class="flex justify-between items-center border-b border-dotted pb-6">
                        <span>کراتین تراپی</span>
                        <span class="font-mono text-rose-500">۱.۲۹۰</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-dotted pb-6">
                        <span>رنگساژ بالیاژ</span>
                        <span class="font-mono text-rose-500">۹۵۰</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span>لایه‌برداری پوست سر</span>
                        <span class="font-mono text-rose-500">۴۸۰</span>
                    </li>
                </ul>
                <a href="/#services" class="mt-8 text-xs w-full py-6 border border-rose-400 hover:bg-rose-50 transition-colors rounded-3xl font-medium block text-center">
                    مشاهده همه خدمات
                </a>
            </div>

            <div class="pt-3">
                <div class="flex justify-between text-xs font-medium text-gray-400 mb-6 px-1">
                    <div>@mobarosalon</div>
                    <div class="flex items-center gap-x-3">
                        <i class="fa-brands fa-instagram"></i>
                        <span>اینستاگرام</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="aspect-square bg-cover rounded-2xl" style="background-image:url('/assets/images/cache/400x400_201.svg')"></div>
                    <div class="aspect-square bg-cover rounded-2xl" style="background-image:url('/assets/images/cache/400x400_211.svg')"></div>
                    <div class="aspect-square bg-cover rounded-2xl" style="background-image:url('/assets/images/cache/400x400_29.svg')"></div>
                </div>
            </div>

            <div onclick="window.location.href='/booking'"
                 class="mt-6 bg-gradient-to-br from-rose-600 to-rose-800 text-white rounded-3xl px-7 py-7 cursor-pointer active:scale-[0.97] transition-transform">
                <div class="flex items-center justify-between">
                    <div class="max-w-[160px]">
                        <div class="text-sm opacity-75">نوبت خود را امروز رزرو کنید</div>
                        <div class="text-3xl leading-none mt-3 font-bold">تغییر را شروع کنید</div>
                    </div>
                    <div class="text-6xl opacity-60"><i class="fa-solid fa-calendar-check"></i></div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (!empty($relatedPosts)) : ?>
<div class="max-w-screen-2xl mx-auto px-8 py-16 bg-white">
    <div class="flex items-end justify-between mb-8">
        <div class="text-3xl font-bold text-gray-900">مقالات مرتبط</div>
        <a href="/blog" class="flex items-center text-xs gap-x-2 text-rose-600 hover:text-rose-700">
            همه مقالات
            <span class="text-xl leading-none">→</span>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($relatedPosts as $rp) : ?>
        <a href="/blog/<?= e($rp['slug']) ?>"
           class="article-card group bg-white border border-transparent hover:border-rose-200 rounded-3xl overflow-hidden cursor-pointer">
            <div class="h-60 bg-cover bg-center transition-all group-active:scale-105"
                 style="background-image: url('/assets/images/<?= e($rp['image'] ?: 'placeholder.svg') ?>')"></div>
            <div class="p-6">
                <div class="text-xs text-rose-500"><?= e($rp['category']) ?></div>
                <div class="font-medium text-xl leading-6 mt-2 line-clamp-2"><?= e($rp['title']) ?></div>
                <div class="flex justify-between items-center text-xs text-gray-400 mt-8">
                    <div><?= jdate('Y/m/d', strtotime($rp['published_at'])) ?></div>
                    <div class="flex items-center gap-x-1">
                        <i class="fa-solid fa-eye"></i>
                        <span><?= faNum($rp['views'] > 999 ? round($rp['views'] / 1000, 1) . 'k' : $rp['views']) ?></span>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function shareArticle(network) {
    let message = '';
    switch(network) {
        case 'twitter': message = 'مقاله "<?= e($post['title']) ?>" از وبلاگ موبارو را بخوانید!'; break;
        case 'facebook': message = 'اشتراک گذاری در فیسبوک'; break;
        case 'instagram': message = 'این پست را در اینستاگرام ذخیره کنید'; break;
        case 'whatsapp': message = 'مقاله را از طریق واتس‌اپ بفرستید'; break;
    }
    if (typeof showToast === 'function') showToast(message, 2000);
}
function likeComment(commentId, el) {
    var body = 'comment_id=' + commentId + '&' + csrfParam();
    fetch('/blog/comment/like', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var countEl = el.querySelector('.like-count');
            countEl.innerText = d.likes;
            el.classList.add('!text-red-400');
        } else if (typeof showToast === 'function') {
            showToast(d.error || 'خطا', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('خطا در ارتباط با سرور', 'error'); });
}
function postComment() {
    const textarea = document.getElementById('comment-textarea');
    if (textarea.value.trim() === '') return;
    var body = 'text=' + encodeURIComponent(textarea.value.trim()) + '&' + csrfParam();
    fetch('/blog/<?= e($post['slug']) ?>/comment', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            if (typeof showToast === 'function') showToast(d.message || 'نظر شما ثبت شد!', 2500);
            textarea.value = '';
        } else {
            if (typeof showToast === 'function') showToast(d.error || 'خطا', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('خطا در ارتباط با سرور', 'error'); });
}
function subscribeNewsletter() {
    const email = document.getElementById('newsletter-email');
    if (!email || !email.value.trim()) { if (typeof showToast === 'function') showToast('لطفا ایمیل خود را وارد کنید', 'error'); return; }
    var body = 'contact=' + encodeURIComponent(email.value.trim()) + '&' + csrfParam();
    fetch('/newsletter/subscribe', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
    .then(function(r) { return r.json(); })
    .then(function(d) { if (typeof showToast === 'function') showToast(d.message || d.error, d.success ? 'success' : 'error'); if (d.success) email.value = ''; })
    .catch(function() { if (typeof showToast === 'function') showToast('خطا در ارتباط با سرور', 'error'); });
}
(function() {
    const bar = document.getElementById('progress-bar');
    if (bar) {
        window.addEventListener('scroll', function() {
            const st = window.scrollY;
            const dh = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            bar.style.width = (st / dh * 100) + '%';
        });
    }
})();
</script>
