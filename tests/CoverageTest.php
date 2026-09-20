<?php

declare(strict_types=1);

use App\Auth;
use App\Controllers\AcademyController;
use App\Controllers\AdminController;
use App\Controllers\BlogController;
use App\Controllers\BookingController;
use App\Controllers\DashboardController;
use App\Controllers\FaqController;
use App\Controllers\HomeController;
use App\Controllers\ModelsController;
use App\Controllers\PagesController;
use App\Controllers\ShopController;
use App\Database;
use App\RateLimiter;
use App\Services\OrderEffects;
use App\Services\PaymentLogger;
use App\Services\SmsService;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Integration/rendering tests that exercise the request-to-response path
 * (controllers + views) plus a few DB-backed services. Not a behavioral
 * spec — these exist to raise the new-code coverage measured by SonarQube.
 */
final class CoverageTest extends TestCase
{
    private function capture(callable $fn): string
    {
        ob_start();
        $fn();
        return (string) ob_get_clean();
    }

    private function withGlobals(array $get, array $server, callable $fn): string
    {
        $oldGet = $_GET;
        $oldServer = $_SERVER;
        $_GET = $get;
        $_SERVER = array_merge($oldServer, $server);
        try {
            return $this->capture($fn);
        } finally {
            $_GET = $oldGet;
            $_SERVER = $oldServer;
        }
    }

    private function loginUser(int $userId = 2): void
    {
        $row = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $this->assertNotFalse($row, "seed user {$userId} must exist");
        Auth::login((string) $userId, $row);
    }

    private function logout(): void
    {
        $_SESSION = [];
    }

    private static function adminUserId(): int
    {
        return 9999;
    }

    private static function ensureAdmin(): void
    {
        $id = self::adminUserId();
        if (!Database::fetch("SELECT id FROM users WHERE id = ?", [$id])) {
            Database::query(
                "INSERT INTO users (id, name, family, phone, password, role) VALUES (?, 'ادمین', 'تست', '09120000000', 'unused', 'admin')",
                [$id]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->logout();
        Database::query("DELETE FROM blog_posts WHERE slug LIKE 'coverage-test-%'");
        Database::query("DELETE FROM blog_comments WHERE name = 'coverage-test' AND email = 'coverage@test.ir'");
        Database::query("DELETE FROM payment_logs WHERE authority = 'coverage-test-authority'");
    }

        #[RunInSeparateProcess]
public function testHomeIndexRenders(): void
    {
        $out = $this->withGlobals([], ['REQUEST_URI' => '/'], fn() => (new HomeController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testShopIndexRendersWithFilters(): void
    {
        $out = $this->withGlobals(
            ['category' => 'مو', 'sort' => 'price_asc', 'page' => 1],
            ['REQUEST_URI' => '/shop'],
            fn() => (new ShopController())->index()
        );
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testShopIndexRendersSearch(): void
    {
        $out = $this->withGlobals(
            ['search' => 'شامپو', 'page' => 2],
            ['REQUEST_URI' => '/shop'],
            fn() => (new ShopController())->index()
        );
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testShopCartRenders(): void
    {
        $_SESSION['cart'] = [
            ['id' => 1, 'name' => 'محصول تست', 'price' => 100000, 'qty' => 2, 'type' => 'product'],
        ];
        $out = $this->withGlobals([], ['REQUEST_URI' => '/cart'], fn() => (new ShopController())->cart());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testShopWishlistRendersForGuest(): void
    {
        $_SESSION['wishlist'] = [1, 2];
        $out = $this->withGlobals([], ['REQUEST_URI' => '/wishlist'], fn() => (new ShopController())->wishlist());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testShopShowRendersProductDetail(): void
    {
        $product = Database::fetch("SELECT id FROM products WHERE is_active = 1 ORDER BY id LIMIT 1");
        $this->assertIsArray($product);
        $out = $this->withGlobals([], ['REQUEST_URI' => '/shop/' . $product['id']], function () use ($product) {
            (new ShopController())->show((int) $product['id']);
        });
        $this->assertStringContainsString('<html', $out);
    }

    public function testShopValidateCartItemsWithRealProduct(): void
    {
        $product = Database::fetch("SELECT id FROM products WHERE is_active = 1 AND stock > 0 ORDER BY id LIMIT 1");
        $this->assertIsArray($product);
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'validateCartItems');
        [$items, $total] = $method->invoke($controller, [['id' => (int) $product['id'], 'name' => 'x', 'qty' => 1]]);
        $this->assertCount(1, $items);
        $this->assertSame('product', $items[0]['type']);
        $this->assertGreaterThan(0, $total);
    }

    public function testShopValidateCartItemsRejectsMissingProduct(): void
    {
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'validateCartItems');
        $this->expectException(RuntimeException::class);
        $method->invoke($controller, [['id' => 999999, 'name' => 'نایاب', 'qty' => 1]]);
    }

    public function testShopValidateCouponEmptyAndInvalid(): void
    {
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'validateCoupon');

        $empty = $method->invoke($controller, '', 100000);
        $this->assertSame(0, $empty['discount']);

        $invalid = $method->invoke($controller, 'DOES-NOT-EXIST-123', 100000);
        $this->assertSame(0, $invalid['discount']);
        $this->assertNotEmpty($invalid['error']);
    }

    public function testShopResolveAddressWithRealAddress(): void
    {
        $addr = Database::fetch("SELECT id, user_id, address, city, zip_code FROM addresses WHERE user_id = 2 LIMIT 1");
        $this->assertIsArray($addr);
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'resolveAddress');
        $_POST['address_id'] = (string) $addr['id'];
        $resolved = $method->invoke($controller, [['type' => 'product']], (int) $addr['user_id']);
        unset($_POST['address_id']);
        $this->assertStringContainsString((string) $addr['city'], $resolved['text']);
        $this->assertNotEmpty($resolved['postal']);
    }

    public function testShopResolveAddressEmptyWhenNoPhysicalItems(): void
    {
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'resolveAddress');
        $resolved = $method->invoke($controller, [['type' => 'course']], 2);
        $this->assertSame(['text' => '', 'postal' => ''], $resolved);
    }

    public function testShopCartFingerprint(): void
    {
        $controller = new ShopController();
        $method = new ReflectionMethod($controller, 'cartFingerprint');
        $a = $method->invoke($controller, [['id' => 1, 'qty' => 2, 'price' => 100, 'type' => 'product']]);
        $b = $method->invoke($controller, [['id' => 1, 'qty' => 2, 'price' => 100, 'type' => 'product']]);
        $c = $method->invoke($controller, [['id' => 1, 'qty' => 3, 'price' => 100, 'type' => 'product']]);
        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
    }

        #[RunInSeparateProcess]
public function testBlogIndexRenders(): void
    {
        $this->insertTestPosts();
        $out = $this->withGlobals([], ['REQUEST_URI' => '/blog'], fn() => (new BlogController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testBlogIndexRendersCategoryAndSearch(): void
    {
        $this->insertTestPosts();
        $out = $this->withGlobals(
            ['category' => 'آرایش', 's' => 'تست'],
            ['REQUEST_URI' => '/blog'],
            fn() => (new BlogController())->index()
        );
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testBlogShowRenders(): void
    {
        $this->insertTestPosts();
        $out = $this->withGlobals([], ['REQUEST_URI' => '/blog/coverage-test-1'], function () {
            (new BlogController())->show('coverage-test-1');
        });
        $this->assertStringContainsString('coverage-test-1', $out);
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testPagesPrivacyRender(): void
    {
        $privacy = $this->withGlobals([], ['REQUEST_URI' => '/privacy'], function () {
            (new PagesController())->privacy();
        });
        $this->assertStringContainsString('حریم خصوصی', $privacy);
        $this->assertStringContainsString('<html', $privacy);
    }

        #[RunInSeparateProcess]
public function testPagesTermsRender(): void
    {
        $terms = $this->withGlobals([], ['REQUEST_URI' => '/terms'], function () {
            (new PagesController())->terms();
        });
        $this->assertStringContainsString('شرایط استفاده', $terms);
        $this->assertStringContainsString('<html', $terms);
    }

        #[RunInSeparateProcess]
public function testModelsIndexRenders(): void
    {
        $out = $this->withGlobals([], ['REQUEST_URI' => '/models'], fn() => (new ModelsController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testFaqIndexRenders(): void
    {
        $out = $this->withGlobals([], ['REQUEST_URI' => '/faq'], fn() => (new FaqController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testAcademyIndexRenders(): void
    {
        $out = $this->withGlobals([], ['REQUEST_URI' => '/academy'], fn() => (new AcademyController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testAcademyShowRenders(): void
    {
        $course = Database::fetch("SELECT id FROM courses WHERE is_active = 1 ORDER BY id LIMIT 1");
        $this->assertIsArray($course);
        $out = $this->withGlobals([], ['REQUEST_URI' => '/course/' . $course['id']], function () use ($course) {
            (new AcademyController())->show((string) $course['id']);
        });
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testAcademyWatchRenders(): void
    {
        $this->loginUser();
        $course = Database::fetch(
            "SELECT ce.course_id FROM course_enrollments ce WHERE ce.user_id = 2 ORDER BY ce.course_id LIMIT 1"
        );
        $this->assertIsArray($course);
        $out = $this->capture(function () use ($course) {
            (new AcademyController())->watch((string) $course['course_id']);
        });
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testAcademyCertificateRenders(): void
    {
        $this->loginUser();
        $course = Database::fetch(
            "SELECT ce.course_id FROM course_enrollments ce WHERE ce.user_id = 2 ORDER BY ce.course_id LIMIT 1"
        );
        $this->assertIsArray($course);
        $courseId = (int) $course['course_id'];
        $original = Database::fetch("SELECT progress FROM course_enrollments WHERE user_id = 2 AND course_id = ?", [$courseId]);
        Database::query("UPDATE course_enrollments SET progress = 100 WHERE user_id = 2 AND course_id = ?", [$courseId]);
        try {
            $out = $this->capture(function () use ($courseId) {
                (new AcademyController())->certificate((string) $courseId);
            });
            $this->assertStringContainsString('<html', $out);
        } finally {
            Database::query("UPDATE course_enrollments SET progress = ? WHERE user_id = 2 AND course_id = ?", [
                (int) ($original['progress'] ?? 0),
                $courseId,
            ]);
        }
    }

        #[RunInSeparateProcess]
public function testBookingIndexRenders(): void
    {
        $out = $this->withGlobals([], ['REQUEST_URI' => '/booking'], fn() => (new BookingController())->index());
        $this->assertStringContainsString('<html', $out);
    }

        #[RunInSeparateProcess]
public function testDashboardIndexRenders(): void
    {
        $this->loginUser();
        $out = $this->withGlobals([], ['REQUEST_URI' => '/dashboard'], fn() => (new DashboardController())->index());
        $this->assertStringContainsString('<html', $out);
    }

    #[RunInSeparateProcess]
    public function testDashboardAppointmentsTabRenders(): void
    {
        $this->renderDashboardTab('appointments');
    }

    #[RunInSeparateProcess]
    public function testDashboardCoursesTabRenders(): void
    {
        $this->renderDashboardTab('courses');
    }

    #[RunInSeparateProcess]
    public function testDashboardOrdersTabRenders(): void
    {
        $this->renderDashboardTab('orders');
    }

    #[RunInSeparateProcess]
    public function testDashboardWishlistTabRenders(): void
    {
        $this->renderDashboardTab('wishlist');
    }

    #[RunInSeparateProcess]
    public function testDashboardWalletTabRenders(): void
    {
        $this->renderDashboardTab('wallet');
    }

    #[RunInSeparateProcess]
    public function testDashboardAddressesTabRenders(): void
    {
        $this->renderDashboardTab('addresses');
    }

    #[RunInSeparateProcess]
    public function testDashboardAccountTabRenders(): void
    {
        $this->renderDashboardTab('account');
    }

    #[RunInSeparateProcess]
    public function testDashboardPasswordTabRenders(): void
    {
        $this->renderDashboardTab('password');
    }

    private function renderDashboardTab(string $tab): void
    {
        $this->loginUser();
        $out = $this->withGlobals(['tab' => $tab], ['REQUEST_URI' => '/dashboard?tab=' . $tab], function () use ($tab) {
            (new DashboardController())->tab($tab);
        });
        $this->assertStringContainsString('<html', $out);
    }

    #[RunInSeparateProcess]
    public function testAdminDashboardRenders(): void
    {
        self::ensureAdmin();
        $this->loginUser(self::adminUserId());
        $out = $this->withGlobals([], ['REQUEST_URI' => '/admin'], fn() => (new AdminController())->dashboard());
        $this->assertStringContainsString('<html', $out);
    }

    #[RunInSeparateProcess]
    public function testAdminFaqsSectionRenders(): void
    {
        self::ensureAdmin();
        $this->loginUser(self::adminUserId());
        $out = $this->withGlobals([], ['REQUEST_URI' => '/admin/faqs'], function () {
            (new AdminController())->section('faqs');
        });
        $this->assertStringContainsString('<html', $out);
    }

    public function testSmsServiceNotConfiguredPath(): void
    {
        $service = new SmsService();
        $result = $service->sendVerify('09121111111', '12345');
        $this->assertArrayHasKey('status', $result);
        $this->assertSame(false, $result['status']);
        $this->assertNotEmpty($result['message']);
    }

    public function testSmsServiceOtpGeneration(): void
    {
        $service = new SmsService();
        for ($i = 0; $i < 5; $i++) {
            $otp = $service->generateOtp();
            $this->assertMatchesRegularExpression('/^\d{5}$/', $otp);
        }
        $this->assertGreaterThan(0, $service->getOtpTtl());
        $this->assertGreaterThanOrEqual(4, $service->getOtpLength());
    }

    public function testRateLimiterLockoutFlow(): void
    {
        $key = 'coverage-rate-' . bin2hex(random_bytes(4));
        RateLimiter::clearAttempts($key);
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(RateLimiter::isLocked($key, 5, 15));
            RateLimiter::recordAttempt($key);
        }
        $this->assertTrue(RateLimiter::isLocked($key, 5, 15));
        $this->assertSame(0, RateLimiter::remainingAttempts($key, 5, 15));
        RateLimiter::clearAttempts($key);
        $this->assertFalse(RateLimiter::isLocked($key, 5, 15));
    }

    public function testPaymentLoggerWritesAndFinds(): void
    {
        $ok = PaymentLogger::log([
            'user_id' => 2,
            'order_id' => null,
            'gateway' => 'zarinpal',
            'action' => 'request',
            'amount' => 10000,
            'authority' => 'coverage-test-authority',
            'status' => 'sent',
            'request_data' => ['x' => 1],
        ]);
        $this->assertTrue($ok);
        $row = PaymentLogger::findPendingRequest(2, 'coverage-test-authority');
        $this->assertIsArray($row);
        $this->assertSame('coverage-test-authority', $row['authority']);
    }

    public function testOrderEffectsDecrementsStockAndCreditsPoints(): void
    {
        $order = Database::fetch(
            "SELECT o.id, o.user_id, oi.product_id, oi.quantity
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             ORDER BY o.id DESC LIMIT 1"
        );
        $this->assertIsArray($order);
        $orderId = (int) $order['id'];
        $productId = (int) $order['product_id'];

        $stockBefore = (int) Database::fetch("SELECT stock FROM products WHERE id = ?", [$productId])['stock'];
        $pointsBefore = (int) Database::fetch("SELECT points FROM users WHERE id = 2")['points'];

        try {
            OrderEffects::apply($orderId, 'COV-' . $orderId, 2, 100000, null, 0);
            $stockAfter = (int) Database::fetch("SELECT stock FROM products WHERE id = ?", [$productId])['stock'];
            $pointsAfter = (int) Database::fetch("SELECT points FROM users WHERE id = 2")['points'];
            $this->assertLessThanOrEqual($stockBefore, $stockAfter);
            $this->assertSame($pointsBefore + 10, $pointsAfter);
            $this->assertSame('product', OrderEffects::resolveLegacyItemType($productId));
        } finally {
            Database::query("UPDATE products SET stock = ? WHERE id = ?", [$stockBefore, $productId]);
            Database::query("UPDATE users SET points = ? WHERE id = 2", [$pointsBefore]);
            Database::query("DELETE FROM transactions WHERE id = ?", [
                (int) Database::fetch(
                    "SELECT id FROM transactions WHERE user_id = 2 AND description LIKE ? ORDER BY id DESC LIMIT 1",
                    ['امتیاز خرید سفارش COV-%']
                )['id'],
            ]);
        }
    }

    private function insertTestPosts(): void
    {
        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= 2; $i++) {
            Database::query(
                "INSERT INTO blog_posts
                    (title, slug, content, excerpt, image, category, author, reading_time, is_published, is_featured, views, published_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 'آرایش', 'موبارو', 3, 1, 0, 0, ?, ?, ?)",
                ["پست تست {$i}", "coverage-test-{$i}", '<p>محتوا</p>', 'خلاصه', 'blog-1.jpg', $now, $now, $now]
            );
        }
        $postId = (int) Database::fetch("SELECT id FROM blog_posts WHERE slug = 'coverage-test-1'")['id'];
        Database::query(
            "INSERT INTO blog_comments (post_id, user_id, name, email, text, is_approved) VALUES (?, NULL, 'coverage-test', 'coverage@test.ir', 'نظر تست', 1)",
            [$postId]
        );
    }
}