<section id="services" class="py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <span class="px-5 py-1.5 text-xs font-semibold bg-rose-100 text-rose-600 rounded-3xl">خدمات ما</span>
                <h2 class="text-5xl font-semibold tracking-tighter mt-3">خدمات زیبایی حرفه‌ای</h2>
            </div>
            <a href="/#booking" class="hidden md:flex items-center gap-x-3 text-sm font-medium group">
                <span class="group-hover:underline">همه خدمات</span>
                <div class="w-8 h-8 bg-rose-100 text-rose-500 rounded-2xl flex items-center justify-center">→</div>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($services as $index => $service): ?>
            <div onclick="selectService(<?= $index ?>)" class="service-card bg-white border border-zinc-100 rounded-3xl overflow-hidden cursor-pointer">
                <div class="h-64 bg-cover bg-center relative"
                     style="background-image: url('/assets/images/<?= e($service['image'] ?? '') ?>')">
                    <div class="absolute top-4 right-4 bg-white text-xs font-bold px-4 py-2 rounded-3xl shadow"><?= e($service['category']) ?></div>
                </div>
                <div class="p-7">
                    <div class="flex justify-between items-baseline">
                        <div class="font-semibold text-2xl"><?= e($service['title']) ?></div>
                        <div class="text-rose-500 font-bold"><?= number_format($service['price'] / 1000) ?></div>
                    </div>
                    <div class="text-zinc-500 text-sm mt-1"><?= e($service['description']) ?></div>
                    <div class="mt-8 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-x-1">
                            <i class="fa-solid fa-clock text-zinc-400"></i>
                            <span class="text-zinc-500"><?= e($service['duration']) ?></span>
                        </div>
                        <div class="text-emerald-500 font-medium"><?= e($service['rating']) ?> ★</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
