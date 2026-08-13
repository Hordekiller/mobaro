<?php

declare(strict_types=1);

$title = 'سؤالات متداول | ' . ($settings['brand_name'] ?? 'موبارو');
?>

<section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-rose-600 text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-screen-2xl mx-auto px-8 py-16 md:py-24">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-x-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm mb-6">
                <i class="fa-solid fa-circle-question text-amber-300"></i>
                پاسخ به پرتکرارترین سؤالات
            </div>
            <h1 class="text-3xl md:text-5xl font-bold leading-tight tracking-tight">سؤالات متداول</h1>
            <p class="mt-4 text-white/80 leading-relaxed">
                پاسخ سؤالات پرتکرار درباره رزرو نوبت، دوره‌های آکادمی، خرید از فروشگاه و روش‌های پرداخت را در اینجا ببینید. اگر پاسخ سؤال خود را پیدا نکردید، با پشتیبانی در تماس باشید.
            </p>
        </div>
    </div>
</section>

<section class="max-w-screen-2xl mx-auto px-8 py-12 md:py-16">
    <?php if (empty($grouped)) : ?>
    <div class="bg-white rounded-3xl border border-zinc-100 shadow-sm p-16 text-center">
        <div class="text-5xl mb-4"><i class="fa-solid fa-circle-question text-zinc-200"></i></div>
        <h2 class="text-xl font-bold text-zinc-800 mb-2">هنوز سؤالی ثبت نشده است</h2>
        <p class="text-zinc-500 text-sm mb-6">سؤالات متداول به‌زودی در این بخش قرار می‌گیرند.</p>
        <a href="/contact" class="inline-block px-6 py-3 bg-rose-600 text-white rounded-2xl font-semibold hover:bg-rose-700 transition-all">
            <i class="fa-solid fa-headset ml-1.5"></i>تماس با پشتیبانی
        </a>
    </div>
    <?php else : ?>
    <?php
    $allCategories = array_keys($grouped);
    ?>
    <div class="flex flex-wrap gap-2 mb-8" id="faq-category-chips">
        <button type="button" data-category="all" onclick="filterFaq('all', this)" class="px-4 py-2 rounded-full text-sm font-medium border transition-all bg-rose-600 text-white border-rose-600">
            همه
        </button>
        <?php foreach ($allCategories as $category) : ?>
        <button type="button" data-category="<?= e($category) ?>" onclick="filterFaq('<?= e($category) ?>', this)" class="px-4 py-2 rounded-full text-sm font-medium border border-zinc-200 text-zinc-600 hover:border-rose-300 hover:text-rose-600 transition-all">
            <?= e($category) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="space-y-10 max-w-4xl">
        <?php foreach ($grouped as $category => $items) : ?>
        <div data-category-group="<?= e($category) ?>" class="faq-category-group">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-sm">
                    <i class="fa-solid fa-tag"></i>
                </span>
                <?= e($category) ?>
            </h2>
            <div class="space-y-3">
                <?php foreach ($items as $faq) : ?>
                <div class="faq-item bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between gap-4 p-5 hover:bg-zinc-50 transition-colors text-right">
                        <span class="font-medium text-sm leading-relaxed"><?= e($faq['question'] ?? '') ?></span>
                        <i class="fa-solid fa-chevron-down text-xs text-zinc-400 transition-transform flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-5 pb-5 text-sm text-zinc-600 leading-relaxed border-t border-zinc-100 pt-4">
                        <?= e($faq['answer'] ?? '') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-12 bg-gradient-to-br from-rose-50 to-indigo-50 rounded-3xl border border-zinc-100 p-8 max-w-4xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-zinc-800">سؤال دیگری دارید؟</h2>
                <p class="text-sm text-zinc-500 mt-1">تیم پشتیبانی موبارو آماده پاسخگویی به سؤالات شماست.</p>
            </div>
            <a href="/contact" class="inline-flex items-center justify-center px-6 py-3 bg-rose-600 text-white rounded-2xl font-semibold hover:bg-rose-700 transition-all whitespace-nowrap">
                <i class="fa-solid fa-headset ml-1.5"></i>ارسال پیام به پشتیبانی
            </a>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const icon = btn.querySelector('.fa-chevron-down');
    answer.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function filterFaq(category, btn) {
    var chips = document.querySelectorAll('#faq-category-chips button');
    chips.forEach(function (chip) {
        var active = chip === btn;
        if (active) {
            chip.className = 'px-4 py-2 rounded-full text-sm font-medium border transition-all bg-rose-600 text-white border-rose-600';
        } else {
            chip.className = 'px-4 py-2 rounded-full text-sm font-medium border border-zinc-200 text-zinc-600 hover:border-rose-300 hover:text-rose-600 transition-all';
        }
    });

    var groups = document.querySelectorAll('.faq-category-group');
    groups.forEach(function (group) {
        var groupCategory = group.getAttribute('data-category-group');
        if (category === 'all' || groupCategory === category) {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });
}
</script>
