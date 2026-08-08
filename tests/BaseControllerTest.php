<?php

use App\Controllers\BaseController;
use PHPUnit\Framework\TestCase;

final class BaseControllerTest extends TestCase
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultSchema(array $data, string $uri): array
    {
        $previous = $_SERVER['REQUEST_URI'] ?? null;
        $_SERVER['REQUEST_URI'] = $uri;
        try {
            $controller = new BaseController();
            $method = new ReflectionMethod(BaseController::class, 'defaultSchema');
            return $method->invoke($controller, $data);
        } finally {
            if ($previous === null) {
                unset($_SERVER['REQUEST_URI']);
            } else {
                $_SERVER['REQUEST_URI'] = $previous;
            }
        }
    }

    private function types(array $blocks): array
    {
        $types = [];
        foreach ($blocks as $block) {
            $types[] = $block['@type'] ?? null;
        }
        return $types;
    }

    public function testHomepagePathOnlyEmitsOrganizationGraph(): void
    {
        $blocks = $this->defaultSchema(['seo' => ['title' => 'خانه']], '/');
        $this->assertCount(1, $blocks);
        $this->assertSame('@graph', array_keys($blocks[0])[0]);
        $graphTypes = array_column($blocks[0]['@graph'], '@type');
        $this->assertContains('HairSalon', $graphTypes);
        $this->assertContains('WebSite', $graphTypes);
    }

    public function testInteriorPageEmitsOrganizationBreadcrumbAndWebPage(): void
    {
        $blocks = $this->defaultSchema(
            ['seo' => ['title' => 'فروشگاه', 'description' => 'توضیح فروشگاه']],
            '/shop'
        );

        $this->assertCount(3, $blocks);
        $this->assertSame('@graph', array_keys($blocks[0])[0]);
        $this->assertSame(['BreadcrumbList', 'WebPage'], $this->types(array_slice($blocks, 1)));

        $breadcrumb = $blocks[1];
        $this->assertSame(1, $breadcrumb['itemListElement'][0]['position']);
        $this->assertSame(2, $breadcrumb['itemListElement'][1]['position']);
        $this->assertSame('فروشگاه', $breadcrumb['itemListElement'][1]['name']);

        $webPage = $blocks[2];
        $this->assertSame('فروشگاه', $webPage['name']);
        $this->assertSame('توضیح فروشگاه', $webPage['description']);
        $this->assertStringContainsString('/shop', $webPage['url']);
    }

    public function testPageNameFallsBackToBrandWhenNoSeoTitle(): void
    {
        $blocks = $this->defaultSchema(['settings' => ['brand_name' => 'موبارو']], '/booking');

        $this->assertCount(3, $blocks);
        $this->assertSame('BreadcrumbList', $blocks[1]['@type']);
        $this->assertSame('موبارو', $blocks[1]['itemListElement'][1]['name']);
        $this->assertSame('موبارو', $blocks[2]['name']);
    }

    public function testExistingJsonLdIsNeverOverriddenByView(): void
    {
        $controller = new BaseController();

        $method = new ReflectionMethod(BaseController::class, 'skipDefaultSchema');
        $this->assertFalse($method->invoke($controller, 'shop/index'));
        $this->assertFalse($method->invoke($controller, 'blog/index'));
        $this->assertTrue($method->invoke($controller, 'admin/index'));
        $this->assertTrue($method->invoke($controller, 'dashboard/index'));
        $this->assertTrue($method->invoke($controller, 'auth/login'));
        $this->assertTrue($method->invoke($controller, 'academy/watch'));
        $this->assertTrue($method->invoke($controller, 'academy/certificate'));
        $this->assertTrue($method->invoke($controller, 'home/cart'));
        $this->assertTrue($method->invoke($controller, 'shop/wishlist'));
    }

    public function testStringSeoValueNeverBreaksDefaultSchema(): void
    {
        $blocks = $this->defaultSchema(['seo' => 'not-an-array', 'title' => 'صفحه'], '/test');

        $this->assertCount(3, $blocks);
        $this->assertSame('BreadcrumbList', $blocks[1]['@type']);
        $this->assertSame('صفحه', $blocks[1]['itemListElement'][1]['name']);
    }
}
