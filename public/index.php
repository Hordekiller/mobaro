<?php

require_once __DIR__ . '/../app/bootstrap.php';

Router::get('/', ['HomeController', 'index']);

Router::get('/login', ['AuthController', 'showLogin']);
Router::post('/login', ['AuthController', 'login']);
Router::get('/register', ['AuthController', 'showRegister']);
Router::post('/register', ['AuthController', 'register']);
Router::get('/logout', ['AuthController', 'logout']);
Router::post('/auth/forgot', ['AuthController', 'forgot']);
Router::get('/auth/google', ['AuthController', 'googleRedirect']);
Router::get('/auth/google/callback', ['AuthController', 'googleCallback']);

Router::get('/dashboard', ['DashboardController', 'index']);
Router::get('/dashboard/{tab}', ['DashboardController', 'tab']);

Router::post('/dashboard/profile/update', ['DashboardController', 'updateProfile']);
Router::post('/dashboard/password/change', ['DashboardController', 'changePassword']);
Router::post('/dashboard/address/add', ['DashboardController', 'addAddress']);
Router::post('/dashboard/address/delete/{id}', ['DashboardController', 'deleteAddress']);
Router::post('/dashboard/wishlist/toggle', ['DashboardController', 'toggleWishlist']);
Router::post('/dashboard/appointment/cancel', ['DashboardController', 'cancelAppointment']);
Router::post('/dashboard/appointment/reschedule', ['DashboardController', 'rescheduleAppointment']);
Router::post('/dashboard/order/cancel', ['DashboardController', 'cancelOrder']);
Router::get('/dashboard/order/detail', ['DashboardController', 'orderDetail']);
Router::post('/dashboard/wallet/topup', ['DashboardController', 'walletTopUp']);

Router::post('/booking/services', ['BookingController', 'getServices']);
Router::post('/booking/slots', ['BookingController', 'getSlots']);
Router::post('/booking/confirm', ['BookingController', 'confirm']);
Router::post('/booking/captcha/refresh', ['BookingController', 'refreshCaptcha']);
Router::get('/booking', ['BookingController', 'index']);

Router::get('/shop', ['ShopController', 'index']);
Router::get('/product/{id}', ['ShopController', 'show']);
Router::post('/product/{id}/review', ['ShopController', 'postReview']);
Router::post('/shop/cart/add', ['ShopController', 'addToCart']);
Router::post('/shop/cart/update', ['ShopController', 'updateCart']);
Router::post('/shop/cart/remove', ['ShopController', 'removeFromCart']);
Router::post('/shop/cart/checkout', ['ShopController', 'checkout']);
Router::post('/shop/cart/list', function () {
    header('Content-Type: application/json');
    echo json_encode(['cart' => $_SESSION['cart'] ?? []]);
    exit;
});
Router::post('/shop/coupon/verify', ['ShopController', 'verifyCoupon']);
Router::get('/cart', ['ShopController', 'cart']);
Router::get('/shop/cart/summary', ['ShopController', 'cartSummary']);
Router::post('/shop/wishlist/toggle', ['ShopController', 'toggleWishlist']);
Router::get('/wishlist', ['ShopController', 'wishlist']);
Router::post('/shop/course/add', ['ShopController', 'addCourseToCart']);

Router::get('/shop/payment/callback', ['ShopController', 'paymentCallback']);

Router::get('/blog', ['BlogController', 'index']);
Router::get('/blog/{slug}', ['BlogController', 'show']);
Router::post('/blog/{slug}/comment', ['BlogController', 'postComment']);
Router::post('/blog/comment/like', ['BlogController', 'likeComment']);

Router::get('/contact', ['ContactController', 'index']);
Router::post('/contact/send', ['ContactController', 'send']);

Router::get('/about', ['AboutController', 'index']);

Router::get('/dashboard/wallet/payment/callback', ['DashboardController', 'walletPaymentCallback']);

Router::post('/newsletter/subscribe', ['NewsletterController', 'subscribe']);

Router::get('/admin/login', ['AdminController', 'loginForm']);
Router::post('/admin/login', ['AdminController', 'doLogin']);
Router::get('/admin', ['AdminController', 'dashboard']);
Router::get('/admin/{section}', ['AdminController', 'section']);
Router::post('/admin/{section}/save', ['AdminController', 'save']);
Router::post('/admin/{section}/delete/{id}', ['AdminController', 'delete']);
Router::post('/admin/settings/update', ['AdminController', 'updateSettings']);
Router::post('/admin/password/change', ['AdminController', 'changePassword']);

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
Router::get('/api/user/addresses', ['ApiController', 'userAddresses']);

Router::get('/privacy', function () {
    $title = 'حریم خصوصی | موبارو';
    require __DIR__ . '/../app/views/layouts/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-20"><h1 class="text-2xl font-bold mb-4">حریم خصوصی</h1><p class="text-zinc-600 leading-relaxed">اطلاعات کاربران موبارو نزد ما محفوظ است و بدون رضایت شما در اختیار شخص ثالث قرار نخواهد گرفت.</p></div>';
    require __DIR__ . '/../app/views/layouts/footer.php';
});
Router::get('/terms', function () {
    $title = 'شرایط استفاده | موبارو';
    require __DIR__ . '/../app/views/layouts/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-20"><h1 class="text-2xl font-bold mb-4">شرایط و قوانین</h1><p class="text-zinc-600 leading-relaxed">استفاده از خدمات موبارو به معنی پذیرش قوانین و مقررات زیر است. لطفاً پیش از استفاده مطالعه کنید.</p></div>';
    require __DIR__ . '/../app/views/layouts/footer.php';
});

Router::get('/academy', ['AcademyController', 'index']);
Router::get('/course/{slug}', ['AcademyController', 'show']);
Router::post('/course/{slug}/enroll', ['AcademyController', 'enroll']);
Router::get('/course/{slug}/watch', ['AcademyController', 'watch']);
Router::post('/course/lesson/complete', ['AcademyController', 'completeLesson']);
Router::get('/course/{slug}/certificate', ['AcademyController', 'certificate']);

Router::get('/media/{width}/{height}', ['ImageController', 'random']);
Router::get('/media/{width}/{height}/{seed}', ['ImageController', 'seeded']);
Router::get('/avatar/{name}/{size}', ['AvatarController', 'generate']);

Router::dispatch();
