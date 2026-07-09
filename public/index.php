<?php

require_once __DIR__ . '/../app/bootstrap.php';

Router::get('/', ['HomeController', 'index']);

Router::get('/login', ['AuthController', 'showLogin']);
Router::post('/login', ['AuthController', 'login']);
Router::get('/register', ['AuthController', 'showRegister']);
Router::post('/register', ['AuthController', 'register']);
Router::get('/logout', ['AuthController', 'logout']);
Router::post('/auth/forgot', ['AuthController', 'forgot']);

Router::get('/dashboard', ['DashboardController', 'index']);
Router::get('/dashboard/{tab}', ['DashboardController', 'tab']);

Router::post('/dashboard/profile/update', ['DashboardController', 'updateProfile']);
Router::post('/dashboard/password/change', ['DashboardController', 'changePassword']);
Router::post('/dashboard/address/add', ['DashboardController', 'addAddress']);
Router::post('/dashboard/address/delete/{id}', ['DashboardController', 'deleteAddress']);
Router::post('/dashboard/wishlist/toggle', ['DashboardController', 'toggleWishlist']);

Router::post('/booking/services', ['BookingController', 'getServices']);
Router::post('/booking/slots', ['BookingController', 'getSlots']);
Router::post('/booking/confirm', ['BookingController', 'confirm']);
Router::post('/booking/captcha/refresh', ['BookingController', 'refreshCaptcha']);
Router::get('/booking', ['BookingController', 'index']);

Router::get('/shop', ['ShopController', 'index']);
Router::get('/product/{id}', ['ShopController', 'show']);
Router::post('/shop/cart/add', ['ShopController', 'addToCart']);
Router::post('/shop/cart/update', ['ShopController', 'updateCart']);
Router::post('/shop/cart/remove', ['ShopController', 'removeFromCart']);
Router::post('/shop/cart/checkout', ['ShopController', 'checkout']);
Router::post('/shop/cart/list', function () {
    header('Content-Type: application/json');
    echo json_encode(['cart' => $_SESSION['cart'] ?? []]);
    exit;
});
Router::get('/cart', ['ShopController', 'cart']);
Router::get('/shop/cart/summary', ['ShopController', 'cartSummary']);
Router::post('/shop/wishlist/toggle', ['ShopController', 'toggleWishlist']);
Router::get('/wishlist', ['ShopController', 'wishlist']);

Router::post('/newsletter/subscribe', ['NewsletterController', 'subscribe']);

Router::get('/admin/login', ['AdminController', 'loginForm']);
Router::post('/admin/login', ['AdminController', 'doLogin']);
Router::get('/admin', ['AdminController', 'dashboard']);
Router::get('/admin/{section}', ['AdminController', 'section']);
Router::post('/admin/{section}/save', ['AdminController', 'save']);
Router::post('/admin/{section}/delete/{id}', ['AdminController', 'delete']);
Router::post('/admin/settings/update', ['AdminController', 'updateSettings']);

Router::get('/cart/summary', function () {
    $count = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
    exit;
});

Router::post('/api/like-model', function () {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'لایک ثبت شد ❤️'], JSON_UNESCAPED_UNICODE);
    exit;
});

Router::get('/api/services', ['ApiController', 'services']);
Router::get('/api/artists', ['ApiController', 'artists']);
Router::get('/api/products', ['ApiController', 'products']);

Router::dispatch();
