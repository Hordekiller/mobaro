<section id="education" class="bg-gradient-to-br from-zinc-100 to-white py-20">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="grid md:grid-cols-12 gap-16">
            <div class="md:col-span-5">
                <div>
                    <span class="uppercase text-xs tracking-widest font-medium text-rose-500">یاد بگیرید • رشد کنید</span>
                    <h2 class="text-3xl md:text-5xl font-semibold leading-none tracking-tighter mt-4">آموزش‌های رایگان زیبایی</h2>
                    <p class="mt-6 text-zinc-600">ویدیوهای آموزشی کوتاه برای یادگیری آرایش، مراقبت از مو و پوست در خانه</p>
                    <a href="/academy" class="mt-8 inline-flex items-center gap-x-4 group">
                        <div class="w-16 h-16 bg-rose-600 text-white rounded-3xl flex items-center justify-center text-4xl shadow-inner group-active:scale-95 transition-transform">▶</div>
                        <div class="text-left">
                            <div class="font-medium">مشاهده دوره‌ها</div>
                            <div class="text-xs text-zinc-500">ورود به آکادمی موبارو</div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="md:col-span-7">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($educationCourses as $course) : ?>
                    <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>" class="group bg-white border border-transparent hover:border-rose-200 rounded-3xl overflow-hidden">
                        <div class="relative">
                            <img src="/assets/images/<?= e($course['image']) ?>"
                                 class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-500"
                                 onerror="this.src='/media/600/340/<?= e($course['id']) ?>'">
                            <div class="absolute bottom-4 left-4 bg-black/70 text-white text-[10px] px-3 py-1 rounded-3xl flex items-center gap-x-2">
                                <i class="fa-solid fa-clock"></i>
                                <span><?= e($course['duration']) ?></span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between text-xs">
                                <span class="text-emerald-500"><?= e($course['category']) ?></span>
                                <span class="text-zinc-400"><?= number_format($course['students']) ?> دانشجو</span>
                            </div>
                            <h4 class="font-semibold mt-2 group-hover:text-rose-500 transition-colors"><?= e($course['title']) ?></h4>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-8 text-center">
                    <a href="/academy" class="inline-flex items-center gap-2 bg-white border border-zinc-200 hover:border-rose-300 hover:text-rose-600 px-6 py-3 rounded-2xl text-sm font-medium transition-all">
                        <i class="fa-solid fa-graduation-cap"></i>
                        مشاهده تمام دوره‌ها در آکادمی
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
