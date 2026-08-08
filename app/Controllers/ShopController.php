<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\SEOService;
use App\Auth;
use App\StructuredData;
use App\Services\SmsService;
use App\Services\ZarinPal;
use App\Services\PaymentLogger;
use App\Services\OrderEffects;
use Throwable;

class ShopController extends BaseController
{
    private const WISHLIST_QUERY = 'SELECT product_id as id FROM wishlist WHERE user_id = ?';
    private const LAYOUT_HEADER = '/../views/layouts/header.php';
    private const LAYOUT_FOOTER = '/../views/layouts/footer.php';
    private const WHERE_ID = 'id = :id';
    private const CURRENCY_SUFFIX = ' تومان';

    private static array $columnCache = [];

    private function hasColumn(string $table, string $column): bool
    {
        if (!isset(self::$columnCache[$table][$column])) {
            $result = Database::fetch(
                "SELECT COUNT(*) AS cnt
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
                [$table, $column]
            );
            self::$columnCache[$table][$column] = (int) ($result['cnt'] ?? 0) > 0;
        }

        return self::$columnCache[$table][$column];
    }

    private function getFacets(): array
    {
        return Cache::remember('shop_facets', Config::get('cache.ttl.page', 600), function () {
            $categoryRows = Database::fetchAll(
                "SELECT category, COUNT(*) as cnt FROM products WHERE is_active = 1 GROUP BY category ORDER BY cnt DESC"
            );
            $brandRows = Database::fetchAll(
                "SELECT brand, COUNT(*) as cnt FROM products WHERE is_active = 1 AND brand IS NOT NULL AND brand != '' GROUP BY brand ORDER BY cnt DESC"
            );
            return compact('categoryRows', 'brandRows');
        }, 'products');
    }

    public function index(): void
    {
        $category = sanitize($_GET['category'] ?? 'all');
        $brand = sanitize($_GET['brand'] ?? 'all');
        $search = sanitize($_GET['search'] ?? '');
        $sort = sanitize($_GET['sort'] ?? 'newest');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE p.is_active = 1";
        $params = [];

        if ($category !== 'all') {
            $where .= " AND p.category = ?";
            $params[] = $category;
        }
        if ($brand !== 'all') {
            $where .= " AND p.brand = ?";
            $params[] = $brand;
        }
        if ($search !== '') {
            $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
            $s = likePattern($search);
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $orderClause = match ($sort) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'rating' => 'p.rating DESC',
            'popular' => $this->hasColumn('products', 'reviews') ? 'p.reviews DESC' : 'p.rating DESC',
            default => 'p.id DESC',
        };

        $priceMin = (int) ($_GET['price_min'] ?? 0);
        $priceMax = (int) ($_GET['price_max'] ?? 0);
        if ($priceMin > 0) {
            $where .= " AND p.price >= ?";
            $params[] = $priceMin;
        }
        if ($priceMax > 0) {
            $where .= " AND p.price <= ?";
            $params[] = $priceMax;
        }

        $rating = (int) ($_GET['rating'] ?? 0);
        if ($rating > 0) {
            $where .= " AND p.rating >= ?";
            $params[] = $rating;
        }

        $isSale = (int) ($_GET['is_sale'] ?? 0);
        if ($isSale === 1) {
            $where .= " AND p.is_sale = 1";
        }

        $isNew = (int) ($_GET['is_new'] ?? 0);
        if ($isNew === 1) {
            $where .= " AND p.is_new = 1";
        }

        $inStock = (int) ($_GET['in_stock'] ?? 0);
        if ($inStock === 1) {
            $where .= " AND p.stock > 0";
        }

        $cacheKey = 'shop_' . hash('sha256', serialize([$category, $brand, $search, $sort, $priceMin, $priceMax, $rating, $isSale, $isNew, $inStock, $page]));
        $cached = Cache::remember($cacheKey, Config::get('cache.ttl.page', 600), function () use ($where, $params, $orderClause, $perPage, $offset) {
            $countRow = Database::fetch("SELECT COUNT(*) as cnt FROM products p {$where}", $params);
            $totalProducts = (int) ($countRow['cnt'] ?? 0);
            $totalPages = max(1, (int) ceil($totalProducts / $perPage));
            $allTotalRow = Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE is_active = 1");
            $allTotal = (int) ($allTotalRow['cnt'] ?? 0);
            $products = Database::fetchAll(
                "SELECT p.* FROM products p {$where} ORDER BY {$orderClause} LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return compact('products', 'totalProducts', 'totalPages', 'allTotal');
        }, 'products');

        $products = $cached['products'];
        $totalProducts = $cached['totalProducts'];
        $totalPages = $cached['totalPages'];
        $allTotal = $cached['allTotal'];

        $facets = $this->getFacets();
        $cart = $_SESSION['cart'] ?? [];
        if (Auth::check()) {
            $wRows = Database::fetchAll(self::WISHLIST_QUERY, [Auth::id()]);
            $wishlist = array_column($wRows, 'id');
        } else {
            $wishlist = $_SESSION['wishlist'] ?? [];
        }
        $settings = Settings::all();
        $seo = SEOService::forPage('shop');

        $this->view('shop/index', [
            'products' => $products, 'category' => $category, 'brand' => $brand,
            'search' => $search, 'sort' => $sort, 'page' => $page,
            'totalPages' => $totalPages, 'totalProducts' => $totalProducts,
            'allTotal' => $allTotal,
            'cart' => $cart, 'wishlist' => $wishlist, 'settings' => $settings,
            'seo' => $seo,
            'priceMin' => $priceMin, 'priceMax' => $priceMax, 'rating' => $rating,
            'isSale' => $isSale, 'isNew' => $isNew, 'inStock' => $inStock,
        ] + $facets);
    }

    public function show(int $id): void
    {
        $product = Cache::remember('product_' . $id, Config::get('cache.ttl.page', 600), function () use ($id) {
            $p = Database::fetch("SELECT * FROM products WHERE id = ? AND is_active = 1", [$id]);
            if ($p) {
                Cache::tag('products', 'product_' . $id);
            }
            return $p;
        });
        if (!$product) {
            http_response_code(404);
            $settings = Settings::all();
            require_once __DIR__ . self::LAYOUT_HEADER;
            require_once __DIR__ . '/../views/errors/404.php';
            require_once __DIR__ . self::LAYOUT_FOOTER;
            return;
        }

        $related = Database::fetchAll(
            "SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 LIMIT 4",
            [$product['category'], $id]
        );

        $reviews = Database::fetchAll(
            "SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC",
            [$id]
        );
        $avgRating = !empty($reviews) ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
        $reviewCount = count($reviews);

        $gallery = Cache::remember('product_gallery_' . $id, Config::get('cache.ttl.page', 600), function () use ($id) {
            $rows = Database::fetchAll(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id",
                [$id]
            );
            Cache::tag('products', 'product_gallery_' . $id);
            return $rows;
        });

        $productMedia = null;
        if (!empty($product['video_url']) && $product['video_type'] === 'upload') {
            $mediaCacheKey = 'product_media_' . $id;
            $productMedia = Cache::remember($mediaCacheKey, Config::get('cache.ttl.page', 600), function () use ($product) {
                $row = Database::fetch("SELECT id FROM media WHERE filepath = ?", [ltrim($product['video_url'], '/')]);
                Cache::tag('products', 'product_media_' . $product['id']);
                return $row;
            });
        }

        $settings = Settings::all();
        $cart = $_SESSION['cart'] ?? [];
        $seo = SEOService::forPage('shop');

        $jsonLd = StructuredData::render(
            StructuredData::organization(),
            StructuredData::breadcrumb([
                ['name' => 'خانه', 'url' => url('/')],
                ['name' => (string) $product['name'], 'url' => url('/product/' . $id)],
            ]),
            StructuredData::product($product, $reviews, (float) $avgRating, $reviewCount)
        );

        $this->view('shop/detail', compact('product', 'related', 'settings', 'cart', 'reviews', 'avgRating', 'reviewCount', 'gallery', 'productMedia', 'seo', 'jsonLd'));
    }

    public function postReview(int $productId): void
    {
        header(self::CONTENT_TYPE_JSON);

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'لطفاً ابتدا وارد شوید.']);
            exit;
        }
        $this->verifyCsrf();

        $product = Database::fetch("SELECT id FROM products WHERE id = ? AND is_active = 1", [$productId]);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'محصول یافت نشد.']);
            exit;
        }

        $rating = (int) ($_POST['rating'] ?? 0);
        $text = trim($_POST['text'] ?? '');

        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'امتیاز باید بین ۱ تا ۵ باشد.']);
            exit;
        }

        if (empty($text)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'متن نظر را وارد کنید.']);
            exit;
        }

        $user = Auth::user();
        $userName = ($user['name'] ?? '') . ' ' . ($user['family'] ?? '');

        $existing = Database::fetch(
            "SELECT id FROM reviews WHERE product_id = ? AND user_name = ?",
            [$productId, trim($userName)]
        );
        if ($existing) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'شما قبلاً برای این محصول نظر ثبت کرده‌اید.']);
            exit;
        }

        Database::insert('reviews', [
            'product_id' => $productId,
            'user_id' => $user['id'],
            'user_name' => trim($userName ?: $user['phone']),
            'rating' => $rating,
            'text' => sanitize($text),
        ]);

        $avg = Database::fetch(
            "SELECT COALESCE(AVG(rating), 0) as avg FROM reviews WHERE product_id = ?",
            [$productId]
        )['avg'];
        $cnt = Database::fetch(
            "SELECT COUNT(*) as cnt FROM reviews WHERE product_id = ?",
            [$productId]
        )['cnt'];
        $productUpdate = ['rating' => round((float) $avg, 1)];
        if ($this->hasColumn('products', 'reviews')) {
            $productUpdate['reviews'] = $cnt;
        }
        Database::update('products', $productUpdate, self::WHERE_ID, ['id' => $productId]);

        Cache::forget('product_' . $productId);
        Cache::flushByTag('products');

        echo json_encode(['success' => true, 'message' => 'نظر شما با موفقیت ثبت شد.']);
        exit;
    }

    public function toggleWishlist(): void
    {
        $this->verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        if (!$productId) {
            $this->json(['error' => 'محصول نامعتبر'], 400);
            return;
        }

        if (Auth::check()) {
            $existing = Database::fetch(
                "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
                [Auth::id(), $productId]
            );
            if ($existing) {
                Database::delete('wishlist', 'id = ?', [$existing['id']]);
                $this->json(['success' => true, 'action' => 'removed', 'wishlist_count' => $this->getWishlistCount()]);
            } else {
                Database::insert('wishlist', ['user_id' => Auth::id(), 'product_id' => $productId]);
                $this->json(['success' => true, 'action' => 'added', 'wishlist_count' => $this->getWishlistCount()]);
            }
        } else {
            if (!isset($_SESSION['wishlist'])) {
                $_SESSION['wishlist'] = [];
            }
            $idx = array_search($productId, $_SESSION['wishlist']);
            if ($idx !== false) {
                array_splice($_SESSION['wishlist'], $idx, 1);
                $this->json(['success' => true, 'action' => 'removed', 'wishlist_count' => count($_SESSION['wishlist'])]);
            } else {
                $_SESSION['wishlist'][] = $productId;
                $this->json(['success' => true, 'action' => 'added', 'wishlist_count' => count($_SESSION['wishlist'])]);
            }
        }
    }

    private function getWishlistCount(): int
    {
        if (Auth::check()) {
            $result = Database::fetch("SELECT COUNT(*) as cnt FROM wishlist WHERE user_id = ?", [Auth::id()]);
            return (int) ($result['cnt'] ?? 0);
        }
        return count($_SESSION['wishlist'] ?? []);
    }

    public function wishlist(): void
    {
        if (Auth::check()) {
            $rows = Database::fetchAll(
                self::WISHLIST_QUERY,
                [Auth::id()]
            );
            $ids = array_column($rows, 'id');
        } else {
            $ids = $_SESSION['wishlist'] ?? [];
        }
        if (empty($ids)) {
            $products = [];
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $products = Database::fetchAll("SELECT * FROM products WHERE id IN ({$placeholders}) AND is_active = 1", $ids);
        }
        $cart = $_SESSION['cart'] ?? [];
        $settings = Settings::all();
        $this->view('shop/wishlist', compact('products', 'cart', 'settings'));
    }

    public function wishlistData(): void
    {
        if (Auth::check()) {
            $rows = Database::fetchAll(
                self::WISHLIST_QUERY,
                [Auth::id()]
            );
            $ids = array_column($rows, 'id');
        } else {
            $ids = $_SESSION['wishlist'] ?? [];
        }
        $products = [];
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = Database::fetchAll("SELECT id, name, price, old_price, image, brand FROM products WHERE id IN ({$placeholders}) AND is_active = 1", $ids);
            foreach ($rows as $p) {
                $products[] = [
                    'id' => (int)$p['id'],
                    'name' => $p['name'],
                    'price' => (int)$p['price'],
                    'old_price' => (int)($p['old_price'] ?? 0),
                    'image' => '/assets/images/' . e($p['image']),
                    'brand' => $p['brand'] ?? '',
                ];
            }
        }
        $this->json(['items' => $products]);
    }

    public function cartSummary(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as &$item) {
            $total += $item['price'] * $item['qty'];
        }
        $this->json([
            'cart_count' => array_sum(array_column($_SESSION['cart'] ?? [], 'qty')),
            'total' => $total,
            'total_formatted' => number_format($total) . self::CURRENCY_SUFFIX,
        ]);
    }

    public function cart(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        $this->view('home/cart', compact('cart', 'total'));
    }

    public function addToCart(): void
    {
        $this->verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        if (!$productId) {
            $this->json(['error' => 'محصول نامعتبر'], 400);
            return;
        }

        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND is_active = 1", [$productId]);
        if (!$product) {
            $this->json(['error' => 'محصول یافت نشد'], 404);
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $productId) {
                $item['qty']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => (int) $product['price'],
                'old_price' => (int) ($product['old_price'] ?? 0),
                'image' => $product['image'],
                'category' => $product['category'],
                'brand' => $product['brand'] ?? '',
                'qty' => 1,
                'type' => 'product',
            ];
        }

        $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
        $this->json([
            'success' => true,
            'message' => $product['name'] . ' به سبد خرید اضافه شد',
            'cart_count' => $cartCount,
        ]);
    }

    public function updateCart(): void
    {
        $this->verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['qty'] ?? 1));

        if (!isset($_SESSION['cart'])) {
            $this->json(['error' => 'سبد خرید خالی است'], 400);
            return;
        }

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $productId) {
                if (($item['type'] ?? 'product') === 'course') {
                    $this->json(['error' => 'تعداد دوره قابل تغییر نیست'], 400);
                    return;
                }
                $item['qty'] = $qty;
                break;
            }
        }

        $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
        $this->json(['success' => true, 'cart_count' => $cartCount]);
    }

    public function removeFromCart(): void
    {
        $this->verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $itemType = sanitize($_POST['type'] ?? 'product');

        if (isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values(
                array_filter($_SESSION['cart'], fn($item) =>
                    !($item['id'] === $productId && ($item['type'] ?? 'product') === $itemType))
            );
        }

        $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
        $this->json(['success' => true, 'cart_count' => $cartCount]);
    }

    public function checkout(): void
    {
        if (!Auth::check()) {
            $this->json(['require_login' => true, 'error' => 'لطفاً ابتدا وارد شوید.'], 401);
            return;
        }
        $this->verifyCsrf();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->json(['error' => 'سبد خرید خالی است'], 400);
            return;
        }

        $user = Auth::user();
        $couponCode = sanitize($_POST['coupon_code'] ?? '');
        $useWallet = !empty($_POST['use_wallet']);
        $addressResult = $this->resolveAddress($cart, $user['id']);

        $idemKey = sha1((string) $user['id'] . '|' . $this->cartFingerprint($cart) . '|' . $couponCode . '|' . ($useWallet ? 'wallet' : 'gateway'));

        $orderId = 0;
        $finalTotal = 0;
        $trackingCode = '';
        $paymentStatus = 'pending';
        $paymentMethod = null;

        Database::beginTransaction();
        try {
            $existing = Database::fetch(
                "SELECT * FROM orders WHERE user_id = ? AND idempotency_key = ? AND status = 'pending' AND payment_status = 'pending' LIMIT 1",
                [$user['id'], $idemKey]
            );

            if ($existing) {
                $orderId = (int) $existing['id'];
                $finalTotal = (int) $existing['total'];
                $trackingCode = (string) $existing['tracking_code'];
                Database::commit();
            } else {
                [$items, $total] = $this->validateCartItems($cart);

                $couponResult = $this->validateCoupon($couponCode, $total);
                if ($couponResult['error']) {
                    Database::rollback();
                    $this->json(['error' => $couponResult['error']], 400);
                    return;
                }
                $couponDiscount = $couponResult['discount'];
                $finalTotal = max(0, $total - $couponDiscount);
                $trackingCode = 'MB-' . date('Ymd') . '-' . str_pad((string) random_int(1000, 99999), 5, '0', STR_PAD_LEFT);

                $walletRow = Database::fetch("SELECT wallet FROM users WHERE id = ? FOR UPDATE", [$user['id']]);
                $walletBalance = (int) ($walletRow['wallet'] ?? 0);
                if ($useWallet && $finalTotal > 0 && $walletBalance < $finalTotal) {
                    Database::rollback();
                    $this->json(['error' => 'موجودی کیف پول کافی نیست. لطفاً کیف پول خود را شارژ کنید.'], 400);
                    return;
                }

                if ($finalTotal <= 0) {
                    $paymentStatus = 'paid';
                    $paymentMethod = 'free';
                } elseif ($useWallet) {
                    $paymentStatus = 'paid';
                    $paymentMethod = 'wallet';
                } else {
                    $paymentStatus = 'pending';
                    $paymentMethod = null;
                }

                $orderId = Database::insert('orders', [
                    'user_id' => $user['id'],
                    'total' => $finalTotal,
                    'discount' => $couponDiscount,
                    'coupon_code' => $couponCode ?: null,
                    'coupon_discount' => $couponDiscount,
                    'status' => $paymentStatus === 'paid' ? 'processing' : 'pending',
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentMethod,
                    'tracking_code' => $trackingCode,
                    'address' => $addressResult['text'] ?: null,
                    'postal_code' => $addressResult['postal'] ?: null,
                    'idempotency_key' => $idemKey,
                ]);

                foreach ($items as $item) {
                    Database::insert('order_items', [
                        'order_id' => $orderId,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'price' => $item['price'],
                        'quantity' => $item['qty'],
                        'item_type' => $item['type'] ?? 'product',
                    ]);
                }

                if ($paymentStatus === 'paid') {
                    if ($paymentMethod === 'wallet') {
                        Database::update('users', ['wallet' => $walletBalance - $finalTotal], self::WHERE_ID, ['id' => $user['id']]);
                        Database::insert('transactions', [
                            'user_id' => $user['id'],
                            'type' => 'wallet_withdraw',
                            'amount' => $finalTotal,
                            'description' => "پرداخت سفارش {$trackingCode}",
                            'payment_status' => 'paid',
                        ]);
                    }
                    OrderEffects::apply($orderId, $trackingCode, $user['id'], $finalTotal, $couponCode, $couponDiscount);
                }

                Database::commit();
            }
        } catch (\RuntimeException $e) {
            Database::rollback();
            $this->json(['error' => $e->getMessage()], 400);
            return;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }

        if ($paymentStatus === 'paid') {
            $_SESSION['cart'] = [];
            $_SESSION['user'] = Database::fetch("SELECT * FROM users WHERE id = ?", [$user['id']]);
            $this->json([
                'success' => true,
                'payment_required' => false,
                'message' => 'سفارش با موفقیت ثبت شد' . ($paymentMethod === 'wallet' ? ' و از کیف پول کسر گردید.' : '.'),
            ]);
            return;
        }

        $this->processZarinPalPayment($orderId, $finalTotal, $trackingCode, $user);
    }

    private function validateCoupon(string $couponCode, int $total): array
    {
        if ($couponCode === '') {
            return ['error' => null, 'discount' => 0, 'code' => ''];
        }

        $coupon = Database::fetch("SELECT * FROM coupons WHERE code = ? AND is_active = 1", [$couponCode]);

        if (!$coupon) {
            return ['error' => 'کد تخفیف نامعتبر است.', 'discount' => 0, 'code' => ''];
        }

        $expires = $coupon['expires_at'];
        if ($expires && strtotime($expires) < time()) {
            return ['error' => 'کد تخفیف منقضی شده است.', 'discount' => 0, 'code' => ''];
        }

        if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['error' => 'کد تخفیف به حداکثر تعداد استفاده رسیده است.', 'discount' => 0, 'code' => ''];
        }

        if ($coupon['min_order'] > 0 && $total < $coupon['min_order']) {
            return ['error' => 'حداقل مبلغ خرید برای این کد تخفیف ' . number_format((int) $coupon['min_order']) . ' تومان است.', 'discount' => 0, 'code' => ''];
        }

        $discount = $coupon['discount_type'] === 'percentage'
            ? (int) min($total * $coupon['discount_value'] / 100, $total)
            : (int) min($coupon['discount_value'], $total);

        return ['error' => null, 'discount' => $discount, 'code' => $couponCode];
    }

    private function resolveAddress(array $cart, int|string $userId): array
    {
        $userId = (int) $userId;
        $addressId = (int) ($_POST['address_id'] ?? 0);
        $hasPhysicalProducts = !empty(array_filter($cart, fn($item) => ($item['type'] ?? 'product') === 'product'));

        if ($hasPhysicalProducts && !$addressId) {
            $this->json(['error' => 'آدرس تحویل برای محصولات فیزیکی الزامی است.'], 422);
            exit;
        }

        $addressText = '';
        $postalCode = '';
        if ($addressId) {
            $addr = Database::fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$addressId, $userId]);
            if ($addr) {
                $addressText = $addr['address'] . '، ' . ($addr['city'] ?? '');
                $postalCode = $addr['zip_code'] ?? '';
            }
        }

        return ['text' => $addressText, 'postal' => $postalCode];
    }

    private function cartFingerprint(array $cart): string
    {
        $parts = array_map(fn($item) => ($item['type'] ?? 'product') . ':' . $item['id'] . ':' . $item['qty'] . ':' . $item['price'], $cart);
        sort($parts);
        return md5(implode('|', $parts));
    }

    private function validateCartItems(array $cart): array
    {
        $items = [];
        $total = 0;
        foreach ($cart as $item) {
            $type = $item['type'] ?? 'product';
            $qty = max(1, (int) ($item['qty'] ?? 1));

            if ($type === 'course') {
                $course = Database::fetch(
                    "SELECT id, title, price, is_free, is_active FROM courses WHERE id = ? FOR UPDATE",
                    [(int) $item['id']]
                );
                if (!$course || (int) $course['is_active'] !== 1) {
                    throw new \RuntimeException('دوره «' . ($item['name'] ?? '') . '» دیگر در دسترس نیست.');
                }
                if ((int) $course['is_free'] === 1) {
                    throw new \RuntimeException('دوره «' . $course['title'] . '» هم‌اکنون رایگان است.');
                }
                $price = (int) $course['price'];
                $items[] = ['id' => (int) $course['id'], 'name' => $course['title'], 'price' => $price, 'qty' => $qty, 'type' => 'course'];
            } else {
                $product = Database::fetch(
                    "SELECT id, name, price, stock, is_active FROM products WHERE id = ? FOR UPDATE",
                    [(int) $item['id']]
                );
                if (!$product || (int) $product['is_active'] !== 1) {
                    throw new \RuntimeException('محصول «' . ($item['name'] ?? '') . '» دیگر در دسترس نیست.');
                }
                $stock = (int) ($product['stock'] ?? 0);
                if ($stock < $qty) {
                    throw new \RuntimeException('موجودی محصول «' . $product['name'] . '» کافی نیست (' . $qty . ' عدد درخواستی، ' . $stock . ' عدد موجود).');
                }
                $price = (int) $product['price'];
                $items[] = ['id' => (int) $product['id'], 'name' => $product['name'], 'price' => $price, 'qty' => $qty, 'type' => 'product'];
            }

            $total += $price * $qty;
        }

        return [$items, $total];
    }

    private function processZarinPalPayment(int $orderId, int $finalTotal, string $trackingCode, array $user): void
    {
        $zpl = new ZarinPal();
        $callbackUrl = url('/shop/payment/callback');
        $result = $zpl->requestPayment($finalTotal, "سفارش {$trackingCode}", $callbackUrl, null, $user['phone'] ?? null);

        PaymentLogger::log([
            'user_id' => $user['id'] ?? null,
            'order_id' => $orderId,
            'action' => 'request',
            'amount' => $finalTotal,
            'authority' => $result['authority'] ?? null,
            'status' => $result['status'] ? 'sent' : 'failed',
            'request_data' => $zpl->getLastRequest(),
            'response_data' => $zpl->getLastResponse(),
        ]);

        if ($result['status']) {
            Database::update('orders', [
                'authority' => $result['authority'],
                'payment_method' => 'zarinpal',
            ], self::WHERE_ID, ['id' => $orderId]);
            $_SESSION['pending_payment_order_id'] = $orderId;
            $this->json([
                'success' => true,
                'payment_required' => true,
                'redirect' => $result['redirect_url'],
                'message' => 'در حال انتقال به درگاه پرداخت...',
            ]);
        } else {
            $this->json([
                'success' => false,
                'payment_required' => false,
                'message' => $result['message'] ?? 'خطا در اتصال به درگاه پرداخت. لطفاً دوباره تلاش کنید.',
                'order_id' => $orderId,
            ], 500);
        }
    }

    public function paymentCallback(): void
    {
        Auth::requireAuth();

        $authority = $_GET['Authority'] ?? '';
        $status = $_GET['Status'] ?? '';
        $orderId = (int) ($_GET['order_id'] ?? 0);

        if ($orderId === 0 && $authority !== '') {
            $pending = PaymentLogger::findPendingRequest(Auth::id(), $authority);
            $orderId = (int) ($pending['order_id'] ?? 0);
        }
        if ($orderId === 0) {
            $orderId = (int) ($_SESSION['pending_payment_order_id'] ?? 0);
        }
        unset($_SESSION['pending_payment_order_id']);

        if (!$orderId || !$authority) {
            $this->renderPaymentResult('error', 'درخواست نامعتبر است.', null);
            return;
        }

        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, Auth::id()]);
        if (!$order) {
            $this->renderPaymentResult('error', 'سفارش یافت نشد.', null);
            return;
        }

        if ($order['authority'] && $order['authority'] !== $authority) {
            $this->renderPaymentResult('error', 'اطلاعات پرداخت نامعتبر است.', $orderId);
            return;
        }

        if ($order['payment_status'] === 'paid') {
            redirect('/dashboard/orders');
            return;
        }

        if ($status !== 'OK') {
            Database::update('orders', [
                'payment_status' => 'failed',
                'authority' => $authority ?: $order['authority'],
                'idempotency_key' => null,
            ], self::WHERE_ID, ['id' => $orderId]);

            PaymentLogger::log([
                'user_id' => Auth::id(),
                'order_id' => $orderId,
                'action' => 'callback',
                'amount' => (int) ($order['total'] ?? 0),
                'authority' => $authority ?: $order['authority'],
                'status' => 'cancelled',
                'request_data' => $_GET,
            ]);

            $this->renderPaymentResult('cancelled', null, $orderId);
            return;
        }

        $zpl = new ZarinPal();
        $result = $zpl->verifyPayment((int) $order['total'], $authority);

        PaymentLogger::log([
            'user_id' => Auth::id(),
            'order_id' => $orderId,
            'action' => 'verify',
            'amount' => (int) ($order['total'] ?? 0),
            'authority' => $authority,
            'ref_id' => $result['ref_id'] ?? null,
            'status' => $result['status'] ? 'verified' : 'failed',
            'request_data' => $zpl->getLastRequest(),
            'response_data' => $zpl->getLastResponse(),
        ]);

        if ($result['status']) {
            Database::beginTransaction();
            try {
                $order = Database::fetch("SELECT * FROM orders WHERE id = ? FOR UPDATE", [$orderId]);
                if (!$order || $order['payment_status'] === 'paid') {
                    Database::rollback();
                    redirect('/dashboard/orders');
                    return;
                }

                $refId = (string) ($result['ref_id'] ?: ($order['ref_id'] ?: $authority));
                Database::update('orders', [
                    'payment_status' => 'paid',
                    'payment_method' => 'zarinpal',
                    'status' => 'processing',
                    'authority' => $authority,
                    'payment_id' => $refId,
                    'ref_id' => $refId,
                    'idempotency_key' => null,
                ], self::WHERE_ID, ['id' => $orderId]);

                OrderEffects::apply(
                    $orderId,
                    (string) $order['tracking_code'],
                    Auth::id(),
                    (int) $order['total'],
                    isset($order['coupon_code']) && $order['coupon_code'] !== '' ? (string) $order['coupon_code'] : null,
                    (int) ($order['coupon_discount'] ?? 0)
                );

                $_SESSION['cart'] = [];
                Database::commit();
            } catch (\Throwable $e) {
                Database::rollback();
                throw $e;
            }

            $this->notifyPaidOrder($order);

            $this->renderPaymentResult('success', null, null, $refId, $order['tracking_code']);
        } else {
            Database::update('orders', [
                'payment_status' => 'failed',
                'authority' => $authority ?: $order['authority'],
                'idempotency_key' => null,
            ], self::WHERE_ID, ['id' => $orderId]);
            $this->renderPaymentResult('error', $result['message'], $orderId);
        }
    }

    public function retryPayment(): void
    {
        if (!Auth::check()) {
            $this->json(['require_login' => true, 'error' => 'لطفاً ابتدا وارد شوید.'], 401);
            return;
        }
        $this->verifyCsrf();

        $orderId = (int) ($_POST['order_id'] ?? 0);
        if (!$orderId) {
            $this->json(['error' => 'سفارش نامعتبر است.'], 400);
            return;
        }

        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, Auth::id()]);
        if (!$order) {
            $this->json(['error' => 'سفارش یافت نشد.'], 404);
            return;
        }
        if (in_array($order['status'], ['cancelled', 'processing', 'shipped', 'delivered'], true)) {
            $this->json(['error' => 'این سفارش قابل پرداخت نیست.'], 400);
            return;
        }
        if ($order['payment_status'] === 'paid') {
            $this->json(['error' => 'این سفارش قبلاً پرداخت شده است.'], 400);
            return;
        }

        $finalTotal = (int) $order['total'];
        if ($finalTotal <= 0) {
            $this->json(['error' => 'مبلغ این سفارش صفر است.'], 400);
            return;
        }

        $this->processZarinPalPayment($orderId, $finalTotal, (string) $order['tracking_code'], Auth::user());
    }

    private function notifyPaidOrder(array $order): void
    {
        try {
            $user = Auth::user();
            $userPhone = $user['phone'] ?? '';
            $userName = $user['name'] ?? 'کاربر';
            $total = faNum(number_format((int) ($order['total'] ?? 0))) . ' تومان';
            $code = $order['tracking_code'] ?? $order['id'];

            if ($userPhone !== '') {
                SmsService::notify('order_receipt', [$userPhone], [
                    'Code' => $code,
                    'Total' => $total,
                ]);
            }

            $ownerPhone = smsOwnerPhone();
            if ($ownerPhone !== '') {
                SmsService::notify('order_new', [$ownerPhone], [
                    'Code' => $code,
                    'Total' => $total,
                    'Name' => $userName,
                ]);
            }
        } catch (Throwable $e) {
            error_log("SMS paid order notify failed: " . $e->getMessage());
        }
    }

    private function renderPaymentResult(string $type, ?string $message, ?int $orderId, ?string $refId = null, ?string $trackingCode = null): void
    {
        require_once __DIR__ . self::LAYOUT_HEADER;

        $retry = $orderId
            ? '<a href="/dashboard/order/detail?id=' . $orderId . '" class="inline-flex items-center gap-2 px-6 py-3 bg-rose-600 text-white rounded-xl font-semibold">پرداخت مجدد</a>'
            : '';

        $content = match ($type) {
            'success' => '<h2 class="text-2xl font-bold text-green-600 mb-4">پرداخت موفق</h2>'
                . '<div class="bg-green-50 rounded-2xl p-6 mb-6">'
                . '<p class="text-green-700">کد رهگیری پرداخت: <strong>' . e($refId) . '</strong></p>'
                . '<p class="text-green-700 mt-2">کد پیگیری سفارش: <strong>' . e($trackingCode) . '</strong></p></div>'
                . '<a href="/dashboard/orders" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-semibold">مشاهده سفارشات</a>',
            'cancelled' => '<h2 class="text-2xl font-bold text-red-600 mb-4">پرداخت لغو شد</h2>'
                . '<p class="text-zinc-600 mb-6">پرداخت شما لغو شد. می‌توانید مجدداً اقدام کنید.</p>'
                . $retry,
            default => '<h2 class="text-2xl font-bold text-red-600 mb-4">پرداخت ناموفق</h2>'
                . '<p class="text-zinc-600 mb-6">' . e($message) . '</p>'
                . $retry,
        };

        echo '<div class="max-w-lg mx-auto px-4 py-20 text-center">' . $content . '</div>';
        require_once __DIR__ . self::LAYOUT_FOOTER;
    }

    public function verifyCoupon(): void
    {
        $code = sanitize($_POST['code'] ?? '');
        if (empty($code)) {
            $this->json(['error' => 'کد تخفیف را وارد کنید.'], 400);
            return;
        }

        $coupon = Database::fetch("SELECT * FROM coupons WHERE code = ? AND is_active = 1", [$code]);
        if (!$coupon) {
            $this->json(['error' => 'کد تخفیف نامعتبر است.'], 400);
            return;
        }

        $expires = $coupon['expires_at'];
        if ($expires && strtotime($expires) < time()) {
            $this->json(['error' => 'کد تخفیف منقضی شده است.'], 400);
            return;
        }
        if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
            $this->json(['error' => 'کد تخفیف به حداکثر تعداد استفاده رسیده است.'], 400);
            return;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->json(['error' => 'سبد خرید خالی است.'], 400);
            return;
        }

        try {
            [, $total] = $this->validateCartItems($cart);
        } catch (\RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 400);
            return;
        }

        if ($coupon['min_order'] > 0 && $total < $coupon['min_order']) {
            $this->json(['error' => 'حداقل مبلغ خرید برای این کد تخفیف ' . number_format((int) $coupon['min_order']) . ' تومان است.'], 400);
            return;
        }

        if ($coupon['discount_type'] === 'percentage') {
            $discount = (int) min($total * $coupon['discount_value'] / 100, $total);
        } else {
            $discount = (int) min($coupon['discount_value'], $total);
        }

        $this->json([
            'success' => true,
            'discount' => $discount,
            'discount_formatted' => number_format($discount) . self::CURRENCY_SUFFIX,
            'total_after' => $total - $discount,
            'total_after_formatted' => number_format($total - $discount) . self::CURRENCY_SUFFIX,
            'message' => 'کد تخفیف اعمال شد.',
        ]);
    }

    public function addCourseToCart(): void
    {
        $this->verifyCsrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        if (!$courseId) {
            $this->json(['error' => 'دوره نامعتبر'], 400);
            return;
        }

        $course = Database::fetch("SELECT * FROM courses WHERE id = ? AND is_active = 1", [$courseId]);
        if (!$course) {
            $this->json(['error' => 'دوره یافت نشد'], 404);
            return;
        }

        if ($course['is_free']) {
            $this->json(['error' => 'این دوره رایگان است'], 400);
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($_SESSION['cart'] as $item) {
            if (($item['type'] ?? 'product') === 'course' && $item['id'] === $courseId) {
                $this->json(['error' => 'این دوره قبلاً به سبد اضافه شده است'], 400);
                return;
            }
        }

        $slug = $course['slug'] ?: $course['id'];
        $_SESSION['cart'][] = [
            'id' => $course['id'],
            'name' => $course['title'],
            'price' => (int) $course['price'],
            'old_price' => (int) ($course['old_price'] ?? 0),
            'image' => $course['image'],
            'category' => $course['category'],
            'brand' => '',
            'qty' => 1,
            'type' => 'course',
            'slug' => $slug,
        ];

        $cartCount = count($_SESSION['cart']);
        $this->json([
            'success' => true,
            'message' => $course['title'] . ' به سبد خرید اضافه شد',
            'cart_count' => $cartCount,
        ]);
    }
}
