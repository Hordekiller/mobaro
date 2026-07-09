<header id="home" class="hero-bg min-h-screen flex items-center pt-16">
    <div class="max-w-screen-2xl mx-auto px-8 grid md:grid-cols-2 gap-12 items-center h-full w-full">
        <div class="text-white pt-12 md:pt-0">
            <div class="inline-flex items-center gap-x-2 bg-white/20 backdrop-blur-md px-6 py-2 rounded-3xl text-sm mb-6">
                <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="font-medium">باز شده از ساعت ۹ صبح</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-bold leading-none tracking-tighter logo-font mb-4">
                <?= e($settings['hero_title'] ?? 'زیبایی را<br>با ما تجربه کنید') ?>
            </h1>

            <p class="max-w-md text-lg text-white/90 mb-10">
                <?= e($settings['hero_description'] ?? 'سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای در خدمت شماست. رزرو آنلاین، آموزش‌های رایگان و فروشگاه آنلاین.') ?>
            </p>

            <div class="flex items-center gap-x-4">
                <a href="/#booking"
                   class="flex-1 md:flex-none bg-white text-rose-600 hover:bg-amber-100 px-6 py-4 md:px-10 md:py-6 rounded-3xl font-bold text-xl shadow-2xl shadow-rose-500/30 transition-all active:scale-95 text-center">
                    رزرو نوبت
                </a>
                <a href="/#models"
                   class="flex-1 md:flex-none border-2 border-white/80 hover:border-white px-6 py-4 md:px-8 md:py-6 rounded-3xl font-semibold text-lg transition-all text-center">
                    مشاهده مدل‌ها
                </a>
            </div>

            <div class="mt-16 flex items-center gap-x-8 text-sm">
                <div class="flex items-center">
                    <div class="flex -space-x-4">
                        <div class="w-7 h-7 bg-white rounded-2xl border-2 border-rose-500 flex items-center justify-center text-[10px] font-bold text-rose-600">۱</div>
                        <div class="w-7 h-7 bg-white rounded-2xl border-2 border-rose-500 flex items-center justify-center text-[10px] font-bold text-rose-600">۲</div>
                        <div class="w-7 h-7 bg-white rounded-2xl border-2 border-rose-500 flex items-center justify-center text-[10px] font-bold text-rose-600">۳</div>
                    </div>
                    <span class="mr-4 text-white/80 text-xs leading-tight">بیش از ۴۲۰ مشتری<br>راضی امروز</span>
                </div>
                <div class="h-10 w-px bg-white/30"></div>
                <div>
                    <div class="flex text-amber-400">★★★★☆</div>
                    <span class="text-xs text-white/70">۴.۹۸ از ۵</span>
                </div>
            </div>
        </div>

        <div class="hidden md:flex justify-end relative">
            <div class="relative">
                <div class="absolute -left-6 -top-6 bg-white rounded-3xl p-5 shadow-2xl z-10">
            <div class="flex items-center gap-x-2 md:gap-x-4">
                        <div class="text-emerald-500"><i class="fa-solid fa-check-circle text-4xl"></i></div>
                        <div>
                            <div class="font-semibold text-zinc-800">نوبت شما ثبت شد</div>
                            <div class="text-xs text-zinc-500">امروز ساعت ۱۴:۳۰</div>
                        </div>
                    </div>
                </div>
                <img src="/assets/images/hero-model.jpg" alt="مدل مو"
                     class="w-80 h-[520px] object-cover rounded-[4rem] shadow-2xl ring-8 ring-white/60"
                     onerror="this.src='https://picsum.photos/id/1005/520/620'">
                <div class="absolute -bottom-4 -right-4 bg-white rounded-3xl px-6 py-4 shadow-2xl flex items-center gap-x-3">
                    <div class="text-rose-500"><i class="fa-solid fa-heart text-3xl"></i></div>
                    <div class="text-xs leading-tight">
                        <div class="font-bold text-zinc-700">سارا احمدی</div>
                        <div class="text-rose-500">آرایش عروس</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-12 left-1/2 flex flex-col items-center gap-y-1 text-white/60 text-xs">
        <div class="animate-bounce"><i class="fa-solid fa-chevron-down"></i></div>
        <span class="tracking-widest text-[10px]">پایین</span>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md py-5 border-t border-white">
        <div class="max-w-screen-2xl mx-auto px-8 flex flex-wrap justify-center items-center gap-x-12 text-xs text-zinc-500 font-medium">
            <div class="flex items-center gap-x-2"><i class="fa-solid fa-shield-halved"></i><span>ضمانت کیفیت</span></div>
            <div class="flex items-center gap-x-2"><i class="fa-solid fa-clock"></i><span>نوبت‌دهی ۲۴ ساعته</span></div>
            <div class="flex items-center gap-x-2"><i class="fa-solid fa-wand-magic-sparkles"></i><span>محصولات اورجینال</span></div>
            <div class="flex items-center gap-x-2"><i class="fa-solid fa-users"></i><span>آرایشگران حرفه‌ای</span></div>
        </div>
    </div>
</header>
