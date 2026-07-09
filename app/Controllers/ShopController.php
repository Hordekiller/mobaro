<?php

class ShopController extends BaseController
{
    public function index(): void
    {
        $category = sanitize($_GET['category'] ?? 'all');
        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY id");

        $categories = [];
        $rows = Database::fetchAll("SELECT DISTINCT category FROM products WHERE is_active = 1");
        foreach ($rows as $r) {
            $categories[] = $r['category'];
        }

        if ($category !== 'all') {
            $products = array_values(array_filter($products, fn($p) => ($p['category'] ?? '') === $category));
        }

        $cart = $_SESSION['cart'] ?? [];
        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('shop/index', compact('products', 'categories', 'category', 'cart', 'settings'));
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
                'image' => $product['image'],
                'category' => $product['category'],
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
