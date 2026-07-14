# Cart & Checkout Bug Fixes — Improved

## Change: Move wallet check BEFORE order creation

The wallet balance check needs to happen BEFORE creating the order (not after as in the original code), so we avoid having to delete the order.

In `ShopController.php:checkout()`, restructure:

**Current flow:**
```
1. calculate total, coupon → finalTotal
2. INSERT order
3. INSERT order_items  
4. INSERT coupon usage
5. check wallet → insufficient → need to DELETE order
```

**New flow:**
```
1. calculate total, coupon → finalTotal
2. check wallet → insufficient → return error BEFORE any DB writes
3. INSERT order
4. INSERT order_items
5. INSERT coupon usage
6. proceed with payment (wallet deduct or ZarinPal)
```

## Edits needed

### Edit 1: `app/Controllers/DashboardController.php` — addAddress()

Add `from_cart` handling (same pattern as updateAddress).

### Edit 2: `app/views/home/cart.php`

1. Add `required` to `<select id="address-select">`
2. In `checkout()`: validate `addrId` before disabling button

### Edit 3: `app/Controllers/ShopController.php` — checkout()

Move wallet balance check before order creation:

```php
$finalTotal = $total - $couponDiscount;

// --- WALLET CHECK BEFORE ORDER CREATION ---
$useWallet = !empty($_POST['use_wallet']);
$walletBalance = (int) (Auth::user()['wallet'] ?? 0);

if ($useWallet && $walletBalance < $finalTotal) {
    $this->json(['error' => 'موجودی کیف پول کافی نیست. لطفاً کیف پول خود را شارژ کنید.'], 400);
    return;
}

$paymentMethod = $useWallet ? 'wallet' : null;
$paymentStatus = $useWallet ? 'paid' : 'pending';

$trackingCode = 'MB-' . date('Ymd') . '-' . rand(100, 999);
$user = Auth::user();
// ... rest of order creation ...
```

Remove the old wallet check block (lines 476-484) entirely.
