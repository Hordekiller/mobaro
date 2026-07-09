<?php $title = 'رزرو نوبت | موبارو'; ?>
<div id="booking" class="min-h-screen bg-[#FDF6F0] pt-24">
    <div class="max-w-screen-2xl mx-auto px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-16 items-start">
            <div class="lg:col-span-5">
                <div class="sticky top-28">
                    <span class="text-[#B76E79] text-sm tracking-[2px]">نوبت‌دهی آنلاین</span>
                    <h2 class="text-4xl font-extrabold text-zinc-800 mt-2 mb-6">نوبت خود را همین حالا رزرو کنید</h2>
                    <p class="text-zinc-500 max-w-xs">انتخاب آرایشگر، خدمات و زمان مناسب در کمتر از ۳۰ ثانیه</p>

                    <div class="mt-8 bg-white rounded-3xl p-6 shadow-[0_4px_30px_rgba(183,110,121,0.08)]">
                        <div class="flex items-center justify-between mb-6">
                            <div onclick="setBookingStep(0)"
                                 class="booking-step active-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-[#B76E79] bg-[#B76E79] text-white flex items-center justify-center text-xs font-bold">۱</div>
                            <div class="flex-1 h-px bg-zinc-200 mx-3"></div>
                            <div onclick="setBookingStep(1)"
                                 class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-zinc-300 flex items-center justify-center text-xs font-bold text-zinc-400">۲</div>
                            <div class="flex-1 h-px bg-zinc-200 mx-3"></div>
                            <div onclick="setBookingStep(2)"
                                 class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 border-zinc-300 flex items-center justify-center text-xs font-bold text-zinc-400">۳</div>
                        </div>
                        <div id="booking-form-content">
                            <div class="text-center py-8 text-zinc-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                                <p class="mt-3 text-sm">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_30px_rgba(183,110,121,0.08)]">
                    <div class="flex justify-between text-sm mb-6">
                        <div class="font-semibold text-zinc-700">نوبت‌های امروز</div>
                        <div class="text-emerald-500 text-xs font-medium bg-emerald-50 px-3 py-1 rounded-full">۴ نوبت باقی مانده</div>
                    </div>
                    <div class="space-y-4" id="today-appointments">
                        <div class="text-center py-12 text-zinc-300">
                            <i class="fa-solid fa-calendar-day text-4xl mb-3"></i>
                            <p class="text-sm">برای مشاهده نوبت‌های امروز یک سرویس انتخاب کنید</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
