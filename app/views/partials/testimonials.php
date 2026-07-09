<section class="py-20 bg-zinc-900 text-white">
    <div class="max-w-screen-2xl mx-auto px-8">
        <div class="text-center mb-16">
            <span class="text-rose-400">نظرات مشتریان</span>
        </div>
        <div class="max-w-3xl mx-auto text-center">
            <?php if (!empty($testimonials)): ?>
            <div id="testimonial-text" class="text-3xl leading-tight font-light text-zinc-200 italic">
                “<?= e($testimonials[0]['text']) ?>”
            </div>
            <div class="flex justify-center gap-x-4 items-center mt-12">
                <div class="w-9 h-px bg-white/30"></div>
                <div class="text-center">
                    <div id="testimonial-name" class="font-semibold"><?= e($testimonials[0]['name']) ?></div>
                    <div id="testimonial-role" class="text-xs text-rose-300"><?= e($testimonials[0]['role']) ?></div>
                </div>
                <div class="w-9 h-px bg-white/30"></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="flex justify-center gap-x-6 mt-16">
            <button onclick="prevTestimonial()" class="w-12 h-12 border border-white/30 hover:border-white text-white rounded-2xl flex items-center justify-center text-xl transition-colors">‹</button>
            <button onclick="nextTestimonial()" class="w-12 h-12 border border-white/30 hover:border-white text-white rounded-2xl flex items-center justify-center text-xl transition-colors">›</button>
        </div>
    </div>
</section>

<script>
const testimonials = <?= json_encode($testimonials, JSON_UNESCAPED_UNICODE) ?>;
let currentTestimonial = 0;
</script>
