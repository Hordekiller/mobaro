<?php $title = 'رزرو نوبت | موبارو'; ?>
<div id="booking" class="min-h-screen bg-gradient-to-br from-rose-50 to-white pt-24">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <span class="text-rose-500 text-sm tracking-[2px] font-medium">نوبت‌دهی آنلاین</span>
                <h2 class="text-3xl font-bold text-zinc-800 mt-2">نوبت خود را رزرو کنید</h2>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-zinc-100">
                <div class="flex items-center justify-between mb-6">
                    <div onclick="setBookingStep(0)"
                         class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2">۱</div>
                    <div class="flex-1 h-px bg-zinc-200 mx-2"></div>
                    <div onclick="setBookingStep(1)"
                         class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2">۲</div>
                    <div class="flex-1 h-px bg-zinc-200 mx-2"></div>
                    <div onclick="setBookingStep(2)"
                         class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2">۳</div>
                    <div class="flex-1 h-px bg-zinc-200 mx-2"></div>
                    <div onclick="setBookingStep(3)"
                         class="booking-step cursor-pointer w-8 h-8 rounded-xl text-xs font-bold flex items-center justify-center transition-all border-2">۴</div>
                </div>
                <div id="booking-form-content" data-theme="light">
                    <div class="text-center py-12 text-zinc-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                        <p class="mt-3 text-sm">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window._mobaroArtists = <?= $artistsJson ?? '[]' ?>;
window._mobaroCaptchaQuestion = '<?= $captchaQuestion ?? '۵ + ۳' ?>';
window._mobaroCaptchaEnabled = <?= ($captchaEnabled ?? true) ? 'true' : 'false' ?>;
</script>
