<?php

declare(strict_types=1);

?>
<?php if (!empty($latestPosts)) : ?>
<section id="blog" class="py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="font-semibold text-5xl tracking-tighter">جدیدترین مقالات مجله زیبایی</h2>
            <a href="/blog" class="text-rose-500 hover:text-rose-600 font-medium flex items-center gap-2 transition-colors">
                مشاهده همه مطالب
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($latestPosts as $post) : ?>
            <article class="group bg-white rounded-3xl overflow-hidden border border-transparent hover:border-rose-200 transition-colors">
                <a href="/blog/<?= e($post['slug']) ?>" class="block">
                    <div class="relative overflow-hidden">
                        <img src="/assets/images/<?= e($post['image'] ?: 'placeholder.svg') ?>"
                             alt="<?= e($post['image_alt'] ?? $post['title']) ?>"
                             class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy" decoding="async">
                        <?php if (!empty($post['category'])) : ?>
                        <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-bold text-rose-600 shadow-md">
                            <?= e($post['category']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 text-xs text-zinc-400">
                            <span><i class="far fa-calendar-alt ml-1"></i><?= e(jdate('Y/m/d', strtotime((string) $post['published_at']))) ?></span>
                            <span><i class="far fa-clock ml-1"></i><?= e($post['reading_time']) ?> دقیقه مطالعه</span>
                        </div>
                        <h3 class="font-semibold mt-3 group-hover:text-rose-500 transition-colors line-clamp-2"><?= e($post['title']) ?></h3>
                        <p class="mt-2 text-sm text-zinc-500 line-clamp-2"><?= e($post['excerpt']) ?></p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>