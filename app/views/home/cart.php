<?php $title = 'سبد خرید | موبارو'; ?>
<div class="min-h-screen bg-gradient-to-br from-rose-50 to-white pt-24">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center">
                <i class="fa-solid fa-cart-shopping text-rose-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-800">سبد خرید</h1>
                <?php
                $productCount = count(array_filter($cart, fn($i) => ($i['type'] ?? 'product') === 'product'));
                $courseCount = count(array_filter($cart, fn($i) => ($i['type'] ?? 'product') === 'course'));
                $parts = [];
                if ($productCount) {
                    $parts[] = "{$productCount} محصول";
                }
                if ($courseCount) {
                    $parts[] = "{$courseCount} دوره";
                }
                ?>
                <p class="text-sm text-zinc-500"><?= implode(' و ', $parts) ?> در سبد خرید شما</p>
            </div>
        </div>

        <?php if (empty($cart)) : ?>
            <div class="bg-white rounded-2xl p-16 text-center shadow-lg border border-zinc-100">
                <i class="fa-solid fa-bag-shopping text-6xl text-zinc-200 mb-4"></i>
                <p class="text-zinc-400 text-lg mb-6">سبد خرید شما خالی است</p>
                <a href="/shop" class="inline-flex items-center gap-2 px-8 py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all font-medium">
                    <i class="fa-solid fa-arrow-right"></i>
                    بازگشت به فروشگاه
                </a>
            </div>
        <?php else : ?>
            <div class="cart-page-layout">
                <div class="cart-page-items space-y-4">
                <?php foreach ($cart as $idx => $item) :
                    $isCourse = ($item['type'] ?? 'product') === 'course';
                    ?>
                <div class="bg-white rounded-2xl p-5 shadow-lg border border-zinc-100 flex items-center gap-5 cart-item" data-id="<?= $item['id'] ?>">
                    <div class="w-20 h-20 rounded-xl bg-zinc-50 flex-shrink-0 overflow-hidden">
                        <img src="/assets/images/<?= e($item['image'] ?? '') ?>" class="w-full h-full object-cover" onerror="this.src='/media/200/200/<?= $item['id'] ?>'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-zinc-800"><?= e($item['name']) ?></h3>
                            <?php if ($isCourse) : ?>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full">دوره</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-zinc-400"><?= e($item['category'] ?? '') ?></p>
                        <p class="text-rose-600 font-bold mt-1"><?= priceFormat($item['price'] * $item['qty']) ?></p>
                    </div>
                    <?php if ($isCourse) : ?>
                    <div class="text-sm text-zinc-400 flex-shrink-0">۱×</div>
                    <?php else : ?>
                    <div class="flex items-center gap-3">
                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['qty'] - 1 ?>)" class="w-9 h-9 rounded-xl border border-zinc-200 flex items-center justify-center hover:bg-zinc-50 transition-all" <?= $item['qty'] <= 1 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center font-semibold cart-qty"><?= $item['qty'] ?></span>
                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['qty'] + 1 ?>)" class="w-9 h-9 rounded-xl border border-zinc-200 flex items-center justify-center hover:bg-zinc-50 transition-all">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <button onclick="removeFromCart(<?= $item['id'] ?>, '<?= $item['type'] ?? 'product' ?>')" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <?php endforeach; ?>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-zinc-100 cart-summary">
                <div class="mb-4">
                    <label class="text-sm font-semibold text-zinc-600 mb-2 block">کد تخفیف</label>
                    <div class="flex gap-2">
                        <input type="text" id="coupon-input" placeholder="کد تخفیف را وارد کنید" class="flex-1 px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                        <button onclick="applyCoupon()" class="px-5 py-3 bg-zinc-800 text-white rounded-xl font-semibold text-sm hover:bg-zinc-900 transition-all">اعمال</button>
                    </div>
                    <div id="coupon-result" class="mt-2 text-sm"></div>
                    <input type="hidden" id="coupon-code" value="">
                </div>

                <?php if (Auth::check()) :
                    $wallet = Auth::user()['wallet'] ?? 0; ?>
                <div class="mb-4 p-4 bg-amber-50 rounded-xl border border-amber-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-semibold text-sm text-amber-800">کیف پول شما</span>
                        <span class="font-bold text-amber-700"><?= priceFormat($wallet) ?></span>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="use-wallet" onchange="toggleWalletPayment()">
                        <span class="text-sm text-amber-800">پرداخت از کیف پول</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-semibold text-zinc-600 mb-2 block">آدرس تحویل</label>
                    <div class="flex gap-2">
                        <select id="address-select" class="flex-1 px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                            <option value="">آدرس خود را انتخاب کنید</option>
                        </select>
                        <button type="button" id="editAddressBtn" onclick="editSelectedAddress()" class="px-3 py-3 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 transition-all hidden" title="ویرایش آدرس">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                    <button type="button" onclick="showAddressModal()" class="text-xs text-rose-600 mt-1 hover:underline">
                        <i class="fa-solid fa-plus ml-1"></i>افزودن آدرس جدید
                    </button>
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-between mb-2">
                    <span class="text-zinc-500">جمع کل</span>
                    <span class="text-2xl font-bold text-zinc-800" id="cart-total"><?= priceFormat($total) ?></span>
                </div>
                <div id="discount-row" class="hidden flex items-center justify-between mb-4 text-sm">
                    <span class="text-green-600">تخفیف</span>
                    <span class="text-green-600 font-bold" id="discount-amount">0</span>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <span class="text-zinc-500">مبلغ قابل پرداخت</span>
                    <span class="text-2xl font-bold text-rose-600" id="final-total"><?= priceFormat($total) ?></span>
                </div>
                <button onclick="checkout()" class="w-full py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl font-semibold transition-all">
                    <i class="fa-solid fa-check ml-2"></i>
                    ثبت سفارش و پرداخت
                </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="addressModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden" onclick="closeAddressModal(event)">
    <div class="bg-white rounded-[20px] p-6 w-full max-w-lg mx-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold" id="addressModalTitle">آدرس جدید</h3>
            <button onclick="closeAddressModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="addressForm" class="space-y-4">
            <input type="hidden" name="is_default" value="0">
            <input type="hidden" name="from_cart" value="1">
            <input type="hidden" name="address_id" id="editAddressId" value="0">
            <div>
                <label class="block text-sm font-semibold mb-1.5">عنوان آدرس</label>
                <input type="text" name="title" id="editTitle" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="مثلاً: منزل، محل کار" value="خانه">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">آدرس کامل</label>
                <textarea name="address" id="editAddress" rows="3" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="استان، شهر، خیابان، کوچه، پلاک" required></textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">شهر</label>
                    <input type="text" name="city" id="editCity" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="تهران">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">کد پستی</label>
                    <input type="text" name="zip_code" id="editZipCode" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">تلفن</label>
                    <input type="text" name="phone" id="editPhone" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" placeholder="اختیاری">
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_default" id="editIsDefault" value="1">
                <span class="text-sm font-medium">آدرس پیش‌فرض</span>
            </label>
            <button type="submit" class="w-full py-3.5 bg-gradient-to-l from-rose-500 to-rose-700 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all" id="addressSubmitBtn">ذخیره آدرس</button>
        </form>
    </div>
</div>

<script>
let selectedAddressId = null;

function loadAddresses() {
    fetch('/api/user/addresses')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('address-select');
            if (!sel) return;
            sel.innerHTML = '<option value="">آدرس خود را انتخاب کنید</option>';
            if (data.addresses) {
                data.addresses.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.textContent = a.title + ': ' + a.address.substring(0, 40) + (a.address.length > 40 ? '...' : '');
                    if (a.is_default) opt.selected = true;
                    sel.appendChild(opt);
                });
            }
            if (sel.value) selectedAddressId = sel.value;
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', loadAddresses);

document.getElementById('address-select')?.addEventListener('change', function() {
    selectedAddressId = this.value;
    const editBtn = document.getElementById('editAddressBtn');
    if (editBtn) {
        editBtn.classList.toggle('hidden', !this.value);
    }
});

function editSelectedAddress() {
    const id = document.getElementById('address-select')?.value;
    if (id) editAddressFromCart(id);
}

function toggleWalletPayment() {
    const checked = document.getElementById('use-wallet').checked;
    const totalText = document.getElementById('final-total').textContent;
    if (checked) {
        document.getElementById('final-total').dataset.original = totalText;
        document.getElementById('final-total').textContent = 'پرداخت با کیف پول';
    } else if (document.getElementById('final-total').dataset.original) {
        document.getElementById('final-total').textContent = document.getElementById('final-total').dataset.original;
    }
}

function showAddressModal() {
    document.getElementById('addressModalTitle').textContent = 'آدرس جدید';
    document.getElementById('addressForm').reset();
    document.getElementById('addressForm').querySelector('input[name="title"]').value = 'خانه';
    document.getElementById('editAddressId').value = '0';
    document.getElementById('addressSubmitBtn').textContent = 'ذخیره آدرس';
    document.getElementById('addressModal').classList.remove('hidden');
}

function editAddressFromCart(id) {
    const sel = document.getElementById('address-select');
    document.getElementById('addressModalTitle').textContent = 'ویرایش آدرس';
    document.getElementById('editAddressId').value = id;
    document.getElementById('addressSubmitBtn').textContent = 'به‌روزرسانی آدرس';

    fetch('/api/user/addresses')
        .then(r => r.json())
        .then(data => {
            if (data.addresses) {
                const addr = data.addresses.find(a => a.id == id);
                if (addr) {
                    document.getElementById('editTitle').value = addr.title || 'خانه';
                    document.getElementById('editAddress').value = addr.address || '';
                    document.getElementById('editCity').value = addr.city || 'تهران';
                    document.getElementById('editZipCode').value = addr.zip_code || '';
                    document.getElementById('editPhone').value = addr.phone || '';
                    document.getElementById('editIsDefault').checked = !!addr.is_default;
                }
            }
        })
        .catch(() => {});

    document.getElementById('addressModal').classList.remove('hidden');
}

function closeAddressModal() {
    document.getElementById('addressModal').classList.add('hidden');
}

document.getElementById('addressForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editAddressId').value;
    const formData = new FormData(this);
    const params = new URLSearchParams();
    formData.forEach((v, k) => params.append(k, v));
    const csrf = document.querySelector('meta[name="csrf"]');
    if (csrf) params.append('_csrf', csrf.getAttribute('content'));

    const url = id && id != '0' ? '/dashboard/address/update/' + id : '/dashboard/address/add';

    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params.toString()
    }).then(r => r.json()).then(d => {
        if (d.success || (d.message && !d.error)) {
            closeAddressModal();
            loadAddresses();
            showToast('آدرس با موفقیت ذخیره شد.', 'success');
        } else {
            showToast(d.error || 'خطا در ذخیره آدرس', 'error');
        }
    }).catch(() => showToast('خطا در ارتباط با سرور', 'error'));
});

function checkout() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-2"></i>در حال پردازش...';
    const couponCode = document.getElementById('coupon-code').value;
    let body = csrfParam();
    if (couponCode) body += '&coupon_code=' + encodeURIComponent(couponCode);
    const useWallet = document.getElementById('use-wallet')?.checked || false;
    if (useWallet) body += '&use_wallet=1';
    const addrId = selectedAddressId || document.getElementById('address-select')?.value || '';
    if (addrId) body += '&address_id=' + encodeURIComponent(addrId);
    fetch('/shop/cart/checkout', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => {
            if (d.require_login) { window.location.href = '/login?redirect=/cart'; return; }
            if (d.payment_required && d.redirect) {
                window.location.href = d.redirect;
            } else {
                showToast(d.message || d.error, d.success ? 'success' : 'error');
                if (d.success) setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(() => { showToast('خطا در ثبت سفارش', 'error'); btn.disabled = false; btn.innerHTML = 'ثبت سفارش و پرداخت'; });
}

function updateCartQty(productId, qty) {
    if (qty < 1) return;
    const body = 'product_id=' + productId + '&qty=' + qty + '&' + csrfParam();
    fetch('/shop/cart/update', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => showToast('خطا در بروزرسانی', 'error'));
}

function removeFromCart(productId, type) {
    const body = 'product_id=' + productId + '&type=' + (type || 'product') + '&' + csrfParam();
    fetch('/shop/cart/remove', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => showToast('خطا در حذف', 'error'));
}

function applyCoupon() {
    const code = document.getElementById('coupon-input').value.trim();
    if (!code) { showToast('کد تخفیف را وارد کنید', 'error'); return; }
    const body = 'code=' + encodeURIComponent(code) + '&' + csrfParam();
    fetch('/shop/coupon/verify', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                document.getElementById('coupon-result').innerHTML = '<span class="text-green-600">' + d.message + '</span>';
                document.getElementById('coupon-code').value = code;
                document.getElementById('discount-row').classList.remove('hidden');
                document.getElementById('discount-amount').textContent = d.discount_formatted;
                document.getElementById('final-total').textContent = d.total_after_formatted;
            } else {
                document.getElementById('coupon-result').innerHTML = '<span class="text-red-500">' + d.error + '</span>';
            }
        })
        .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
