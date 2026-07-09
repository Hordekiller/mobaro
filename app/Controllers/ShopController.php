<?php

class ShopController extends BaseController
{
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
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $orderClause = match($sort) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'rating' => 'p.rating DESC',
            'popular' => 'p.reviews DESC',
            default => 'p.id DESC',
        };

        $countRow = Database::fetch("SELECT COUNT(*) as cnt FROM products p {$where}", $params);
        $totalProducts = (int) ($countRow['cnt'] ?? 0);
        $totalPages = max(1, (int) ceil($totalProducts / $perPage));

        $products = Database::fetchAll(
            "SELECT p.* FROM products p {$where} ORDER BY {$orderClause} LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $categoryRows = Database::fetchAll(
            "SELECT category, COUNT(*) as cnt FROM products WHERE is_active = 1 GROUP BY category ORDER BY cnt DESC"
        );
        $brandRows = Database::fetchAll(
            "SELECT brand, COUNT(*) as cnt FROM products WHERE is_active = 1 AND brand IS NOT NULL AND brand != '' GROUP BY brand ORDER BY cnt DESC"
        );

        $cart = $_SESSION['cart'] ?? [];
        $wishlist = $_SESSION['wishlist'] ?? [];

        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('shop/index', compact(
            'products', 'categoryRows', 'brandRows', 'category', 'brand', 'search', 'sort',
            'page', 'totalPages', 'totalProducts', 'cart', 'wishlist', 'settings'
        ));
    }

    public function show(int $id): void
    {
        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND is_active = 1", [$id]);
        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/header.php';
            require __DIR__ . '/../views/errors/404.php';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        $related = Database::fetchAll(
            "SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 LIMIT 4",
            [$product['category'], $id]
        );

        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $cart = $_SESSION['cart'] ?? [];

        $this->view('shop/detail', compact('product', 'related', 'settings', 'cart'));
    }

    public function toggleWishlist(): void
    {
        $this->verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        if (!$productId) {
            $this->json(['error' => 'محصول نامعتبر'], 400);
            return;
        }

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

    public function wishlist(): void
    {
        $ids = $_SESSION['wishlist'] ?? [];
        if (empty($ids)) {
            $products = [];
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $products = Database::fetchAll("SELECT * FROM products WHERE id IN ({$placeholders}) AND is_active = 1", $ids);
        }
        $cart = $_SESSION['cart'] ?? [];
        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $this->view('shop/wishlist', compact('products', 'cart', 'settings'));
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
            'total_formatted' => number_format($total) . ' تومان',
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

        if (isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values(
                array_filter($_SESSION['cart'], fn($item) => $item['id'] !== $productId)
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

        $total = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));
        $trackingCode = 'MB-' . date('Ymd') . '-' . rand(100, 999);

        $orderId = Database::insert('orders', [
            'user_id' => Auth::id(),
            'total' => $total,
            'status' => 'processing',
            'tracking_code' => $trackingCode,
        ]);

        foreach ($cart as $item) {
            Database::insert('order_items', [
                'order_id' => $orderId,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['qty'],
            ]);
        }

        $pointsEarned = floor($total / 10000);
        Database::insert('transactions', [
            'user_id' => Auth::id(),
            'type' => 'points_earn',
            'amount' => $pointsEarned,
            'description' => "امتیاز خرید سفارش {$trackingCode}",
        ]);
        Database::query("UPDATE users SET points = points + ? WHERE id = ?", [$pointsEarned, Auth::id()]);

        $_SESSION['cart'] = [];

        $this->json([
            'success' => true,
            'message' => "سفارش شما با موفقیت ثبت شد. کد پیگیری: {$trackingCode}",
            'order_id' => $orderId,
            'tracking_code' => $trackingCode,
        ]);
    }
}
