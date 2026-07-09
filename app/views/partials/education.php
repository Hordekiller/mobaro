<section id="education" class="bg-gradient-to-br from-zinc-100 to-white py-20">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="grid md:grid-cols-12 gap-16">
            <div class="md:col-span-5">
                <div class="max-w-xs">
                    <span class="uppercase text-xs tracking-widest font-medium text-rose-500">یاد بگیرید • رشد کنید</span>
                    <h2 class="text-5xl font-semibold leading-none tracking-tighter mt-4">آموزش‌های رایگان زیبایی</h2>
                    <p class="mt-6 text-zinc-600">ویدیوهای آموزشی کوتاه برای یادگیری آرایش، مراقبت از مو و پوست در خانه</p>
                    <button onclick="watchFeaturedVideo()" class="mt-8 flex items-center gap-x-4 group">
                        <div class="w-16 h-16 bg-rose-600 text-white rounded-3xl flex items-center justify-center text-4xl shadow-inner group-active:scale-95 transition-transform">▶</div>
                        <div class="text-left">
                            <div class="font-medium">شروع آموزش</div>
                            <div class="text-xs text-zinc-500">آرایش چشم در ۴ دقیقه</div>
                        </div>
                    </button>
                </div>
            </div>
            <div class="md:col-span-7">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($tutorials as $index => $tutorial): ?>
                    <div onclick="openTutorial(<?= $index ?>)" class="group bg-white border border-transparent hover:border-rose-200 rounded-3xl overflow-hidden cursor-pointer">
                        <div class="relative">
                            <img src="/assets/images/<?= e($tutorial['image']) ?>"
                                 class="w-full h-52 object-cover gallery-img"
                                 onerror="this.src='https://picsum.photos/seed/tut<?= e($tutorial['id']) ?>/600/340'">
                            <div class="absolute bottom-4 left-4 bg-black/70 text-white text-[10px] px-3 py-1 rounded-3xl flex items-center gap-x-2">
                                <i class="fa-solid fa-video"></i>
                                <span><?= e($tutorial['duration']) ?></span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between text-xs">
                                <span class="text-emerald-500"><?= e($tutorial['category']) ?></span>
                                <span class="text-zinc-400"><?= number_format($tutorial['views']) ?> بازدید</span>
                            </div>
                            <h4 class="font-semibold mt-2 group-hover:text-rose-500 transition-colors"><?= e($tutorial['title']) ?></h4>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
