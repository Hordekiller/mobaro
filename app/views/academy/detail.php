<?php
$title = e($course['title']) . ' | موبارو';
$curriculum = json_decode($course['curriculum'] ?? '[]', true) ?: [];
$audience = json_decode($course['audience'] ?? '[]', true) ?: [];
$faqs = json_decode($course['faqs'] ?? '[]', true) ?: [];
$reviews = json_decode($course['reviews'] ?? '[]', true) ?: [];
$isEnrolled = false;
$enrollment = null;
if (isset($_SESSION['user'])) {
    $enrollment = Database::fetch(
        "SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?",
        [$_SESSION['user']['id'], $course['id']]
    );
    $isEnrolled = (bool) $enrollment;
}
?>

<nav class="flex items-center gap-2 text-sm text-zinc-500 mb-6">
    <a href="/" class="hover:text-rose-600 transition-colors">خانه</a>
    <i class="fa-solid fa-chevron-left text-[10px]"></i>
    <a href="/academy" class="hover:text-rose-600 transition-colors">آکادمی</a>
    <i class="fa-solid fa-chevron-left text-[10px]"></i>
    <span class="text-zinc-800 font-medium"><?= e($course['title']) ?></span>
</nav>

<div class="grid lg:grid-cols-[1fr_360px] gap-8 mb-16">
    <div class="space-y-6">

        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-indigo-600 to-rose-600 text-white p-8 md:p-12">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full blur-3xl"></div>
            </div>
            <div class="relative">
                <div class="flex flex-wrap gap-2 mb-4">
                    <?php if ($course['is_free']) : ?>
                    <span class="bg-emerald-500/20 text-emerald-200 text-xs px-3 py-1 rounded-full border border-emerald-400/30">رایگان</span>
                    <?php endif; ?>
                    <span class="bg-white/15 text-white/90 text-xs px-3 py-1 rounded-full border border-white/20"><?= e($course['category']) ?></span>
                    <span class="bg-white/15 text-white/90 text-xs px-3 py-1 rounded-full border border-white/20"><?= e($course['level']) ?></span>
                </div>
                <h1 class="text-2xl md:text-4xl font-bold leading-tight tracking-tight"><?= e($course['title']) ?></h1>
                <p class="mt-4 text-white/70 leading-relaxed max-w-2xl"><?= e($course['description']) ?></p>
                <div class="mt-6 flex flex-wrap items-center gap-6 text-sm text-white/70">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-user text-amber-300"></i>
                        <span><?= e($course['teacher']) ?></span>
                    </div>
                    <div class="flex items-center gap-1 text-amber-300">
                        <?= str_repeat('★', (int) round($course['rating'])) ?>
                        <span class="text-white/70 mr-1"><?= number_format($course['rating'], 1) ?></span>
                    </div>
                    <div><i class="fa-solid fa-user-group ml-1"></i><?= number_format($course['students']) ?> دانشجو</div>
                    <div><i class="fa-solid fa-clock ml-1"></i><?= e($course['duration']) ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($course['video_url'])) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-100">
                <h2 class="text-lg font-bold"><i class="fa-solid fa-play-circle text-rose-500 ml-2"></i>پیش‌نمایش دوره</h2>
            </div>
            <div class="aspect-video bg-zinc-900">
                <?php if (($course['video_type'] ?? 'upload') === 'youtube') : ?>
                <iframe class="w-full h-full" src="https://www.youtube.com/embed/<?= e(getYoutubeId($course['video_url'])) ?>" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                <?php elseif (($course['video_type'] ?? 'upload') === 'aparat') : ?>
                <iframe class="w-full h-full" src="https://www.aparat.com/video/video/embed/videohash/<?= e(getAparatHash($course['video_url'])) ?>/vt/frame" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                <?php else : ?>
                <video controls preload="metadata" id="preview-video" class="w-full h-full object-contain" poster="/assets/images/<?= e($course['image'] ?? '') ?>">
                    <source src="<?= e($course['video_url']) ?>" type="video/mp4">
                    مرورگر شما پخش ویدیو را پشتیبانی نمی‌کند.
                </video>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($audience)) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4"><i class="fa-solid fa-bullseye text-rose-500 ml-2"></i>این دوره برای چه کسانی مناسب است؟</h2>
            <div class="grid sm:grid-cols-2 gap-3">
                <?php foreach ($audience as $i => $item) : ?>
                <div class="flex items-start gap-3 p-3 bg-zinc-50 rounded-2xl">
                    <div class="w-9 h-9 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0"><?= faNum($i + 1) ?></div>
                    <div>
                        <div class="font-medium text-sm"><?= e($item['title'] ?? '') ?></div>
                        <div class="text-xs text-zinc-500 mt-0.5"><?= e($item['desc'] ?? '') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($curriculum)) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4"><i class="fa-solid fa-book-open text-rose-500 ml-2"></i>برنامه درسی دوره</h2>
            <div class="space-y-3">
                <?php foreach ($curriculum as $mi => $module) : ?>
                <div class="detail-tab-module border border-zinc-100 rounded-2xl overflow-hidden">
                    <button onclick="toggleModule(this)" class="w-full flex items-center justify-between p-4 hover:bg-zinc-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-sm font-bold"><?= faNum($mi + 1) ?></span>
                            <span class="font-medium text-sm"><?= e($module['title'] ?? '') ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-zinc-400"><?= count($module['lessons'] ?? []) ?> درس • <?= e($module['duration'] ?? '') ?></span>
                            <i class="fa-solid fa-chevron-down text-xs text-zinc-400 transition-transform"></i>
                        </div>
                    </button>
                    <div class="module-lessons hidden border-t border-zinc-100">
                        <?php foreach ($module['lessons'] ?? [] as $lesson) : ?>
                        <div class="flex items-center justify-between px-4 py-3 text-sm hover:bg-zinc-50">
                            <div class="flex items-center gap-2 text-zinc-600"><i class="fa-solid fa-play text-[10px] text-rose-400"></i><?= e($lesson['title'] ?? '') ?></div>
                            <span class="text-xs text-zinc-400"><?= e($lesson['duration'] ?? '') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4"><i class="fa-solid fa-chalkboard-user text-rose-500 ml-2"></i>مدرس دوره</h2>
            <div class="flex items-start gap-4">
                <img src="/avatar/<?= e($course['teacher']) ?>/88" class="w-20 h-20 rounded-2xl border-2 border-rose-100 flex-shrink-0">
                <div>
                    <h3 class="font-bold text-lg"><?= e($course['teacher']) ?></h3>
                    <p class="text-sm text-zinc-500 mt-1">مدرس <?= e($course['category']) ?> با بیش از ۱۰ سال سابقه تدریس و فعالیت حرفه‌ای در سالن‌های زیبایی معتبر.</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="text-xs bg-zinc-100 text-zinc-600 px-3 py-1 rounded-full"><i class="fa-solid fa-star text-amber-400 ml-1"></i>امتیاز <?= number_format($course['rating'], 1) ?></span>
                        <span class="text-xs bg-zinc-100 text-zinc-600 px-3 py-1 rounded-full"><i class="fa-solid fa-user-group text-rose-400 ml-1"></i><?= number_format($course['students']) ?> دانشجو</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($reviews)) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4"><i class="fa-solid fa-star text-amber-400 ml-2"></i>نظرات دانشجویان</h2>
            <div class="flex items-center gap-6 mb-6 p-4 bg-gradient-to-l from-rose-50 to-amber-50 rounded-2xl">
                <div class="text-center">
                    <div class="text-4xl font-bold text-rose-600"><?= number_format($course['rating'], 1) ?></div>
                    <div class="text-amber-400 text-sm mt-1"><?= str_repeat('★', (int) round($course['rating'])) ?></div>
                    <div class="text-xs text-zinc-500 mt-1">از ۵</div>
                </div>
                <div class="flex-1 space-y-1">
                    <?php for ($i = 5; $i >= 1; $i--) : ?>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-6 text-zinc-500"><?= $i ?></span>
                        <div class="flex-1 h-2 bg-zinc-200 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: <?= $i === 5 ? '65' : ($i === 4 ? '25' : ($i === 3 ? '7' : ($i === 2 ? '2' : '1'))) ?>%"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="space-y-4">
                <?php foreach ($reviews as $review) : ?>
                <div class="p-4 bg-zinc-50 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-<?= e($review['color'] ?? 'rose') ?>-100 text-<?= e($review['color'] ?? 'rose') ?>-600 rounded-full flex items-center justify-center text-xs font-bold"><?= e($review['initial'] ?? '') ?></div>
                            <span class="font-medium text-sm"><?= e($review['name'] ?? '') ?></span>
                        </div>
                        <div class="text-amber-400 text-xs"><?= str_repeat('★', $review['rating'] ?? 5) ?><?= str_repeat('☆', 5 - ($review['rating'] ?? 5)) ?></div>
                    </div>
                    <p class="text-sm text-zinc-600 mt-2 leading-relaxed"><?= e($review['text'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($faqs)) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4"><i class="fa-solid fa-circle-question text-rose-500 ml-2"></i>سؤالات متداول</h2>
            <div class="space-y-3">
                <?php foreach ($faqs as $faq) : ?>
                <div class="faq-item border border-zinc-100 rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 hover:bg-zinc-50 transition-colors text-right">
                        <span class="font-medium text-sm"><?= e($faq['q'] ?? '') ?></span>
                        <i class="fa-solid fa-chevron-down text-xs text-zinc-400 transition-transform"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4 text-sm text-zinc-600 leading-relaxed">
                        <?= e($faq['a'] ?? '') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="lg:sticky lg:top-28 space-y-4 h-fit">
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm overflow-hidden">
            <div class="relative">
                <img src="/assets/images/<?= e($course['image']) ?>"
                     class="w-full h-48 object-cover"
                     onerror="this.src='/media/400/300/<?= e($course['id']) ?>'">
                <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                    <?php if (($course['video_type'] ?? 'upload') === 'youtube') : ?>
                    <a href="https://www.youtube.com/watch?v=<?= e(getYoutubeId($course['video_url'])) ?>" target="_blank" class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center text-rose-600 text-2xl hover:bg-white hover:scale-110 transition-all shadow-lg">
                        <i class="fa-solid fa-play mr-[-2px]"></i>
                    </a>
                    <?php elseif (($course['video_type'] ?? 'upload') === 'aparat') : ?>
                    <a href="<?= e($course['video_url']) ?>" target="_blank" class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center text-rose-600 text-2xl hover:bg-white hover:scale-110 transition-all shadow-lg">
                        <i class="fa-solid fa-play mr-[-2px]"></i>
                    </a>
                    <?php else : ?>
                    <button onclick="document.getElementById('preview-video').play()" class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center text-rose-600 text-2xl hover:bg-white hover:scale-110 transition-all shadow-lg">
                        <i class="fa-solid fa-play mr-[-2px]"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <?php if ($course['is_free']) : ?>
                    <span class="text-2xl font-bold text-emerald-600">رایگان</span>
                    <?php else : ?>
                    <div>
                        <?php if (($course['old_price'] ?? 0) > $course['price']) : ?>
                        <div class="text-sm text-zinc-400 line-through"><?= number_format($course['old_price'] ?? 0) ?> تومان</div>
                        <?php endif; ?>
                        <div class="text-2xl font-bold"><?= number_format($course['price']) ?> <span class="text-sm font-normal text-zinc-500">تومان</span></div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isEnrolled) : ?>
                <div class="w-full py-3.5 bg-emerald-100 text-emerald-700 rounded-2xl font-semibold text-center">
                    <i class="fa-solid fa-check-circle ml-2"></i>شما ثبت‌نام کرده‌اید
                </div>
                <?php elseif ($course['is_free']) : ?>
                <form action="/course/<?= e($course['slug'] ?: $course['id']) ?>/enroll" method="POST">
                    <?= csrf() ?>
                    <button type="submit" class="w-full py-3.5 bg-emerald-600 text-white rounded-2xl font-semibold hover:bg-emerald-700 transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-rocket ml-2"></i>ثبت‌نام رایگان
                    </button>
                </form>
                <?php else : ?>
                <button onclick="addCourseToCart(<?= $course['id'] ?>)" class="w-full py-3.5 bg-gradient-to-l from-rose-600 to-rose-500 text-white rounded-2xl font-semibold hover:shadow-lg hover:shadow-rose-200 transition-all active:scale-[0.98]">
                    <i class="fa-solid fa-cart-shopping ml-2"></i>افزودن به سبد خرید
                </button>
                <?php endif; ?>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center gap-3 text-sm text-zinc-600">
                        <i class="fa-solid fa-infinity text-rose-400 w-5 text-center"></i>
                        <span>دسترسی مادام‌العمر</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600">
                        <i class="fa-solid fa-certificate text-rose-400 w-5 text-center"></i>
                        <span>گواهی پایان دوره</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600">
                        <i class="fa-solid fa-clock text-rose-400 w-5 text-center"></i>
                        <span><?= e($course['duration']) ?> محتوای آموزشی</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600">
                        <i class="fa-solid fa-download text-rose-400 w-5 text-center"></i>
                        <span>امکان دانلود منابع</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600">
                        <i class="fa-solid fa-shield-halved text-rose-400 w-5 text-center"></i>
                        <span>پشتیبانی ۳۰ روزه</span>
                    </div>
                </div>

                <div class="mt-5 p-3 bg-emerald-50 rounded-xl text-center text-sm text-emerald-700">
                    <i class="fa-solid fa-check-circle ml-1"></i>
                    ضمانت بازگشت وجه تا ۳۰ روز
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-5">
            <h3 class="font-bold text-sm mb-3"><i class="fa-solid fa-headset text-rose-500 ml-1.5"></i>پشتیبانی دوره</h3>
            <div class="space-y-2.5 text-sm text-zinc-600">
                <div class="flex items-center gap-2"><span>💬</span> پاسخ به سؤالات در کمتر از ۲۴ ساعت</div>
                <div class="flex items-center gap-2"><span>📄</span> منابع تکمیلی قابل دانلود</div>
                <div class="flex items-center gap-2"><span>🎓</span> مشاوره رایگان شغلی</div>
            </div>
        </div>

        <?php if (!empty($related)) : ?>
        <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-5">
            <h3 class="font-bold text-sm mb-3">دوره‌های مرتبط</h3>
            <div class="space-y-3">
                <?php foreach ($related as $rel) : ?>
                <a href="/course/<?= e($rel['slug'] ?: $rel['id']) ?>" class="flex items-center gap-3 group">
                    <img src="/assets/images/<?= e($rel['image']) ?>"
                         class="w-16 h-16 rounded-xl object-cover flex-shrink-0"
                         onerror="this.src='/media/100/100/<?= e($rel['id']) ?>'">
                    <div class="min-w-0">
                        <div class="text-sm font-medium line-clamp-1 group-hover:text-rose-600 transition-colors"><?= e($rel['title']) ?></div>
                        <div class="text-xs text-zinc-400 mt-0.5"><?= e($rel['teacher']) ?></div>
                        <div class="text-xs font-semibold mt-0.5">
                            <?php if ($rel['is_free']) : ?>
                            <span class="text-emerald-600">رایگان</span>
                            <?php else : ?>
                                <?= number_format($rel['price']) ?> تومان
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-zinc-200 p-4 z-40">
    <div class="flex items-center justify-between">
        <div>
            <?php if ($course['is_free']) : ?>
            <span class="text-lg font-bold text-emerald-600">رایگان</span>
            <?php else : ?>
            <span class="text-lg font-bold"><?= number_format($course['price']) ?> <span class="text-xs font-normal text-zinc-500">تومان</span></span>
            <?php endif; ?>
        </div>
        <?php if ($isEnrolled) : ?>
        <span class="px-6 py-3 bg-emerald-100 text-emerald-700 rounded-2xl font-semibold text-sm">ثبت‌نام شده</span>
        <?php elseif ($course['is_free']) : ?>
        <form action="/course/<?= e($course['slug'] ?: $course['id']) ?>/enroll" method="POST" class="inline">
            <?= csrf() ?>
            <button type="submit" class="px-6 py-3 bg-gradient-to-l from-emerald-600 to-emerald-500 text-white rounded-2xl font-semibold text-sm active:scale-95 transition-transform">
                ثبت‌نام رایگان
            </button>
        </form>
        <?php else : ?>
        <button onclick="addCourseToCart(<?= $course['id'] ?>)" class="px-6 py-3 bg-gradient-to-l from-rose-600 to-rose-500 text-white rounded-2xl font-semibold text-sm active:scale-95 transition-transform">
            <i class="fa-solid fa-cart-shopping ml-1"></i>افزودن به سبد
        </button>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleModule(btn) {
    const module = btn.closest('.detail-tab-module');
    const lessons = module.querySelector('.module-lessons');
    const icon = btn.querySelector('.fa-chevron-down');
    lessons.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const icon = btn.querySelector('.fa-chevron-down');
    answer.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function addCourseToCart(courseId) {
    fetch('/shop/course/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'course_id=' + courseId + '&' + csrfParam()
    }).then(r => r.json()).then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success && d.cart_count !== undefined) {
            var el = document.getElementById('cart-count');
            if (el) el.textContent = d.cart_count;
        }
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
