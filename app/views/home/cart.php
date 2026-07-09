<?php $title = 'سبد خرید | موبارو'; ?>
<div class="min-h-screen bg-gradient-to-br from-rose-50 to-white pt-24">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center">
                <i class="fa-solid fa-cart-shopping text-rose-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-800">سبد خرید</h1>
                <p class="text-sm text-zinc-500"><?= count($cart) ?> محصول در سبد خرید شما</p>
            </div>
        </div>

        <?php if (empty($cart)): ?>
            <div class="bg-white rounded-2xl p-16 text-center shadow-lg border border-zinc-100">
                <i class="fa-solid fa-bag-shopping text-6xl text-zinc-200 mb-4"></i>
                <p class="text-zinc-400 text-lg mb-6">سبد خرید شما خالی است</p>
                <a href="/shop" class="inline-flex items-center gap-2 px-8 py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl transition-all font-medium">
                    <i class="fa-solid fa-arrow-right"></i>
                    بازگشت به فروشگاه
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4 mb-8">
                <?php foreach ($cart as $item): ?>
                <div class="bg-white rounded-2xl p-5 shadow-lg border border-zinc-100 flex items-center gap-5 cart-item" data-id="<?= $item['id'] ?>">
                    <div class="w-20 h-20 rounded-xl bg-zinc-50 flex-shrink-0 overflow-hidden">
                        <img src="/assets/images/<?= e($item['image'] ?? '') ?>" class="w-full h-full object-cover" onerror="this.src='https://picsum.photos/seed/p<?= $item['id'] ?>/200/200'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-zinc-800"><?= e($item['name']) ?></h3>
                        <p class="text-sm text-zinc-400"><?= e($item['category'] ?? '') ?></p>
                        <p class="text-rose-600 font-bold mt-1"><?= priceFormat($item['price'] * $item['qty']) ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['qty'] - 1 ?>)" class="w-9 h-9 rounded-xl border border-zinc-200 flex items-center justify-center hover:bg-zinc-50 transition-all" <?= $item['qty'] <= 1 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center font-semibold cart-qty"><?= $item['qty'] ?></span>
                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['qty'] + 1 ?>)" class="w-9 h-9 rounded-xl border border-zinc-200 flex items-center justify-center hover:bg-zinc-50 transition-all">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                    <button onclick="removeFromCart(<?= $item['id'] ?>)" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-zinc-100">
                <div class="flex items-center justify-between mb-6">
                    <span class="text-zinc-500">جمع کل</span>
                    <span class="text-2xl font-bold text-zinc-800" id="cart-total"><?= priceFormat($total) ?></span>
                </div>
                <button onclick="checkout()" class="w-full py-4 bg-zinc-900 hover:bg-black text-white rounded-2xl font-semibold transition-all">
                    <i class="fa-solid fa-check ml-2"></i>
                    ثبت سفارش
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateCartQty(productId, qty) {
    if (qty < 1) return;
    const body = 'product_id=' + productId + '&qty=' + qty + '&' + csrfParam();
    fetch('/shop/cart/update', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => showToast('خطا در بروزرسانی', 'error'));
}

function removeFromCart(productId) {
    const body = 'product_id=' + productId + '&' + csrfParam();
    fetch('/shop/cart/remove', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => showToast('خطا در حذف', 'error'));
}

function checkout() {
    const body = csrfParam();
    fetch('/shop/cart/checkout', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(d => {
            if (d.require_login) { window.location.href = '/login?redirect=/cart'; return; }
            showToast(d.message || d.error, d.success ? 'success' : 'error');
            if (d.success) setTimeout(() => location.reload(), 1500);
        })
        .catch(() => showToast('خطا در ثبت سفارش', 'error'));
}
</script>
