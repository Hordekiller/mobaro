<?php

use App\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private function getRoutesProperty(): ReflectionProperty
    {
        $prop = new ReflectionProperty(Router::class, 'routes');
        $prop->setAccessible(true);
        return $prop;
    }

    private function getAddMethod(): ReflectionMethod
    {
        $method = new ReflectionMethod(Router::class, 'add');
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp(): void
    {
        $this->getRoutesProperty()->setValue(null, []);
    }

    protected function tearDown(): void
    {
        $this->getRoutesProperty()->setValue(null, []);
    }

    public function testGetRouteRegistered(): void
    {
        Router::get('/test', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertCount(1, $all);
        $this->assertSame('GET', $all[0]['method']);
    }

    public function testPostRouteRegistered(): void
    {
        Router::post('/test', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertCount(1, $all);
        $this->assertSame('POST', $all[0]['method']);
    }

    public function testSimplePathPattern(): void
    {
        $this->getAddMethod()->invoke(null, 'GET', '/blog', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertMatchesRegularExpression($all[0]['pattern'], '/blog');
        $this->assertDoesNotMatchRegularExpression($all[0]['pattern'], '/blog/123');
    }

    public function testParameterizedPathPattern(): void
    {
        $this->getAddMethod()->invoke(null, 'GET', '/blog/{slug}', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertMatchesRegularExpression($all[0]['pattern'], '/blog/my-post');
        $this->assertDoesNotMatchRegularExpression($all[0]['pattern'], '/blog/');
    }

    public function testTrailingSlashNotMatched(): void
    {
        $this->getAddMethod()->invoke(null, 'GET', '/admin', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertMatchesRegularExpression($all[0]['pattern'], '/admin');
        $this->assertDoesNotMatchRegularExpression($all[0]['pattern'], '/admin/');
        $this->assertDoesNotMatchRegularExpression($all[0]['pattern'], '/admin/dashboard');
    }

    public function testMultipleRoutesAccumulated(): void
    {
        Router::get('/a', fn() => null);
        Router::post('/b', fn() => null);
        Router::get('/c', fn() => null);
        $all = $this->getRoutesProperty()->getValue();
        $this->assertCount(3, $all);
    }

    public function testNumericParamAlwaysCastToInt(): void
    {
        $captured = null;
        Router::get('/product/{id}', function (int $id) use (&$captured) {
            $captured = $id;
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/product/abc';
        Router::dispatch();

        $this->assertSame(0, $captured);
    }

    public function testNamedParamMismatchDispatchesPositionally(): void
    {
        RouterTestControllerDouble::$lastProductId = -1;
        Router::post('/product/{id}/review', [RouterTestControllerDouble::class, 'postReview']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/product/42/review';
        Router::dispatch();

        $this->assertSame(42, RouterTestControllerDouble::$lastProductId);
    }

    public function testNonNumericIdOnTypedMethodDispatchedAsZero(): void
    {
        RouterTestControllerDouble::$lastProductId = -1;
        Router::post('/product/{id}/review', [RouterTestControllerDouble::class, 'postReview']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/product/abc/review';
        Router::dispatch();

        $this->assertSame(0, RouterTestControllerDouble::$lastProductId);
    }
}

class RouterTestControllerDouble
{
    public static int $lastProductId = -1;

    public function postReview(int $productId): void
    {
        self::$lastProductId = $productId;
    }
}
