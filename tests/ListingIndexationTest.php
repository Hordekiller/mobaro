<?php

use App\SEOService;
use App\Controllers\BlogController;
use App\Controllers\ShopController;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the listing indexation strategy:
 *  - page 1 category/filter variants are indexable with a self-canonical;
 *  - search results and page>=2 get noindex,follow with a page-1 canonical;
 *  - an explicitly configured noindex is never downgraded.
 */
final class ListingIndexationTest extends TestCase
{
    private function shopSeo(array $filters): array
    {
        return ShopController::listingSeo(
            SEOService::forPage('shop'),
            $filters,
            (int) ($filters['page'] ?? 1),
            (string) ($filters['search'] ?? '')
        );
    }

    private function blogSeo(array $params): array
    {
        return BlogController::listingSeo(
            SEOService::forPage('blog'),
            (string) ($params['category'] ?? ''),
            (int) ($params['page'] ?? 1),
            (string) ($params['s'] ?? '')
        );
    }

    public function testShopPageOneStaysIndexableWithSelfCanonical(): void
    {
        $seo = $this->shopSeo(['category' => 'all', 'brand' => 'all']);

        $this->assertSame(url('/shop'), $seo['canonical']);
        $this->assertStringNotContainsString('noindex', (string) ($seo['robots'] ?? ''));
    }

    public function testShopCategoryPageGetsSelfCanonicalAndStaysIndexable(): void
    {
        $seo = $this->shopSeo(['category' => 'آرایشی']);

        $this->assertSame(url('/shop?category=%D8%A2%D8%B1%D8%A7%DB%8C%D8%B4%DB%8C'), $seo['canonical']);
        $this->assertStringNotContainsString('noindex', (string) ($seo['robots'] ?? ''));
    }

    public function testShopSearchIsNoindexFollowWithPageOneCanonical(): void
    {
        $seo = $this->shopSeo(['search' => 'شامپو']);

        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame(url('/shop'), $seo['canonical']);
    }

    public function testShopPageTwoIsNoindexFollowCanonicalToPageOne(): void
    {
        $seo = $this->shopSeo(['page' => 2]);

        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame(url('/shop'), $seo['canonical']);
    }

    public function testShopPaginatedCategoryCanonicalPointsAtCategoryPageOne(): void
    {
        $seo = $this->shopSeo(['category' => 'آرایشی', 'page' => 3]);

        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame(url('/shop?category=%D8%A2%D8%B1%D8%A7%DB%8C%D8%B4%DB%8C'), $seo['canonical']);
    }

    public function testBlogCategoryPageGetsSelfCanonical(): void
    {
        $seo = $this->blogSeo(['category' => 'آرایش']);

        $this->assertSame(url('/blog?category=%D8%A2%D8%B1%D8%A7%DB%8C%D8%B4'), $seo['canonical']);
        $this->assertStringNotContainsString('noindex', (string) ($seo['robots'] ?? ''));
    }

    public function testBlogSearchIsNoindexFollowCanonicalToBlog(): void
    {
        $seo = $this->blogSeo(['s' => 'مراقبت']);

        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame(url('/blog'), $seo['canonical']);
    }

    public function testBlogPageTwoIsNoindexFollowCanonicalToPageOne(): void
    {
        $seo = $this->blogSeo(['page' => 2]);

        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame(url('/blog'), $seo['canonical']);
    }

    public function testExplicitNoindexIsNeverDowngraded(): void
    {
        $seo = ShopController::listingSeo(
            [
                'title'       => 'فروشگاه',
                'description' => 'd',
                'canonical'   => '',
                'og_type'     => 'website',
                'robots'      => 'noindex,nofollow',
            ],
            ['category' => 'آرایشی'],
            1,
            ''
        );

        $this->assertSame('noindex,nofollow', $seo['robots']);
    }
}
