function getCsrf() {
    const meta = document.querySelector('meta[name="csrf"]');
    return meta ? meta.getAttribute('content') : '';
}
function csrfParam() {
    const token = getCsrf();
    return token ? '_csrf=' + encodeURIComponent(token) : '';
}

// ========== GLOBAL TOAST ==========
function showToast(message, type) {
    type = type || 'success';
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:999999;';
        document.body.appendChild(container);
    }
    container.classList.remove('hidden');
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    const color = type === 'success' ? 'text-emerald-500' : 'text-rose-500';
    const toast = document.createElement('div');
    toast.className = 'flex items-center gap-x-3 bg-white shadow-2xl border border-zinc-100 text-zinc-700 px-5 py-4 rounded-3xl mb-3 animate-slide-up';
    toast.innerHTML = '<div class="' + color + '"><i class="fa-solid ' + icon + '"></i></div><div class="text-sm font-medium">' + message + '</div>';
    container.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
    }, 3000);
}

// ========== UTILITY ==========
function formatPrice(price) {
    if (!price) return '۰ تومان';
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' تومان';
}
function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) stars += '<i class="fa-solid fa-star"></i>';
        else if (i - 0.5 <= rating) stars += '<i class="fa-solid fa-star-half-alt"></i>';
        else stars += '<i class="fa-regular fa-star text-zinc-300"></i>';
    }
    return stars;
}
function showLoading(el) {
    if (el) { el.classList.add('loading'); el.disabled = true; }
}
function hideLoading(el) {
    if (el) { el.classList.remove('loading'); el.disabled = false; }
}

// ========== MOBILE MENU ==========
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.toggle('hidden');
}
function navigateToSection(section) {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.add('hidden');
    if (section === 'booking') {
        const el = document.getElementById('booking');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
            setTimeout(function() { if (typeof renderBookingStep === 'function') renderBookingStep(); }, 800);
        }
        return;
    }
    const el = document.getElementById(section);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

// ========== SCROLL PROGRESS ==========
(function() {
    window.addEventListener('scroll', function() {
        const bar = document.getElementById('progress-bar');
        if (!bar) return;
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        bar.style.width = docHeight > 0 ? (scrollTop / docHeight * 100) + '%' : '0%';
    });
})();

// ========== HERO SCROLL ==========
document.addEventListener('click', function(e) {
    const heroBtn = e.target.closest('[data-scroll]');
    if (heroBtn) {
        const target = heroBtn.getAttribute('data-scroll');
        const el = document.getElementById(target);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }
});

// ========== NEWSLETTER ==========
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
    .then(function(r) { return r.json(); })
    .then(function(d) {
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) input.value = '';
    })
    .catch(function() { showToast('خطا در ارتباط با سرور', 'error'); });
}

// ========== CART ==========
function addToCart(productId, btn) {
    if (btn) showLoading(btn);
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (btn) hideLoading(btn);
        if (d.success) {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = d.cart_count || d.count;
            showToast(d.message || 'به سبد خرید اضافه شد');
        } else {
            showToast(d.error || d.message || 'خطا', 'error');
        }
    })
    .catch(function() { if (btn) hideLoading(btn); showToast('خطا در ارتباط با سرور', 'error'); });
}

function toggleCart() {
    const sidebar = document.getElementById('cartSidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('hidden');
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    if (!container) return;
    const body = '_action=cart_data&' + csrfParam();
    fetch('/shop/cart/list', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        const items = data.cart || [];
        if (items.length === 0) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-zinc-400"><i class="fa-solid fa-bag-shopping text-6xl mb-4"></i><p>سبد خرید شما خالی است</p></div>';
            document.getElementById('cartTotal').textContent = '۰ تومان';
            return;
        }
        container.innerHTML = items.map(function(item) { return `
            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-zinc-100">
                <img src="/assets/images/${item.image}" alt="${item.name}" class="w-20 h-20 rounded-lg object-cover" onerror="this.src='https://picsum.photos/seed/i${item.id}/200/200'">
                <div class="flex-1">
                    <h4 class="font-medium text-zinc-800">${item.name}</h4>
                    <p class="text-sm text-zinc-500">${item.brand || ''}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center border rounded-lg">
                            <button onclick="updateShopQuantity(${item.id}, -1, this)" class="px-3 py-1 text-zinc-500 hover:text-rose-500">-</button>
                            <span class="px-3 py-1 border-x">${item.qty}</span>
                            <button onclick="updateShopQuantity(${item.id}, 1, this)" class="px-3 py-1 text-zinc-500 hover:text-rose-500">+</button>
                        </div>
                        <button onclick="removeFromCartItem(${item.id})" class="text-red-400 hover:text-red-500"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>
                <div class="text-left">
                    <span class="font-bold text-rose-500">${formatPrice(item.price * item.qty)}</span>
                </div>
            </div>`;
        }).join('');
        const total = items.reduce(function(sum, item) { return sum + (item.price * item.qty); }, 0);
        document.getElementById('cartTotal').textContent = formatPrice(total);
    })
    .catch(function() { container.innerHTML = '<div class="text-center py-8 text-zinc-400">خطا در بارگذاری</div>'; });
}

function updateShopQuantity(productId, delta, btn) {
    const btnGroup = btn ? btn.parentElement : null;
    if (!btnGroup) return;
    const qtySpan = btnGroup.querySelector('span');
    if (!qtySpan) return;
    const currentQty = parseInt(qtySpan.textContent.replace(/,/g, '')) || 1;
    const newQty = Math.max(1, currentQty + delta);
    const body = 'product_id=' + productId + '&qty=' + newQty + '&' + csrfParam();
    fetch('/shop/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) { if (d.success) renderCart(); })
    .catch(function() {});
}

function removeFromCartItem(productId) {
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/cart/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = d.cart_count || 0;
            renderCart();
            showToast('محصول از سبد خرید حذف شد');
        }
    })
    .catch(function() {});
}

function checkout() {
    const body = csrfParam();
    fetch('/shop/cart/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.require_login) { window.location.href = '/login?redirect=/shop'; return; }
        showToast(d.message || d.error, d.success ? 'success' : 'error');
        if (d.success) {
            document.getElementById('cart-count').textContent = '0';
            toggleCart();
        }
    })
    .catch(function() { showToast('خطا در ثبت سفارش', 'error'); });
}

// ========== WISHLIST ==========
function toggleWishlistItem(productId) {
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/wishlist/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            const badge = document.getElementById('wishlist-count');
            if (badge) badge.innerText = d.wishlist_count;
            showToast(d.action === 'added' ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد');
            // Toggle heart icon
            document.querySelectorAll('.heart-btn[onclick*="toggleWishlistItem(' + productId + ')"]').forEach(function(btn) {
                btn.classList.toggle('active');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = btn.classList.contains('active') ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-rose-500';
                }
            });
            // Toggle detail page heart
            document.querySelectorAll('.heart-btn-detail').forEach(function(btn) {
                btn.classList.toggle('active');
                btn.classList.toggle('text-rose-500');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = btn.classList.contains('active') ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                }
                const span = btn.querySelector('span');
                if (span) {
                    span.textContent = btn.classList.contains('active') ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها';
                }
            });
        }
    })
    .catch(function() { showToast('خطا در ارتباط با سرور', 'error'); });
}

function toggleWishlistSidebar() {
    const sidebar = document.getElementById('wishlistSidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('hidden');
    renderWishlist();
}

function renderWishlist() {
    const container = document.getElementById('wishlistItems');
    if (!container) return;
    fetch('/wishlist', {
        method: 'GET',
        headers: { 'Accept': 'text/html' }
    })
    .then(function(r) { return r.text(); })
    .then(function(html) {
        // Parse the response - we'll render client-side from shopProducts
        const wishlistIds = [];
        document.querySelectorAll('.heart-btn.active').forEach(function(btn) {
            const onclick = btn.getAttribute('onclick') || '';
            const match = onclick.match(/toggleWishlistItem\((\d+)\)/);
            if (match) wishlistIds.push(parseInt(match[1]));
        });
        if (typeof shopProducts === 'undefined' || !shopProducts.length) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-zinc-400"><i class="fa-regular fa-heart text-6xl mb-4"></i><p>لیست علاقه‌مندی‌ها خالی است</p></div>';
            return;
        }
        const items = shopProducts.filter(function(p) { return wishlistIds.includes(p.id); });
        if (items.length === 0) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-zinc-400"><i class="fa-regular fa-heart text-6xl mb-4"></i><p>لیست علاقه‌مندی‌ها خالی است</p></div>';
            return;
        }
        container.innerHTML = items.map(function(item) { return `
            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-zinc-100">
                <img src="${item.image}" alt="${item.name}" class="w-20 h-20 rounded-lg object-cover">
                <div class="flex-1">
                    <h4 class="font-medium text-zinc-800">${item.name}</h4>
                    <p class="text-sm text-zinc-500">${item.brand}</p>
                    <span class="font-bold text-rose-500">${formatPrice(item.price)}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <button onclick="addToCart(${item.id})" class="bg-rose-600 text-white px-3 py-2 rounded-lg text-sm"><i class="fa-solid fa-bag-shopping"></i></button>
                    <button onclick="toggleWishlistItem(${item.id})" class="text-red-400 hover:text-red-500"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>`;
        }).join('');
    })
    .catch(function() {});
}

// ========== QUICK VIEW ==========
let qvQty = 1;
function openQuickView(productId) {
    if (typeof shopProducts === 'undefined') return;
    const product = shopProducts.find(function(p) { return p.id === productId; });
    if (!product) return;
    const modal = document.getElementById('quickViewModal');
    const content = document.getElementById('quickViewContent');
    if (!modal || !content) return;
    content.innerHTML = `
        <div><img src="${product.image}" alt="${product.name}" class="w-full rounded-2xl"></div>
        <div>
            <span class="text-rose-500 font-medium">${product.brand}</span>
            <h2 class="text-2xl font-bold text-zinc-800 mt-2 mb-4">${product.name}</h2>
            <div class="flex items-center gap-3 mb-4">
                <div class="star-rating text-amber-400 text-lg">${generateStars(product.rating)}</div>
                <span class="text-zinc-500">(${product.reviews.toLocaleString('fa-IR')} نظر)</span>
            </div>
            <p class="text-zinc-600 mb-6">${product.description}</p>
            <div class="mb-6">
                ${product.old_price ? '<span class="text-zinc-400 text-lg line-through ml-3">' + formatPrice(product.old_price) + '</span>' : ''}
                <span class="text-3xl font-bold text-rose-500">${formatPrice(product.price)}</span>
            </div>
            <div class="flex items-center gap-4 mb-6">
                <div class="flex items-center border-2 border-zinc-200 rounded-xl">
                    <button onclick="qvChangeQty(-1)" class="px-4 py-3 text-zinc-500 hover:text-rose-500 text-lg">-</button>
                    <span id="qvQty" class="px-4 py-3 border-x-2 border-zinc-200 font-medium">۱</span>
                    <button onclick="qvChangeQty(1)" class="px-4 py-3 text-zinc-500 hover:text-rose-500 text-lg">+</button>
                </div>
                <button onclick="addToCart(${product.id}); closeQuickView();" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-medium text-lg transition-all">
                    <i class="fa-solid fa-bag-shopping ml-2"></i>افزودن به سبد خرید
                </button>
            </div>
            <div class="flex items-center gap-4 text-zinc-500">
                <button onclick="toggleWishlistItem(${product.id})" class="flex items-center gap-2 hover:text-rose-500 transition">
                    <i class="fa-regular fa-heart"></i> افزودن به علاقه‌مندی‌ها
                </button>
                <button class="flex items-center gap-2 hover:text-rose-500 transition">
                    <i class="fa-solid fa-share-nodes"></i> اشتراک‌گذاری
                </button>
            </div>
        </div>`;
    modal.classList.add('active');
    qvQty = 1;
}
function qvChangeQty(delta) {
    qvQty = Math.max(1, qvQty + delta);
    const el = document.getElementById('qvQty');
    if (el) el.textContent = qvQty.toLocaleString('fa-IR');
}
function closeQuickView() {
    const modal = document.getElementById('quickViewModal');
    if (modal) modal.classList.remove('active');
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

// ========== BOOKING SYSTEM ==========
function getPersianParts(date) {
    var parts = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric', month: 'long', day: 'numeric'
    }).formatToParts(date);
    var r = {};
    parts.forEach(function(p) { r[p.type] = p.value; });
    return r;
}
function getPersianWeekday(date) {
    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long' }).format(date);
}
function getPersianDateStr(date) {
    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric', month: 'long', day: 'numeric'
    }).format(date);
}
function getTehranNow() {
    var now = new Date();
    var opts = { timeZone: 'Asia/Tehran' };
    var dateStr = now.toLocaleDateString('en-CA', opts);
    var t = now.toLocaleTimeString('en-US', { timeZone: 'Asia/Tehran', hour12: false });
    var p = t.split(':');
    return {
        date: dateStr,
        hour: +p[0],
        minute: +p[1],
        totalMinutes: +p[0] * 60 + +p[1]
    };
}

var currentBookingStep = 0;
var selectedArtistId = 0;
var selectedServiceIndex = null;
var selectedDate = '';
var selectedTime = '';

function getBookingContainer() {
    return document.getElementById('booking-form-content');
}

function getBookingTheme() {
    var container = getBookingContainer();
    if (!container) return 'light';
    return container.getAttribute('data-theme') || 'light';
}

function updateStepDots(step) {
    var dots = document.querySelectorAll('.booking-step');
    var container = getBookingContainer();
    var isDark = container ? container.getAttribute('data-theme') === 'dark' : false;
    for (var i = 0; i < dots.length; i++) {
        var dot = dots[i];
        if (i === step) {
            dot.style.borderColor = '#e11d48';
            dot.style.backgroundColor = '#e11d48';
            dot.style.color = '#fff';
        } else {
            dot.style.borderColor = isDark ? 'rgba(255,255,255,0.3)' : '#d4d4d8';
            dot.style.backgroundColor = 'transparent';
            dot.style.color = isDark ? '#fff' : '#a1a1aa';
        }
    }
}

function renderBookingStep() {
    var container = getBookingContainer();
    if (!container) return;
    var theme = getBookingTheme();
    var artists = window._mobaroArtists || [];
    var services = window._mobaroServices || [];
    var isDark = theme === 'dark';
    var baseBorder = isDark ? 'border-white/10' : 'border-zinc-200';
    var textMuted = isDark ? 'text-zinc-400' : 'text-zinc-500';
    var textPrimary = isDark ? 'text-white' : 'text-zinc-800';
    var priceClass = isDark ? 'text-rose-400' : 'text-rose-500';
    var selectedBorder = isDark ? 'border-rose-400' : 'border-rose-500';
    var selectedBg = isDark ? 'bg-rose-50/10' : 'bg-rose-50';
    var hoverBorder = isDark ? 'hover:border-white/30' : 'hover:border-rose-200';
    var hoverBg = isDark ? 'hover:bg-white/5' : 'hover:bg-rose-50/50';
    var btnBorder = isDark ? 'border-white/30 hover:bg-white/10' : 'border-zinc-300 hover:bg-zinc-50';
    var btnText = isDark ? 'text-white' : 'text-zinc-700';
    var accentBg = isDark ? 'bg-white' : 'bg-rose-600';
    var accentText = isDark ? 'text-zinc-900' : 'text-white';
    var slotBg = isDark ? 'bg-white/5 border-white/10 hover:border-emerald-400 text-white' : 'bg-zinc-50 border-zinc-200 hover:border-emerald-400 text-zinc-700';

    updateStepDots(currentBookingStep);

    if (currentBookingStep === 0) {
        container.innerHTML =
            '<div class="text-xs font-medium ' + textMuted + ' mb-4">انتخاب آرایشگر</div>' +
            '<div class="space-y-3" id="artist-select-list"></div>' +
            '<button onclick="nextBookingStep()" class="mt-6 w-full py-5 text-lg font-semibold border ' + btnBorder + ' ' + btnText + ' transition-colors rounded-3xl">ادامه ←</button>';

        var list = document.getElementById('artist-select-list');
        for (var i = 0; i < artists.length; i++) {
            (function(idx) {
                var a = artists[idx];
                var isSel = selectedArtistId === a.id;
                var el = document.createElement('div');
                el.className = 'flex items-center gap-4 p-4 border ' + (isSel ? selectedBorder + ' ' + selectedBg : baseBorder) + ' rounded-2xl cursor-pointer transition-all ' + hoverBorder + ' ' + hoverBg;
                el.innerHTML =
                    '<div class="w-14 h-14 rounded-2xl bg-cover bg-center flex-shrink-0" style="background-image:url(/assets/images/' + (a.avatar || 'default-avatar.jpg') + ')"></div>' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="font-medium ' + textPrimary + ' truncate">' + a.name + '</div>' +
                        '<div class="text-xs ' + textMuted + ' truncate">' + (a.specialty || '') + '</div>' +
                        '<div class="text-[10px] ' + textMuted + ' mt-0.5">' + (a.working_hours || '') + '</div>' +
                    '</div>' +
                    '<div class="w-5 h-5 rounded-full border-2 flex-shrink-0 ' + (isSel ? 'bg-rose-500 border-rose-500' : (isDark ? 'border-white/30' : 'border-zinc-300')) + '">' +
                        (isSel ? '<i class="fa-solid fa-check text-white text-[10px] flex items-center justify-center w-full h-full"></i>' : '') +
                    '</div>';
                el.onclick = function() {
                    selectedArtistId = a.id;
                    selectedServiceIndex = null;
                    renderBookingStep();
                };
                list.appendChild(el);
            })(i);
        }

    } else if (currentBookingStep === 1) {
        var filtered = [];
        for (var i = 0; i < services.length; i++) {
            if (!services[i].artist_id || services[i].artist_id == selectedArtistId) {
                filtered.push(services[i]);
            }
        }

        container.innerHTML =
            '<div class="text-xs font-medium ' + textMuted + ' mb-4">انتخاب خدمت</div>' +
            '<div class="grid grid-cols-2 gap-3 text-sm" id="service-select-grid"></div>' +
            '<div class="flex items-center gap-4 mt-8">' +
                '<button onclick="prevBookingStep()" class="flex-1 py-4 border ' + btnBorder + ' ' + btnText + ' rounded-3xl text-sm">قبلی</button>' +
                '<button onclick="nextBookingStep()" class="flex-1 py-4 ' + accentBg + ' ' + accentText + ' font-semibold rounded-3xl text-sm">بعدی</button>' +
            '</div>';

        var grid = document.getElementById('service-select-grid');
        if (filtered.length === 0) {
            grid.innerHTML = '<div class="col-span-2 text-center py-10 ' + textMuted + ' text-sm">این آرایشگر خدمتی ثبت نکرده است</div>';
        } else {
            for (var i = 0; i < filtered.length; i++) {
                (function(idx) {
                    var svc = filtered[idx];
                    var actualIdx = services.indexOf(svc);
                    var el = document.createElement('div');
                    el.className = 'px-5 py-6 border ' + (selectedServiceIndex === actualIdx ? selectedBorder + ' ' + selectedBg : baseBorder) + ' rounded-3xl cursor-pointer transition-all ' + hoverBorder + ' ' + hoverBg;
                    el.innerHTML =
                        '<div class="font-medium ' + textPrimary + '">' + svc.title + '</div>' +
                        '<div class="text-xs ' + textMuted + ' mt-1">' + (svc.duration || '') + (svc.artist_name ? ' · ' + svc.artist_name : '') + '</div>' +
                        '<div class="' + priceClass + ' font-semibold text-xl mt-5">' + (svc.price ? svc.price.toLocaleString('fa-IR') : '') + ' تومان</div>';
                    el.onclick = function() {
                        selectedServiceIndex = actualIdx;
                        renderBookingStep();
                    };
                    grid.appendChild(el);
                })(i);
            }
        }

    } else if (currentBookingStep === 2) {
        var today = new Date();
        var dates = [];
        for (var d = 0; d < 5; d++) {
            var dt = new Date(today);
            dt.setDate(today.getDate() + d);
            dates.push(dt);
        }

        var dateHTML = '';
        for (var d = 0; d < dates.length; d++) {
            var dt = dates[d];
            var parts = getPersianParts(dt);
            var weekday = getPersianWeekday(dt);
            var label = d === 0 ? 'امروز' : d === 1 ? 'فردا' : weekday;
            var day = parts.day;
            var month = parts.month;
            var dateStr = dt.toISOString().split('T')[0];
            var isSelected = selectedDate === dateStr;
            dateHTML += '<div onclick="selectDate(\'' + dateStr + '\', this)" class="cursor-pointer text-center min-w-[70px] ' + (isDark ? 'bg-white/5 border-white/10 hover:border-white/40' : 'bg-zinc-50 border-zinc-200 hover:border-zinc-300') + ' border transition-colors rounded-3xl py-4 ' + (isSelected ? 'ring-2 ring-rose-400' : '') + '">' +
                '<div class="text-xs ' + (isDark ? 'text-white/60' : 'text-zinc-400') + '">' + label + '</div>' +
                '<div class="font-semibold text-xl ' + textPrimary + '">' + day + '</div>' +
                '<div class="' + priceClass + ' text-xs">' + month + '</div>' +
            '</div>';
        }

        var timeSlots = [
            { display: '۱۰:۰۰', minutes: 600 },
            { display: '۱۱:۰۰', minutes: 660 },
            { display: '۱۲:۳۰', minutes: 750 },
            { display: '۱۴:۰۰', minutes: 840 },
            { display: '۱۴:۴۵', minutes: 885 },
            { display: '۱۶:۰۰', minutes: 960 },
            { display: '۱۷:۳۰', minutes: 1050 },
            { display: '۱۸:۰۰', minutes: 1080 },
            { display: '۱۹:۰۰', minutes: 1140 },
            { display: '۲۰:۰۰', minutes: 1200 }
        ];
        var tehranNow = getTehranNow();
        var timeHTML = '';
        for (var t = 0; t < timeSlots.length; t++) {
            var slot = timeSlots[t];
            var isPast = selectedDate === tehranNow.date && slot.minutes <= tehranNow.totalMinutes;
            var isTimeSelected = selectedTime === slot.display;
            var cls = isPast ? 'opacity-30 pointer-events-none ' + slotBg : (isTimeSelected ? 'bg-emerald-900 text-emerald-400 border-emerald-400' : slotBg);
            timeHTML += '<div onclick="' + (isPast ? '' : 'selectTimeSlot(\'' + slot.display + '\', this)') + '" class="' + cls + ' border transition-all text-center py-5 rounded-3xl' + (isPast ? '' : ' cursor-pointer') + '">' + slot.display + '</div>';
        }

        container.innerHTML =
            '<div>' +
                '<div class="text-xs font-medium ' + textMuted + ' mb-3">تاریخ</div>' +
                '<div class="flex gap-x-2 overflow-auto pb-6">' + dateHTML + '</div>' +
                '<div class="mt-4 text-xs font-medium ' + textMuted + ' mb-3">ساعت</div>' +
                '<div class="grid grid-cols-3 gap-3 text-xs">' + timeHTML + '</div>' +
            '</div>' +
            '<div class="flex items-center gap-4 mt-14">' +
                '<button onclick="prevBookingStep()" class="flex-1 py-5 border ' + btnBorder + ' ' + btnText + ' rounded-3xl">قبلی</button>' +
                '<button onclick="nextBookingStep()" class="flex-1 py-5 ' + accentBg + ' ' + accentText + ' font-semibold rounded-3xl">بعدی</button>' +
            '</div>';

    } else if (currentBookingStep === 3) {
        var svc = (selectedServiceIndex !== null && services[selectedServiceIndex]) ? services[selectedServiceIndex] : null;
        var artist = null;
        if (selectedArtistId) {
            for (var i = 0; i < artists.length; i++) {
                if (artists[i].id === selectedArtistId) { artist = artists[i]; break; }
            }
        }
        var displayDate = '';
        if (selectedDate) {
            var dt = new Date(selectedDate);
            displayDate = getPersianDateStr(dt);
        }
        var captchaQ = window._mobaroCaptchaQuestion || '۵ + ۳';

        container.innerHTML =
            '<div class="' + (isDark ? 'bg-white/5 border-white/30' : 'bg-zinc-50 border-zinc-200') + ' border border-dashed rounded-3xl p-7 text-center">' +
                '<div class="mx-auto w-20 h-20 ' + (isDark ? 'bg-white/10 text-white' : 'bg-zinc-200 text-zinc-500') + ' rounded-3xl flex items-center justify-center text-4xl mb-5">💇🏻‍♀️</div>' +
                '<div class="font-semibold ' + textPrimary + '">مرور نوبت شما</div>' +
                '<div class="text-xs text-emerald-400 mt-1">' + (displayDate || '') + (selectedTime ? ' - ساعت ' + selectedTime : '') + '</div>' +
                (artist ? '<div class="text-xs ' + textMuted + ' mt-1">آرایشگر: ' + artist.name + '</div>' : '') +
                '<div class="my-8 border-t ' + (isDark ? 'border-white/10' : 'border-zinc-200') + '"></div>' +
                '<div class="flex justify-between text-xs">' +
                    '<div class="text-left">' +
                        '<div class="' + (isDark ? 'text-white/60' : 'text-zinc-400') + '">خدمت</div>' +
                        '<div class="font-medium ' + textPrimary + '">' + (svc ? svc.title : '---') + '</div>' +
                    '</div>' +
                    '<div class="text-right">' +
                        '<div class="' + (isDark ? 'text-white/60' : 'text-zinc-400') + '">هزینه</div>' +
                        '<div class="font-medium ' + priceClass + '">' + (svc ? svc.price.toLocaleString('fa-IR') : '0') + ' تومان</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="mt-6 p-5 ' + (isDark ? 'bg-white/5 border-white/10' : 'bg-zinc-50 border-zinc-200') + ' border rounded-3xl">' +
                '<div class="text-xs font-medium ' + textMuted + ' mb-2">کد امنیتی</div>' +
                '<div class="flex items-center gap-3">' +
                    '<div class="flex-1">' +
                        '<div class="flex items-center gap-3">' +
                            '<span class="text-lg font-bold ' + textPrimary + '" id="captcha-question">' + captchaQ + ' = ?</span>' +
                            '<button onclick="refreshCaptcha()" class="text-xs ' + priceClass + ' hover:underline" type="button">تغییر</button>' +
                        '</div>' +
                        '<input type="text" id="captcha-input" placeholder="پاسخ را وارد کنید" class="mt-3 w-full px-4 py-3 ' + (isDark ? 'bg-zinc-800 text-white border-white/20' : 'bg-white text-zinc-800 border-zinc-200') + ' border-2 rounded-xl focus:border-rose-400 focus:ring-0 outline-none transition-all text-center text-lg font-bold" inputmode="numeric">' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<button onclick="finishBooking()" class="mt-6 w-full py-7 bg-emerald-500 text-white font-bold rounded-3xl" id="bookingConfirmBtn">تأیید و ذخیره نوبت</button>' +
            '<div onclick="prevBookingStep()" class="text-center text-xs ' + (isDark ? 'text-white/60' : 'text-zinc-400') + ' mt-6 cursor-pointer">ویرایش نوبت</div>';
    }
}

function selectDate(dateStr, el) {
    selectedDate = dateStr;
    selectedTime = '';
    var parent = el.parentElement;
    var children = parent.children;
    for (var i = 0; i < children.length; i++) {
        children[i].classList.remove('ring-2', 'ring-rose-400');
    }
    el.classList.add('ring-2', 'ring-rose-400');
}

function selectTimeSlot(time, el) {
    selectedTime = time;
    var container = getBookingContainer();
    var theme = getBookingTheme();
    var isDark = theme === 'dark';
    var slotBg = isDark ? 'bg-white/5 border-white/10 hover:border-emerald-400 text-white' : 'bg-zinc-50 border-zinc-200 hover:border-emerald-400 text-zinc-700';
    var slots = el.parentElement.querySelectorAll('div');
    for (var i = 0; i < slots.length; i++) {
        slots[i].className = slotBg + ' border transition-all text-center py-5 rounded-3xl cursor-pointer';
    }
    el.className = 'bg-emerald-900 text-emerald-400 border border-emerald-400 text-center py-5 rounded-3xl cursor-pointer';
}

function setBookingStep(step) {
    currentBookingStep = step;
    renderBookingStep();
}

function nextBookingStep() {
    if (currentBookingStep === 0 && !selectedArtistId) {
        showToast('لطفاً یک آرایشگر را انتخاب کنید', 'error');
        return;
    }
    if (currentBookingStep === 1 && selectedServiceIndex === null) {
        showToast('لطفاً یک خدمت را انتخاب کنید', 'error');
        return;
    }
    if (currentBookingStep === 2 && !selectedTime) {
        showToast('لطفاً یک ساعت را انتخاب کنید', 'error');
        return;
    }
    if (currentBookingStep < 3) {
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

function selectService(i) {
    var services = window._mobaroServices || [];
    if (!services[i]) return;
    selectedArtistId = services[i].artist_id || 0;
    selectedServiceIndex = i;
    currentBookingStep = 1;
    var bookingSection = document.getElementById('booking');
    if (bookingSection) {
        bookingSection.scrollIntoView({ behavior: 'smooth' });
    }
    setTimeout(function() {
        renderBookingStep();
    }, 900);
    showToast('خدمت "' + services[i].title + '" انتخاب شد');
}

function finishBooking() {
    var services = window._mobaroServices || [];
    var svc = (selectedServiceIndex !== null && services[selectedServiceIndex]) ? services[selectedServiceIndex] : null;
    if (!svc) {
        showToast('لطفاً یک خدمت را انتخاب کنید', 'error');
        return;
    }
    if (!selectedArtistId) {
        showToast('لطفاً یک آرایشگر را انتخاب کنید', 'error');
        return;
    }
    if (!selectedDate || !selectedTime) {
        showToast('لطفاً تاریخ و ساعت را انتخاب کنید', 'error');
        return;
    }
    var captchaInput = document.getElementById('captcha-input');
    var captchaVal = captchaInput ? captchaInput.value.trim() : '';
    if (!captchaVal) {
        showToast('لطفاً پاسخ کد امنیتی را وارد کنید', 'error');
        return;
    }
    var btn = document.getElementById('bookingConfirmBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'در حال ثبت...'; }
    var body = 'service_id=' + (svc.id || svc.service_id || 0) + '&date=' + encodeURIComponent(selectedDate) + '&time=' + encodeURIComponent(selectedTime) + '&artist_id=' + selectedArtistId + '&captcha=' + encodeURIComponent(captchaVal) + '&' + csrfParam();
    fetch('/booking/confirm', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) {
        try { return r.json(); } catch(e) { return { error: 'خطا در پاسخ سرور' }; }
    })
    .then(function(data) {
        if (btn) { btn.disabled = false; btn.textContent = 'تأیید و ذخیره نوبت'; }
        if (data.require_login) {
            window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
            return;
        }
        if (data.success) {
            showToast(data.message || 'نوبت شما با موفقیت ثبت شد!', 'success');
            currentBookingStep = 0;
            selectedArtistId = 0;
            selectedServiceIndex = null;
            selectedDate = '';
            selectedTime = '';
            renderBookingStep();
        } else {
            showToast(data.error || 'خطا در ثبت نوبت', 'error');
            if (data.captcha_error) {
                setTimeout(function() { refreshCaptcha(); }, 500);
            }
        }
    })
    .catch(function() {
        if (btn) { btn.disabled = false; btn.textContent = 'تأیید و ذخیره نوبت'; }
        showToast('خطا در ارتباط با سرور', 'error');
    });
}

function completeBookingDemo() {
    currentBookingStep = 3;
    renderBookingStep();
    showToast('نوبت شما ثبت گردید!', 'success');
}

function refreshCaptcha() {
    var body = csrfParam();
    fetch('/booking/captcha/refresh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var qEl = document.getElementById('captcha-question');
        var inputEl = document.getElementById('captcha-input');
        if (qEl && data.question) {
            qEl.textContent = data.question + ' = ?';
            window._mobaroCaptchaQuestion = data.question;
        }
        if (inputEl) inputEl.value = '';
    })
    .catch(function() {
        showToast('خطا در دریافت کپچای جدید', 'error');
    });
}

// ========== HOME PAGE SHOP (shop-block.php helpers) ==========
function quickAddToCart(id, name, price, image, category) {
    addToCart(id);
}
function addToCartFromShop(id, name, price, image, category) {
    addToCart(id);
}

// ========== GALLERY HELPERS ==========
function likeModel(id) {
    showToast('به علاقه‌مندی‌ها اضافه شد ❤️');
}

// ========== EDUCATION HELPERS ==========
function watchFeaturedVideo() {
    showToast('در حال پخش ویدیو آموزشی...', 'success');
}
function openTutorial(n) {
    var messages = [
        'در حال پخش ویدیو «چگونه موهای خود را لایت کنیم»',
        'در حال پخش ویدیو «آرایش روزانه در ۱۰ دقیقه»'
    ];
    showToast(messages[n] || 'در حال پخش ویدیو...');
}

// ========== PHONE VALIDATION ==========
document.addEventListener('keyup', function(e) {
    const input = e.target;
    if (input.matches('input[name="phone"], input[name="mobile"], #newsletter-input')) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 11) val = val.substring(0, 11);
        input.value = val;
    }
});

// ========== INIT ==========
(function init() {
    // Load booking services
    fetch('/api/services')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            window._mobaroServices = data.services || data;
            if (document.getElementById('booking')) {
                if (typeof renderBookingStep === 'function') renderBookingStep();
            }
        })
        .catch(function() {});

    // Close modals on overlay click
    document.addEventListener('click', function(e) {
        const qvModal = document.getElementById('quickViewModal');
        if (qvModal && e.target === qvModal) closeQuickView();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickView();
            const cartSidebar = document.getElementById('cartSidebar');
            if (cartSidebar && !cartSidebar.classList.contains('hidden')) toggleCart();
            const wishlistSidebar = document.getElementById('wishlistSidebar');
            if (wishlistSidebar && !wishlistSidebar.classList.contains('hidden')) toggleWishlistSidebar();
        }
    });

    // Welcome toast for home page
    if (window.location.pathname === '/' && !sessionStorage.getItem('welcomeShown')) {
        setTimeout(function() {
            showToast('به موبارو خوش آمدید! ✨');
            sessionStorage.setItem('welcomeShown', '1');
        }, 1500);
    }

    console.log('%cموبارو فعال شد', 'color:#e11d48;font-weight:bold');
})();
