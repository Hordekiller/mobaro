<?php $title = 'رزرو نوبت | موبارو'; ?>
<div id="booking" class="min-h-screen bg-gradient-to-br from-rose-50 to-white pt-24">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5">
                <div class="sticky top-28">
                    <span class="text-rose-500 text-sm tracking-[2px] font-medium">نوبت‌دهی آنلاین</span>
                    <h2 class="text-3xl font-bold text-zinc-800 mt-2 mb-3">نوبت خود را رزرو کنید</h2>
                    <p class="text-zinc-500 text-sm max-w-xs leading-relaxed">خدمت مورد نظر، آرایشگر دلخواه و زمان مناسب را در چند مرحله ساده انتخاب کنید.</p>

                    <div class="mt-8 bg-white rounded-2xl p-6 shadow-lg border border-zinc-100">
                        <div class="flex items-center justify-between mb-6">
                            <div onclick="setBookingStep(0)"
                                 class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2 border-rose-500 bg-rose-500 text-white">۱</div>
                            <div class="flex-1 h-px bg-zinc-200 mx-2"></div>
                            <div onclick="setBookingStep(1)"
                                 class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2 border-zinc-300 text-zinc-400">۲</div>
                            <div class="flex-1 h-px bg-zinc-200 mx-2"></div>
                            <div onclick="setBookingStep(2)"
                                 class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2 border-zinc-300 text-zinc-400">۳</div>
                        </div>
                        <div id="booking-form-content">
                            <div class="text-center py-12 text-zinc-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                                <p class="mt-3 text-sm">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-zinc-100">
                    <div class="flex justify-between items-center mb-6">
                        <div class="font-semibold text-zinc-700 text-sm">نوبت‌های امروز</div>
                        <div class="text-emerald-600 text-xs font-medium bg-emerald-50 px-3 py-1.5 rounded-full">در حال بارگذاری...</div>
                    </div>
                    <div class="space-y-3" id="today-appointments">
                        <div class="text-center py-12 text-zinc-300">
                            <i class="fa-regular fa-calendar text-4xl mb-3"></i>
                            <p class="text-sm">در حال بارگذاری نوبت‌های امروز...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window._mobaroArtists = <?= $artistsJson ?? '[]' ?>;
</script>
