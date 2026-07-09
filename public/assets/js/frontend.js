/**
 * Mobaro Frontend JS
 */

function getCsrf() {
    const meta = document.querySelector('meta[name="csrf"]');
    return meta ? meta.getAttribute('content') : '';
}

function csrfParam() {
    const token = getCsrf();
    return token ? '_csrf=' + encodeURIComponent(token) : '';
}

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

    const body = 'contact=' + encodeURIComponent(val) + '&' + csrfParam();
    fetch('/newsletter/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) input.value = '';
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

// ========== CART FUNCTIONS ==========
function addToCart(productId) {
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = d.cart_count || d.count;
            showToast(d.message || 'به سبد خرید اضافه شد');
        } else {
            showToast(d.error || d.message || 'خطا در افزودن به سبد خرید', 'error');
        }
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}

function quickAddToCart(productId) { addToCart(productId); }
function addToCartFromShop(productId) { addToCart(productId); }

// ========== BOOKING ==========
let currentBookingStep = 0;
let selectedServiceId = null;
let selectedArtistId = null;
let selectedSlot = null;
let bookingArtists = [];

function renderBookingStep() {
    const container = document.getElementById('booking-form-content');
    if (!container) return;

    if (currentBookingStep === 0) {
        container.innerHTML = `
            <div class="space-y-1 mb-6">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">۱</span>
                    <span class="text-zinc-400 font-medium">انتخاب خدمت</span>
                </div>
                <div class="text-xs text-zinc-500 pr-8">نوع خدمات مورد نظر خود را انتخاب کنید</div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm" id="service-select-grid"></div>
            <button onclick="nextBookingStep()"
                    class="mt-8 w-full py-5 text-base font-semibold bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all">ادامه</button>
        `;

        const grid = document.getElementById('service-select-grid');
        if (window._mobaroServices) {
            window._mobaroServices.forEach(service => {
                const el = document.createElement('div');
                el.className = `px-5 py-6 border-2 rounded-2xl cursor-pointer transition-all text-center ${
                    selectedServiceId == service.id
                        ? 'border-rose-400 bg-rose-50 shadow-sm'
                        : 'border-zinc-100 hover:border-rose-200 hover:bg-rose-50/50'
                }`;
                el.innerHTML = `
                    <div class="font-semibold text-zinc-800">${service.title}</div>
                    <div class="text-xs text-zinc-400 mt-1">${service.duration || ''}</div>
                    <div class="text-rose-500 font-bold text-xl mt-4">${(service.price || 0).toLocaleString('fa-IR')} <span class="text-xs font-normal">تومان</span></div>
                `;
                el.onclick = function() {
                    selectedServiceId = service.id;
                    renderBookingStep();
                };
                grid.appendChild(el);
            });
        }
    } else if (currentBookingStep === 1) {
        container.innerHTML = `
            <div class="space-y-1 mb-6">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">۲</span>
                    <span class="text-zinc-400 font-medium">انتخاب آرایشگر</span>
                </div>
                <div class="text-xs text-zinc-500 pr-8">آرایشگر دلخواه را انتخاب کنید (اختیاری)</div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm" id="artist-select-grid"></div>
            <div class="flex items-center gap-4 mt-8">
                <button onclick="prevBookingStep()" class="flex-1 py-4 border-2 border-zinc-200 text-zinc-600 rounded-2xl hover:bg-zinc-50 transition-all font-medium">قبلی</button>
                <button onclick="nextBookingStep()" class="flex-1 py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all font-medium">ادامه</button>
            </div>
        `;

        const grid = document.getElementById('artist-select-grid');
        const artists = bookingArtists.length ? bookingArtists : (window._mobaroArtists || []);
        if (artists.length) {
            const anyEl = document.createElement('div');
            anyEl.className = `px-4 py-5 border-2 rounded-2xl cursor-pointer transition-all text-center ${
                !selectedArtistId ? 'border-rose-400 bg-rose-50 shadow-sm' : 'border-zinc-100 hover:border-rose-200'
            }`;
            anyEl.innerHTML = '<div class="font-semibold text-zinc-800">هر آرایشگر</div><div class="text-xs text-zinc-400 mt-1">بدون ترجیح</div>';
            anyEl.onclick = function() { selectedArtistId = null; renderBookingStep(); };
            grid.appendChild(anyEl);

            artists.forEach(artist => {
                const el = document.createElement('div');
                el.className = `px-4 py-5 border-2 rounded-2xl cursor-pointer transition-all text-center ${
                    selectedArtistId == artist.id ? 'border-rose-400 bg-rose-50 shadow-sm' : 'border-zinc-100 hover:border-rose-200'
                }`;
                el.innerHTML = `
                    <div class="w-12 h-12 rounded-full bg-zinc-100 mx-auto mb-2 flex items-center justify-center text-lg overflow-hidden">
                        ${artist.avatar ? `<img src="/assets/images/${artist.avatar}" class="w-full h-full object-cover" alt="">` : `<i class="fa-regular fa-user text-zinc-400"></i>`}
                    </div>
                    <div class="font-semibold text-zinc-800 text-sm">${artist.name}</div>
                    <div class="text-[10px] text-zinc-400 mt-1">${artist.specialty || ''}</div>
                `;
                el.onclick = function() { selectedArtistId = artist.id; renderBookingStep(); };
                grid.appendChild(el);
            });
        } else {
            grid.innerHTML = '<div class="col-span-2 text-center py-8 text-zinc-400">هیچ آرایشگری یافت نشد</div>';
        }
    } else if (currentBookingStep === 2) {
        container.innerHTML = `
            <div class="space-y-1 mb-6">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">۳</span>
                    <span class="text-zinc-400 font-medium">تاریخ و ساعت</span>
                </div>
                <div class="text-xs text-zinc-500 pr-8">زمان مورد نظر خود را انتخاب کنید</div>
            </div>
            <div class="mb-6" id="date-picker">
                <div class="flex gap-2 overflow-x-auto pb-2" id="date-picker-days"></div>
            </div>
            <div class="grid grid-cols-3 gap-3 text-sm" id="time-slots">
                <div class="col-span-3 text-center py-10 text-zinc-400">لطفاً تاریخ را انتخاب کنید</div>
            </div>
            <div class="flex items-center gap-4 mt-8">
                <button onclick="prevBookingStep()" class="flex-1 py-4 border-2 border-zinc-200 text-zinc-600 rounded-2xl hover:bg-zinc-50 transition-all font-medium">قبلی</button>
                <button onclick="confirmBooking()" class="flex-1 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl transition-all font-medium">تأیید نوبت</button>
            </div>
        `;

        renderDatePicker();
    }
}

function renderDatePicker() {
    const container = document.getElementById('date-picker-days');
    if (!container) return;

    const days = ['امروز', 'فردا', 'پس‌فردا', 'شنبه', 'یکشنبه'];
    const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    const now = new Date();

    for (let i = 0; i < 5; i++) {
        const d = new Date();
        d.setDate(d.getDate() + i);
        const dayEl = document.createElement('div');
        dayEl.className = `cursor-pointer text-center min-w-[80px] border-2 rounded-2xl py-4 transition-all flex-shrink-0 ${
            i === 0 ? 'border-rose-400 bg-rose-50' : 'border-zinc-100 hover:border-rose-200 hover:bg-rose-50/50'
        }`;
        dayEl.dataset.date = d.toISOString().split('T')[0];
        dayEl.onclick = function() {
            document.querySelectorAll('#date-picker-days > div').forEach(el => {
                el.className = el.className.replace(/border-rose-400 bg-rose-50/g, 'border-zinc-100');
            });
            this.className = 'cursor-pointer text-center min-w-[80px] border-2 rounded-2xl py-4 transition-all flex-shrink-0 border-rose-400 bg-rose-50';
            loadTimeSlots(this.dataset.date);
        };
        dayEl.innerHTML = `
            <div class="text-[10px] text-zinc-400">${days[i]}</div>
            <div class="font-bold text-xl text-zinc-800 mt-1">${d.getDate()}</div>
            <div class="text-xs text-rose-500">${persianMonths[d.getMonth()]}</div>
        `;
        container.appendChild(dayEl);

        if (i === 0) {
            loadTimeSlots(d.toISOString().split('T')[0]);
        }
    }
}

function loadTimeSlots(date) {
    const container = document.getElementById('time-slots');
    if (!container) return;
    container.innerHTML = '<div class="col-span-3 text-center py-8 text-zinc-400">در حال بارگذاری...</div>';

    const body = 'date=' + encodeURIComponent(date)
        + '&service_id=' + (selectedServiceId || 0)
        + '&' + csrfParam();
    fetch('/booking/slots', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(data => {
        const slots = data.available_slots || data.slots || [];
        if (slots.length) {
            container.innerHTML = slots.map(slot =>
                `<div onclick="selectTimeSlot(this)" data-slot="${slot}" class="border-2 border-zinc-100 hover:border-emerald-400 hover:bg-emerald-50/50 transition-all text-center py-4 rounded-2xl cursor-pointer text-zinc-700 font-medium">${slot}</div>`
            ).join('');
        } else {
            container.innerHTML = '<div class="col-span-3 text-center py-10 text-zinc-400">هیچ ساعتی خالی نیست</div>';
        }
    })
    .catch(() => {
        container.innerHTML = '<div class="col-span-3 text-center py-8 text-zinc-400">خطا در بارگذاری</div>';
    });
}

function selectTimeSlot(el) {
    document.querySelectorAll('#time-slots > div').forEach(d => {
        d.className = d.className.replace(/border-emerald-400 bg-emerald-50/g, 'border-zinc-100');
    });
    el.className = 'border-2 border-emerald-400 bg-emerald-50 transition-all text-center py-4 rounded-2xl cursor-pointer text-emerald-700 font-medium';
    selectedSlot = el.dataset.slot;
}

function nextBookingStep() {
    const maxStep = window._mobaroArtists?.length ? 2 : 1;
    if (currentBookingStep === 0 && !selectedServiceId) {
        showToast('لطفاً یک خدمت را انتخاب کنید', 'error');
        return;
    }
    if (currentBookingStep < maxStep) {
        currentBookingStep++;
        renderBookingStep();
    }
}

function prevBookingStep() {
    if (currentBookingStep > 0) {
        currentBookingStep--;
        renderBookingStep();
    }
}

function confirmBooking() {
    if (!selectedServiceId || !selectedSlot) {
        showToast('لطفاً خدمت و ساعت را انتخاب کنید', 'error');
        return;
    }

    const dateEl = document.querySelector('#date-picker-days > div.border-rose-400');
    const date = dateEl ? dateEl.dataset.date : new Date().toISOString().split('T')[0];

    const body = 'service_id=' + selectedServiceId
        + (selectedArtistId ? '&artist_id=' + selectedArtistId : '')
        + '&date=' + encodeURIComponent(date)
        + '&time=' + encodeURIComponent(selectedSlot)
        + '&' + csrfParam();

    fetch('/booking/confirm', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(d => {
        if (!d.success && d.require_login) {
            window.location.href = '/login?redirect=/booking';
            return;
        }
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) {
            currentBookingStep = 0;
            selectedServiceId = null;
            selectedArtistId = null;
            selectedSlot = null;
            renderBookingStep();
            loadTodayAppointments();
        }
    })
    .catch(() => showToast('خطا در ثبت نوبت', 'error'));
}

function loadTodayAppointments() {
    const container = document.getElementById('today-appointments');
    if (!container) return;

    const today = new Date().toISOString().split('T')[0];
    const body = 'date=' + today + '&' + csrfParam();
    fetch('/booking/slots', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(data => {
        const total = (data.available_slots?.length || 0) + (data.booked_slots?.length || 0);
        const remaining = data.available_slots?.length || 0;
        document.querySelector('#booking .text-emerald-500') &&
            (document.querySelector('#booking .text-emerald-500').textContent = remaining + ' نوبت باقی مانده');
        if (data.booked_slots?.length) {
            container.innerHTML = data.booked_slots.map(slot =>
                `<div class="flex items-center justify-between px-4 py-3 bg-zinc-50 rounded-2xl">
                    <span class="text-sm text-zinc-500"><i class="fa-regular fa-clock ml-2"></i>${slot}</span>
                    <span class="text-xs text-rose-500 bg-rose-50 px-3 py-1 rounded-full">رزرو شده</span>
                </div>`
            ).join('');
        } else {
            container.innerHTML = '<div class="text-center py-8 text-zinc-300"><i class="fa-regular fa-calendar-check text-3xl mb-2"></i><p class="text-sm">هیچ نوبتی برای امروز ثبت نشده</p></div>';
        }
    })
    .catch(() => {});
}

function setBookingStep(step) {
    const bookingEl = document.getElementById('booking');
    if (!bookingEl) return;
    currentBookingStep = Math.min(step, currentBookingStep);
    renderBookingStep();
}

function selectService(serviceId) {
    selectedServiceId = serviceId;
    currentBookingStep = 0;
    renderBookingStep();
}

// ========== TESTIMONIAL CAROUSEL ==========
function prevTestimonial() {
    const container = document.getElementById('testimonial-carousel');
    if (container) container.scrollBy({ left: -container.offsetWidth, behavior: 'smooth' });
}

function nextTestimonial() {
    const container = document.getElementById('testimonial-carousel');
    if (container) container.scrollBy({ left: container.offsetWidth, behavior: 'smooth' });
}

// ========== KEYBOARD SHORTCUTS ==========
document.addEventListener('keydown', function(e) {
    if (e.metaKey && e.key === 'k') {
        e.preventDefault();
        const link = document.querySelector('a[href="/login"]');
        if (link) link.click();
    }
});

// ========== INIT ==========
(function init() {
    fetch('/api/services')
        .then(r => r.json())
        .then(data => {
            window._mobaroServices = data.services || data;
            if (document.getElementById('booking')) {
                renderBookingStep();
                loadTodayAppointments();
            }
        })
        .catch(() => {});

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
