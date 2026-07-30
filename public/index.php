<?php

declare(strict_types=1);

use App\Router;
use App\Settings;

require_once __DIR__ . '/../app/bootstrap.php';

Router::get('/', ['App\Controllers\HomeController', 'index']);

Router::get('/login', ['App\Controllers\AuthController', 'showLogin']);
Router::post('/login', ['App\Controllers\AuthController', 'login']);
Router::get('/register', ['App\Controllers\AuthController', 'showRegister']);
Router::post('/register', ['App\Controllers\AuthController', 'register']);
Router::get('/verify-otp', ['App\Controllers\AuthController', 'showVerifyOtp']);
Router::post('/verify-otp', ['App\Controllers\AuthController', 'verifyOtp']);
Router::get('/logout', ['App\Controllers\AuthController', 'logout']);
Router::post('/auth/forgot', ['App\Controllers\AuthController', 'forgot']);
Router::get('/auth/google', ['App\Controllers\AuthController', 'googleRedirect']);
Router::get('/auth/google/callback', ['App\Controllers\AuthController', 'googleCallback']);

Router::get('/dashboard', ['App\Controllers\DashboardController', 'index']);
Router::get('/dashboard/{tab}', ['App\Controllers\DashboardController', 'tab']);

Router::post('/dashboard/profile/update', ['App\Controllers\DashboardController', 'updateProfile']);
Router::post('/dashboard/password/change', ['App\Controllers\DashboardController', 'changePassword']);
Router::post('/dashboard/address/add', ['App\Controllers\DashboardController', 'addAddress']);
Router::post('/dashboard/address/delete/{id}', ['App\Controllers\DashboardController', 'deleteAddress']);
Router::post('/dashboard/address/update/{id}', ['App\Controllers\DashboardController', 'updateAddress']);
Router::post('/dashboard/wishlist/toggle', ['App\Controllers\DashboardController', 'toggleWishlist']);
Router::post('/dashboard/appointment/cancel', ['App\Controllers\DashboardController', 'cancelAppointment']);
Router::post('/dashboard/appointment/reschedule', ['App\Controllers\DashboardController', 'rescheduleAppointment']);
Router::post('/dashboard/order/cancel', ['App\Controllers\DashboardController', 'cancelOrder']);
Router::get('/dashboard/order/detail', ['App\Controllers\DashboardController', 'orderDetail']);
Router::post('/dashboard/wallet/topup', ['App\Controllers\DashboardController', 'walletTopUp']);

Router::post('/booking/services', ['App\Controllers\BookingController', 'getServices']);
Router::post('/booking/slots', ['App\Controllers\BookingController', 'getSlots']);
Router::post('/booking/confirm', ['App\Controllers\BookingController', 'confirm']);
Router::post('/booking/captcha/refresh', ['App\Controllers\BookingController', 'refreshCaptcha']);
Router::get('/booking', ['App\Controllers\BookingController', 'index']);
Router::get('/api/hair-lengths', ['App\Controllers\BookingController', 'getHairLengths']);
Router::post('/booking/price-calculation', ['App\Controllers\BookingController', 'getServicePrice']);

Router::get('/shop', ['App\Controllers\ShopController', 'index']);
Router::get('/product/{id}', ['App\Controllers\ShopController', 'show']);
Router::post('/product/{id}/review', ['App\Controllers\ShopController', 'postReview']);
Router::post('/shop/cart/add', ['App\Controllers\ShopController', 'addToCart']);
Router::post('/shop/cart/update', ['App\Controllers\ShopController', 'updateCart']);
Router::post('/shop/cart/remove', ['App\Controllers\ShopController', 'removeFromCart']);
Router::post('/shop/cart/checkout', ['App\Controllers\ShopController', 'checkout']);
Router::post('/shop/cart/list', function () {
    header('Content-Type: application/json');
    echo json_encode(['cart' => $_SESSION['cart'] ?? []]);
    exit;
});
Router::post('/shop/coupon/verify', ['App\Controllers\ShopController', 'verifyCoupon']);
Router::get('/cart', ['App\Controllers\ShopController', 'cart']);
Router::get('/shop/cart/summary', ['App\Controllers\ShopController', 'cartSummary']);
Router::post('/shop/wishlist/toggle', ['App\Controllers\ShopController', 'toggleWishlist']);
Router::post('/shop/wishlist/data', ['App\Controllers\ShopController', 'wishlistData']);
Router::get('/wishlist', ['App\Controllers\ShopController', 'wishlist']);
Router::post('/shop/course/add', ['App\Controllers\ShopController', 'addCourseToCart']);

Router::get('/shop/payment/callback', ['App\Controllers\ShopController', 'paymentCallback']);

Router::get('/blog', ['App\Controllers\BlogController', 'index']);
Router::get('/blog/{slug}', ['App\Controllers\BlogController', 'show']);
Router::post('/blog/{slug}/comment', ['App\Controllers\BlogController', 'postComment']);
Router::post('/blog/comment/like', ['App\Controllers\BlogController', 'likeComment']);
Router::post('/admin/blog/upload-image', ['App\Controllers\BlogController', 'uploadImage']);

Router::get('/contact', ['App\Controllers\ContactController', 'index']);
Router::post('/contact/send', ['App\Controllers\ContactController', 'send']);

Router::get('/about', ['App\Controllers\AboutController', 'index']);

Router::get('/sitemap.xml', ['App\Controllers\SitemapController', 'index']);

Router::get('/robots.txt', ['App\Controllers\RobotsController', 'index']);

Router::get('/models', ['App\Controllers\ModelsController', 'index']);

Router::get('/dashboard/wallet/payment/callback', ['App\Controllers\DashboardController', 'walletPaymentCallback']);

Router::post('/newsletter/subscribe', ['App\Controllers\NewsletterController', 'subscribe']);

Router::get('/admin/login', ['App\Controllers\AdminController', 'loginForm']);
Router::post('/admin/login', ['App\Controllers\AdminController', 'doLogin']);
Router::get('/admin', ['App\Controllers\AdminController', 'dashboard']);
Router::get('/admin/{section}', ['App\Controllers\AdminController', 'section']);
Router::post('/admin/{section}/save', ['App\Controllers\AdminController', 'save']);
Router::post('/admin/{section}/delete/{id}', ['App\Controllers\AdminController', 'delete']);
Router::post('/admin/settings/update', ['App\Controllers\AdminController', 'updateSettings']);
Router::post('/admin/password/change', ['App\Controllers\AdminController', 'changePassword']);
Router::post('/admin/sms/send', ['App\Controllers\AdminController', 'sendBulkSms']);
Router::post('/admin/sms/template/save', ['App\Controllers\AdminController', 'saveSmsTemplate']);
Router::post('/admin/sms/template/delete/{id}', ['App\Controllers\AdminController', 'deleteSmsTemplate']);
Router::post('/admin/sms/credit/refresh', ['App\Controllers\AdminController', 'refreshSmsCredit']);

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

Router::get('/api/services', ['App\Controllers\ApiController', 'services']);
Router::get('/api/artists', ['App\Controllers\ApiController', 'artists']);
Router::get('/api/products', ['App\Controllers\ApiController', 'products']);
Router::get('/api/user/addresses', ['App\Controllers\ApiController', 'userAddresses']);

Router::get('/privacy', function () {
    $settings = Settings::all();
    $brandName = $settings['brand_name'] ?? 'موبارو';
    $title = 'حریم خصوصی | ' . $brandName;
    require __DIR__ . '/../app/views/layouts/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-20"><h1 class="text-2xl font-bold mb-4">حریم خصوصی</h1><p class="text-zinc-600 leading-relaxed">اطلاعات کاربران ' . e($brandName) . ' نزد ما محفوظ است و بدون رضایت شما در اختیار شخص ثالث قرار نخواهد گرفت.</p></div>';
    require __DIR__ . '/../app/views/layouts/footer.php';
});
Router::get('/terms', function () {
    $settings = Settings::all();
    $brandName = $settings['brand_name'] ?? 'موبارو';
    $title = 'شرایط استفاده | ' . $brandName;
    require __DIR__ . '/../app/views/layouts/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-20"><h1 class="text-2xl font-bold mb-4">شرایط و قوانین</h1><p class="text-zinc-600 leading-relaxed">استفاده از خدمات ' . e($brandName) . ' به معنی پذیرش قوانین و مقررات زیر است. لطفاً پیش از استفاده مطالعه کنید.</p></div>';
    require __DIR__ . '/../app/views/layouts/footer.php';
});

Router::get('/academy', ['App\Controllers\AcademyController', 'index']);
Router::get('/course/{slug}', ['App\Controllers\AcademyController', 'show']);
Router::post('/course/{slug}/enroll', ['App\Controllers\AcademyController', 'enroll']);
Router::get('/course/{slug}/watch', ['App\Controllers\AcademyController', 'watch']);
Router::post('/course/lesson/complete', ['App\Controllers\AcademyController', 'completeLesson']);
Router::get('/course/{slug}/certificate', ['App\Controllers\AcademyController', 'certificate']);

Router::get('/media/stream/{id}', ['App\Controllers\MediaController', 'stream']);
Router::get('/media/{width}/{height}', ['App\Controllers\ImageController', 'random']);
Router::get('/media/{width}/{height}/{seed}', ['App\Controllers\ImageController', 'seeded']);
Router::get('/avatar/{name}/{size}', ['App\Controllers\AvatarController', 'generate']);

Router::dispatch();
