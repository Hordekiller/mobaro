<?php

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
}
