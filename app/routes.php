<?php

declare(strict_types=1);

use App\Router;

Router::get('/', ['App\Controllers\HomeController', 'index']);

Router::get('/login', [AUTH, 'showLogin']);
Router::post('/login', [AUTH, 'login']);
Router::get('/register', [AUTH, 'showRegister']);
Router::post('/register', [AUTH, 'register']);
Router::get('/verify-otp', [AUTH, 'showVerifyOtp']);
Router::post('/verify-otp', [AUTH, 'verifyOtp']);
Router::get('/logout', [AUTH, 'logout']);
Router::post('/auth/forgot', [AUTH, 'forgot']);
Router::get('/reset-password', [AUTH, 'showResetPassword']);
Router::post('/reset-password', [AUTH, 'resetPassword']);
Router::get('/auth/google', [AUTH, 'googleRedirect']);
Router::get('/auth/google/callback', [AUTH, 'googleCallback']);

Router::get('/dashboard', [DASHBOARD, 'index']);
Router::get('/dashboard/{tab}', [DASHBOARD, 'tab']);
Router::post('/dashboard/profile/update', [DASHBOARD, 'updateProfile']);
Router::post('/dashboard/password/change', [DASHBOARD, 'changePassword']);
Router::post('/dashboard/address/add', [DASHBOARD, 'addAddress']);
Router::post('/dashboard/address/delete/{id}', [DASHBOARD, 'deleteAddress']);
Router::post('/dashboard/address/update/{id}', [DASHBOARD, 'updateAddress']);
Router::post('/dashboard/wishlist/toggle', [DASHBOARD, 'toggleWishlist']);
Router::post('/dashboard/appointment/cancel', [DASHBOARD, 'cancelAppointment']);
Router::post('/dashboard/appointment/reschedule', [DASHBOARD, 'rescheduleAppointment']);
Router::post('/dashboard/order/cancel', [DASHBOARD, 'cancelOrder']);
Router::get('/dashboard/order/detail', [DASHBOARD, 'orderDetail']);
Router::post('/dashboard/wallet/topup', [DASHBOARD, 'walletTopUp']);

Router::post('/booking/services', [BOOKING, 'getServices']);
Router::post('/booking/slots', [BOOKING, 'getSlots']);
Router::post('/booking/confirm', [BOOKING, 'confirm']);
Router::post('/booking/captcha/refresh', [BOOKING, 'refreshCaptcha']);
Router::get('/booking', [BOOKING, 'index']);
Router::get('/api/hair-lengths', [BOOKING, 'getHairLengths']);
Router::post('/booking/price-calculation', [BOOKING, 'getServicePrice']);

Router::get('/shop', [SHOP, 'index']);
Router::get('/product/{id}', [SHOP, 'show']);
Router::post('/product/{id}/review', [SHOP, 'postReview']);
Router::post('/shop/cart/add', [SHOP, 'addToCart']);
Router::post('/shop/cart/update', [SHOP, 'updateCart']);
Router::post('/shop/cart/remove', [SHOP, 'removeFromCart']);
Router::post('/shop/cart/checkout', [SHOP, 'checkout']);
Router::post('/shop/order/pay', [SHOP, 'retryPayment']);
Router::post('/shop/cart/list', function () {
    header(JSON_HEADER);
    echo json_encode(['cart' => $_SESSION['cart'] ?? []]);
    exit;
});
Router::post('/shop/coupon/verify', [SHOP, 'verifyCoupon']);
Router::get('/cart', [SHOP, 'cart']);
Router::get('/shop/cart/summary', [SHOP, 'cartSummary']);
Router::post('/shop/wishlist/toggle', [SHOP, 'toggleWishlist']);
Router::post('/shop/wishlist/data', [SHOP, 'wishlistData']);
Router::get('/wishlist', [SHOP, 'wishlist']);
Router::post('/shop/course/add', [SHOP, 'addCourseToCart']);
Router::get('/shop/payment/callback', [SHOP, 'paymentCallback']);

Router::get('/blog', [BLOG, 'index']);
Router::get('/blog/{slug}', [BLOG, 'show']);
Router::post('/blog/{slug}/comment', [BLOG, 'postComment']);
Router::post('/blog/comment/like', [BLOG, 'likeComment']);
Router::post('/admin/blog/upload-image', [BLOG, 'uploadImage']);

Router::get('/contact', ['App\Controllers\ContactController', 'index']);
Router::post('/contact/send', ['App\Controllers\ContactController', 'send']);

Router::get('/about', ['App\Controllers\AboutController', 'index']);

Router::get('/sitemap.xml', ['App\Controllers\SitemapController', 'index']);
Router::get('/sitemap-{name}.xml', ['App\Controllers\SitemapController', 'section']);
Router::get('/robots.txt', ['App\Controllers\RobotsController', 'index']);
Router::get('/llms.txt', ['App\Controllers\LlmsController', 'index']);

Router::get('/models', ['App\Controllers\ModelsController', 'index']);

Router::get('/dashboard/wallet/payment/callback', [DASHBOARD, 'walletPaymentCallback']);

Router::post('/newsletter/subscribe', ['App\Controllers\NewsletterController', 'subscribe']);

Router::get('/admin/login', [ADMIN, 'loginForm']);
Router::post('/admin/login', [ADMIN, 'doLogin']);
Router::get('/admin', [ADMIN, 'dashboard']);
Router::get('/admin/{section}', [ADMIN, 'section']);
Router::post('/admin/{section}/save', [ADMIN, 'save']);
Router::post('/admin/{section}/delete/{id}', [ADMIN, 'delete']);
Router::post('/admin/settings/update', [ADMIN, 'updateSettings']);
Router::post('/admin/password/change', [ADMIN, 'changePassword']);
Router::post('/admin/sms/send', [ADMIN, 'sendBulkSms']);
Router::post('/admin/sms/template/save', [ADMIN, 'saveSmsTemplate']);
Router::post('/admin/sms/template/delete/{id}', [ADMIN, 'deleteSmsTemplate']);
Router::post('/admin/sms/credit/refresh', [ADMIN, 'refreshSmsCredit']);

Router::get('/cart/summary', function () {
    $count = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
    header(JSON_HEADER);
    echo json_encode(['count' => $count]);
    exit;
});

Router::post('/api/like-model', function () {
    header(JSON_HEADER);
    echo json_encode(['success' => true, 'message' => 'لایک ثبت شد ❤️'], JSON_UNESCAPED_UNICODE);
    exit;
});

Router::get('/api/services', [API, 'services']);
Router::get('/api/artists', [API, 'artists']);
Router::get('/api/products', [API, 'products']);
Router::get('/api/user/addresses', [API, 'userAddresses']);

Router::get('/privacy', ['App\Controllers\PagesController', 'privacy']);
Router::get('/terms', ['App\Controllers\PagesController', 'terms']);

Router::get('/academy', [ACADEMY, 'index']);
Router::get('/course/{slug}', [ACADEMY, 'show']);
Router::post('/course/{slug}/enroll', [ACADEMY, 'enroll']);
Router::get('/course/{slug}/watch', [ACADEMY, 'watch']);
Router::post('/course/lesson/complete', [ACADEMY, 'completeLesson']);
Router::get('/course/{slug}/certificate', [ACADEMY, 'certificate']);

Router::get('/media/stream/{id}', ['App\Controllers\MediaController', 'stream']);
Router::get('/media/{width}/{height}', ['App\Controllers\ImageController', 'random']);
Router::get('/media/{width}/{height}/{seed}', ['App\Controllers\ImageController', 'seeded']);
Router::get('/avatar/{name}/{size}', ['App\Controllers\AvatarController', 'generate']);
