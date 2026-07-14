# Cart & Checkout Bug Fixes

## Files to modify

### 1. `app/Controllers/DashboardController.php` — `addAddress()` (line 286)

**Problem:** Always returns redirect (`back()`), never JSON. Cart page JS expects JSON and fails.

**Fix:** Add `from_cart` handling like `updateAddress()` already has:

```php
public function addAddress(): void
{
    Auth::requireAuth();
    $this->verifyCsrf();

    $title = sanitize($_POST['title'] ?? 'خانه');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? 'تهران');
    $zipCode = sanitize($_POST['zip_code'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($address)) {
        if (isset($_POST['from_cart']) && $_POST['from_cart'] === '1') {
            $this->json(['error' => 'آدرس را وارد کنید.'], 400);
        } else {
            $this->redirectWithErrors('/dashboard/addresses', ['address' => 'آدرس را وارد کنید.']);
        }
        return;
    }

    $isDefault = (int) ($_POST['is_default'] ?? 0);
    if ($isDefault) {
        Database::update('addresses', ['is_default' => 0], 'user_id = :uid', ['uid' => Auth::id()]);
    }

    Database::insert('addresses', [
        'user_id' => Auth::id(),
        'title' => $title,
        'address' => $address,
        'city' => $city,
        'zip_code' => $zipCode,
        'phone' => $phone,
        'is_default' => $isDefault,
    ]);

    if (isset($_POST['from_cart']) && $_POST['from_cart'] === '1') {
        $this->json(['success' => true, 'message' => 'آدرس جدید اضافه شد.']);
    } else {
        flash('success', 'آدرس جدید اضافه شد.');
        back();
    }
}
```

---

### 2. `app/views/home/cart.php` — Add address validation + required attribute

**Find** the `<select id="address-select">` (line 101) — add `required`:
```html
<select id="address-select" required class="...">
```

**Find** the `checkout()` function (line 294) — validate address before submit:
```javascript
function checkout() {
    const btn = event.target;
    const addrId = selectedAddressId || document.getElementById('address-select')?.value || '';
    if (!addrId) {
        showToast('لطفاً یک آدرس تحویل انتخاب کنید.', 'error');
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-2"></i>در حال پردازش...';
    const couponCode = document.getElementById('coupon-code').value;
    let body = csrfParam();
    if (couponCode) body += '&coupon_code=' + encodeURIComponent(couponCode);
    const useWallet = document.getElementById('use-wallet')?.checked || false;
    if (useWallet) body += '&use_wallet=1';
    body += '&address_id=' + encodeURIComponent(addrId);
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
```

Also move `const addrId = ...` before `btn.disabled` so validation happens before disabling the button.

---

### 3. `app/Controllers/ShopController.php` — `checkout()` wallet insufficient balance

**Find** lines 476-484. Change:
```php
$useWallet = !empty($_POST['use_wallet']);
$walletBalance = (int) ($user['wallet'] ?? 0);
$paymentStatus = 'pending';
$paymentMethod = null;

if ($useWallet && $walletBalance >= $finalTotal) {
    $paymentMethod = 'wallet';
    $paymentStatus = 'paid';
}
```

To:
```php
$useWallet = !empty($_POST['use_wallet']);
$walletBalance = (int) ($user['wallet'] ?? 0);
$paymentStatus = 'pending';
$paymentMethod = null;

if ($useWallet) {
    if ($walletBalance < $finalTotal) {
        Database::query("DELETE FROM orders WHERE id = ?", [$orderId]);
        Database::query("DELETE FROM order_items WHERE order_id = ?", [$orderId]);
        $this->json(['error' => 'موجودی کیف پول کافی نیست. لطفاً کیف پول خود را شارژ کنید.'], 400);
        return;
    }
    $paymentMethod = 'wallet';
    $paymentStatus = 'paid';
}
```

This way if wallet is selected but insufficient, we return an error BEFORE the order is finalized. The order is deleted since it was already inserted.

---

## Commit message:
```
Fix checkout: addAddress returns JSON for cart modal, validate address required, error on insufficient wallet
```
