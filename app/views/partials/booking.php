<section id="booking" class="bg-zinc-950 py-20 text-white relative overflow-hidden">
    <div class="max-w-2xl mx-auto px-8">
        <div class="text-center mb-8">
            <span class="text-rose-400 text-sm tracking-[2px]">نوبت‌دهی آنلاین</span>
            <h2 class="text-5xl font-semibold tracking-tighter text-white mt-2 mb-3">نوبت خود را همین حالا رزرو کنید</h2>
            <p class="text-zinc-400 text-lg">انتخاب آرایشگر، خدمات و زمان مناسب در کمتر از ۳۰ ثانیه</p>
        </div>

        <div class="border border-white/10 rounded-3xl p-8 bg-white/5">
            <div class="flex items-center justify-between mb-8">
                <div onclick="setBookingStep(0)"
                     class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 flex items-center justify-center text-xs font-bold">۱</div>
                <div class="flex-1 h-px bg-white/10 mx-3"></div>
                <div onclick="setBookingStep(1)"
                     class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 flex items-center justify-center text-xs font-bold">۲</div>
                <div class="flex-1 h-px bg-white/10 mx-3"></div>
                <div onclick="setBookingStep(2)"
                     class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 flex items-center justify-center text-xs font-bold">۳</div>
                <div class="flex-1 h-px bg-white/10 mx-3"></div>
                <div onclick="setBookingStep(3)"
                     class="booking-step cursor-pointer w-9 h-9 rounded-2xl border-2 flex items-center justify-center text-xs font-bold">۴</div>
            </div>
            <div id="booking-form-content" data-theme="dark">
                <div class="text-center py-12 text-zinc-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                    <p class="mt-3 text-sm">در حال بارگذاری...</p>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 right-12 text-[180px] font-black text-white/5 logo-font select-none pointer-events-none">
        <?= e($settings['brand_name'] ?? 'موبارو') ?>
    </div>
</section>

<?php
// Generate captcha for the booking partial
$captchaKey = 'captcha_' . rand(1, 10);
$expression = $settings[$captchaKey] ?? '5+3';
$expression = trim($expression);
if (preg_match('/^(\d+)\s*([\+-])\s*(\d+)$/', $expression, $m)) {
    $a = (int)$m[1]; $op = $m[2]; $b = (int)$m[3];
    $answer = $op === '+' ? $a + $b : $a - $b;
    $persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $captchaQuestion = str_replace(range(0,9), $persianDigits, $expression);
    $_SESSION['captcha_answer'] = $answer;
} else {
    $captchaQuestion = '۵ + ۳';
    $_SESSION['captcha_answer'] = 8;
}
$artistsJson = json_encode(array_map(fn($a) => [
    'id' => $a['id'],
    'name' => $a['name'],
    'specialty' => $a['specialty'],
    'avatar' => $a['avatar'] ?? '',
    'working_hours' => $a['working_hours'] ?? '۹ صبح - ۸ شب',
    'bio' => $a['bio'] ?? '',
], $artists ?? []), JSON_UNESCAPED_UNICODE);
?>
<script>
window._mobaroArtists = <?= $artistsJson ?>;
window._mobaroCaptchaQuestion = '<?= $captchaQuestion ?>';
</script>
