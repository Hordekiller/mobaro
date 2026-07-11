<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">دیدگاه‌های وبلاگ</h1>
    <p class="text-[#9e9e9e] text-sm">دیدگاه‌هایی که در پست‌های وبلاگ ثبت کرده‌اید</p>
</div>

<?php if (!empty($blogComments)): ?>
    <?php foreach ($blogComments as $comment): ?>
    <div class="bg-white rounded-[18px] p-5 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-3.5">
        <div class="flex items-start gap-4">
            <div class="w-[52px] h-[52px] rounded-xl bg-[#FDF6F0] flex items-center justify-center text-[#B76E79] text-lg flex-shrink-0">
                <i class="fa-regular fa-comment"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                    <div>
                        <a href="/blog/<?= e($comment['post_slug'] ?? 'post-' . $comment['post_id']) ?>" class="font-bold hover:text-[#B76E79] transition-colors"><?= e($comment['post_title']) ?></a>
                        <span class="text-[#9e9e9e] text-xs mr-2"><?= jdate('Y/m/d', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $comment['is_approved'] ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' ?>">
                        <?= $comment['is_approved'] ? 'تأیید شده' : 'در انتظار تأیید' ?>
                    </span>
                </div>
                <p class="text-zinc-600 text-sm leading-relaxed"><?= e($comment['text']) ?></p>
                <?php if ($comment['likes'] > 0): ?>
                <div class="mt-2 text-xs text-[#9e9e9e]">
                    <i class="fa-regular fa-heart ml-1"></i><?= $comment['likes'] ?> لایک
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center py-12 text-[#9e9e9e]">
        <i class="fa-regular fa-comment text-5xl mb-4"></i>
        <p>دیدگاهی ثبت نکرده‌اید</p>
        <a href="/blog" class="inline-block mt-4 px-6 py-3 bg-[#B76E79] text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">مشاهده وبلاگ</a>
    </div>
<?php endif; ?>
