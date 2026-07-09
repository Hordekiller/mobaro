/**
 * Mobaro Frontend JS
 */

// ========== TOAST NOTIFICATION ==========
function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toast-container');
    if (!container) return;
    container.classList.remove('hidden');

    const toastHTML = `
        <div class="toast flex items-center gap-x-3 bg-white shadow-2xl border border-zinc-100 text-zinc-700 px-5 py-4 rounded-3xl mb-3">
            <div class="${type === 'success' ? 'text-emerald-500' : 'text-rose-500'}">
                ${type === 'success'
                    ? '<i class="fa-solid fa-circle-check"></i>'
                    : '<i class="fa-solid fa-circle-exclamation"></i>'}
            </div>
            <div class="text-sm font-medium">${message}</div>
        </div>
    `;
    container.innerHTML = toastHTML;

    setTimeout(() => {
        container.classList.add('hidden');
        container.innerHTML = '';
    }, 3000);
}

// ========== MOBILE MENU ==========
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.toggle('hidden');
}

// ========== SCROLL NAVIGATION ==========
function navigateToSection(section) {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.add('hidden');

    if (section === 'booking') {
        const el = document.getElementById('booking');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
            setTimeout(() => { if (typeof renderBookingStep === 'function') renderBookingStep(); }, 800);
        }
        return;
    }

    const el = document.getElementById(section);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

// ========== SCROLL PROGRESS ==========
(function setupProgressBar() {
    window.addEventListener('scroll', function() {
        const bar = document.getElementById('progress-bar');
        if (!bar) return;
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        bar.style.width = docHeight > 0 ? (scrollTop / docHeight * 100) + '%' : '0%';
    });
})();

// ========== SUBSCRIBE NEWSLETTER ==========
function subscribeNewsletter() {
    const input = document.getElementById('newsletter-input');
    if (!input) return;
    const val = input.value.trim();
    if (!val) { showToast('لطفا ایمیل یا شماره تماس را وارد کنید', 'error'); return; }

    fetch('/newsletter/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'contact=' + encodeURIComponent(val)
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) input.value = '';
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

// ========== CART FUNCTIONS ==========
function addToCart(productId, name, price, image, category) {
    fetch('/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('cart-count').innerText = d.count;
            showToast(d.message || name + ' به سبد خرید اضافه شد');
        } else {
            showToast(d.message || 'خطا در افزودن به سبد خرید', 'error');
        }
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

function quickAddToCart(productId, name, price, image, category) {
    addToCart(productId, name, price, image, category);
}

function addToCartFromShop(productId, name, price, image, category) {
    addToCart(productId, name, price, image, category);
}

// ========== WISHLIST ==========
function toggleWishlist(productId, btn) {
    fetch('/dashboard/wishlist/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success && btn) {
            btn.classList.toggle('text-red-500');
            btn.classList.toggle('text-gray-300');
        }
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

// ========== LIKE HAIR MODEL ==========
function likeModel(id) {
    event && event.stopImmediatePropagation();
    fetch('/api/like-model', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'model_id=' + id
    })
    .then(r => r.json())
    .then(d => showToast(d.message, d.success ? 'success' : 'error'))
    .catch(() => showToast('لایک ثبت شد ❤️'));
}

// ========== BOOKING ==========
let currentBookingStep = 0;
let selectedServiceId = null;

function selectService(serviceId) {
    selectedServiceId = serviceId;
    document.querySelectorAll('#booking .booking-step').forEach((el, i) => {
        el.classList.toggle('active-step', i === 0);
    });
    navigateToSection('booking');
    currentBookingStep = 0;
    setTimeout(renderBookingStep, 900);
}

function renderBookingStep() {
    const container = document.getElementById('booking-form-content');
    if (!container) return;

    if (currentBookingStep === 0) {
        // Step 1: Select service
        container.innerHTML = `
            <div class="text-xs font-medium text-zinc-400 mb-4">انتخاب خدمت</div>
            <div class="grid grid-cols-2 gap-3 text-sm" id="service-select-grid"></div>
            <button onclick="nextBookingStep()"
                    class="mt-10 w-full py-6 text-lg font-semibold border border-white/30 hover:bg-white/10 transition-colors rounded-3xl">ادامه ←</button>
        `;

        const grid = document.getElementById('service-select-grid');
        if (window._mobaroServices) {
            window._mobaroServices.forEach((service, i) => {
                const el = document.createElement('div');
                el.className = `px-5 py-6 border ${selectedServiceId == service.id ? 'border-rose-400 bg-rose-50' : 'border-white/10'} rounded-3xl cursor-pointer transition-all`;
                el.innerHTML = `
                    <div class="font-medium">${service.title}</div>
                    <div class="text-xs text-zinc-400">${service.duration}</div>
                    <div class="text-rose-400 font-semibold text-xl mt-5">${service.price.toLocaleString('fa-IR')}</div>
                `;
                el.onclick = function() {
                    selectedServiceId = service.id;
                    renderBookingStep();
                };
                grid.appendChild(el);
            });
        }
    } else if (currentBookingStep === 1) {
        // Step 2: Date & time - fetch available slots via AJAX
        container.innerHTML = `
            <div>
                <div class="text-xs font-medium text-zinc-400 mb-3">تاریخ و ساعت</div>
                <div class="grid grid-cols-5 gap-2 mb-6" id="date-picker"></div>
                <div class="grid grid-cols-3 gap-3 text-xs" id="time-slots">
                    <div class="text-center py-8 text-zinc-500">لطفاً ابتدا تاریخ را انتخاب کنید</div>
                </div>
            </div>
            <div class="flex items-center gap-4 mt-14">
                <button onclick="prevBookingStep()" class="flex-1 py-5 border border-white/30 text-white rounded-3xl">قبلی</button>
                <button onclick="confirmBooking()" class="flex-1 py-5 bg-white text-zinc-900 font-semibold rounded-3xl">تأیید نوبت</button>
            </div>
        `;

        // Render date options
        const datePicker = document.getElementById('date-picker');
        const days = ['امروز', 'فردا', 'پس‌فردا', 'شنبه', 'یکشنبه'];
        const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        const now = new Date();
        const jYear = now.getFullYear() - 621;
        const jMonth = now.getMonth();
        const jDay = now.getDate();

        for (let i = 0; i < 5; i++) {
            const d = new Date();
            d.setDate(d.getDate() + i);
            const dayEl = document.createElement('div');
            dayEl.className = `cursor-pointer text-center min-w-[60px] bg-white/5 border border-white/10 hover:border-white/40 transition-colors rounded-3xl py-4 ${i === 0 ? 'ring-2 ring-rose-400' : ''}`;
            dayEl.dataset.date = d.toISOString().split('T')[0];
            dayEl.onclick = function() {
                document.querySelectorAll('#date-picker > div').forEach(el => el.classList.remove('ring-2', 'ring-rose-400'));
                this.classList.add('ring-2', 'ring-rose-400');
                loadTimeSlots(this.dataset.date);
            };
            dayEl.innerHTML = `
                <div class="text-xs text-white/60">${days[i]}</div>
                <div class="font-semibold text-xl text-white">${jDay + i}</div>
                <div class="text-rose-400 text-xs">${persianMonths[jMonth]}</div>
            `;
            datePicker.appendChild(dayEl);
        }
    }
}

function loadTimeSlots(date) {
    const container = document.getElementById('time-slots');
    if (!container) return;
    container.innerHTML = '<div class="col-span-3 text-center py-8 text-zinc-500">در حال بارگذاری...</div>';

    fetch('/booking/slots?date=' + encodeURIComponent(date))
        .then(r => r.json())
        .then(data => {
            if (data.slots && data.slots.length) {
                container.innerHTML = data.slots.map(slot =>
                    `<div onclick="selectTimeSlot(this, '${slot}')" class="bg-white/5 border border-white/10 hover:border-emerald-400 transition-all text-center py-5 rounded-3xl cursor-pointer">${slot}</div>`
                ).join('');
            } else {
                container.innerHTML = '<div class="col-span-3 text-center py-8 text-zinc-500">هیچ ساعتی خالی نیست</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="col-span-3 text-center py-8 text-zinc-500">خطا در بارگذاری</div>';
        });
}

let selectedSlot = null;

function selectTimeSlot(el, slot) {
    document.querySelectorAll('#time-slots > div').forEach(d => {
        d.classList.remove('bg-emerald-900', 'text-emerald-400', 'border-emerald-400');
    });
    el.classList.add('bg-emerald-900', 'text-emerald-400', 'border-emerald-400');
    selectedSlot = slot;
}

function nextBookingStep() {
    if (currentBookingStep === 0 && !selectedServiceId) {
        showToast('لطفاً یک خدمت را انتخاب کنید', 'error');
        return;
    }
    if (currentBookingStep < 2) { currentBookingStep++; renderBookingStep(); }
}

function prevBookingStep() {
    if (currentBookingStep > 0) { currentBookingStep--; renderBookingStep(); }
}

function confirmBooking() {
    if (!selectedServiceId || !selectedSlot) {
        showToast('لطفاً خدمت و ساعت را انتخاب کنید', 'error');
        return;
    }

    const dateEl = document.querySelector('#date-picker > div.ring-2');
    const date = dateEl ? dateEl.dataset.date : new Date().toISOString().split('T')[0];

    fetch('/booking/confirm', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'service_id=' + selectedServiceId + '&date=' + encodeURIComponent(date) + '&time=' + encodeURIComponent(selectedSlot)
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) {
            currentBookingStep = 0;
            selectedServiceId = null;
            selectedSlot = null;
            renderBookingStep();
        }
    })
    .catch(() => showToast('خطا در ثبت نوبت', 'error'));
}

function finishBooking() {
    showToast('نوبت شما با موفقیت ثبت شد', 'success');
    currentBookingStep = 0;
}

// ========== KEYBOARD SHORTCUTS ==========
document.addEventListener('keydown', function(e) {
    if (e.metaKey && e.key === 'k') {
        e.preventDefault();
        const link = document.querySelector('a[href="/login"]');
        if (link) link.click();
    }
});

// ========== TESTIMONIAL CAROUSEL ==========
function prevTestimonial() {
    const container = document.getElementById('testimonial-carousel');
    if (container) container.scrollBy({ left: -container.offsetWidth, behavior: 'smooth' });
}

function nextTestimonial() {
    const container = document.getElementById('testimonial-carousel');
    if (container) container.scrollBy({ left: container.offsetWidth, behavior: 'smooth' });
}

// ========== INIT ==========
(function init() {
    // Load services data for booking
    fetch('/api/services')
        .then(r => r.json())
        .then(data => { window._mobaroServices = data.services || data; })
        .catch(() => {});

    // Update cart count on page load
    fetch('/cart/summary')
        .then(r => r.json())
        .then(data => {
            const countEl = document.getElementById('cart-count');
            if (countEl && data.count !== undefined) countEl.innerText = data.count;
        })
        .catch(() => {});

    // Phone input formatting
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('keyup', function() {
            let val = this.value.replace(/\D/g, '');
            if (val.length > 10) val = val.substring(0, 10);
            this.value = val;
        });
    }

    console.log('%cموبارو فعال شد 🚀', 'color:#B76E79; font-family:Vazirmatn,sans-serif');
})();
