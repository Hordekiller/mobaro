<section id="booking" class="bg-zinc-950 py-20 text-white relative overflow-hidden">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="grid lg:grid-cols-12 gap-16 items-center">
            <div class="lg:col-span-5">
                <div class="sticky top-28">
                    <span class="text-rose-400 text-sm tracking-[2px]">نوبت‌دهی آنلاین</span>
                    <h2 class="text-5xl font-semibold tracking-tighter text-white mt-2 mb-6">نوبت خود را همین حالا رزرو کنید</h2>
                    <p class="text-zinc-400 text-lg max-w-xs">انتخاب آرایشگر، خدمات و زمان مناسب در کمتر از ۳۰ ثانیه</p>

                    <div class="mt-12 border border-white/10 rounded-3xl p-8 bg-white/5">
                        <div class="flex items-center justify-between mb-8">
                            <div onclick="setBookingStep(0)"
                                 class="booking-step active-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-rose-400 flex items-center justify-center text-xs font-bold">۱</div>
                            <div class="flex-1 h-px bg-white/10 mx-3"></div>
                            <div onclick="setBookingStep(1)"
                                 class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-white/30 flex items-center justify-center text-xs font-bold">۲</div>
                            <div class="flex-1 h-px bg-white/10 mx-3"></div>
                            <div onclick="setBookingStep(2)"
                                 class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-white/30 flex items-center justify-center text-xs font-bold">۳</div>
                        </div>
                        <div id="booking-form-content"></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-zinc-900 rounded-3xl p-2 shadow-inner">
                    <div class="bg-zinc-800 rounded-3xl p-8">
                        <div class="flex justify-between text-xs mb-6">
                            <div class="font-medium">نوبت‌های امروز</div>
                            <div class="text-emerald-400">۴ نوبت باقی مانده</div>
                        </div>
                        <div class="space-y-6" id="today-appointments">
                            <!-- Loaded via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 right-12 text-[180px] font-black text-white/5 logo-font select-none pointer-events-none">
        <?= e($settings['brand_name'] ?? 'موبارو') ?>
    </div>
</section>
