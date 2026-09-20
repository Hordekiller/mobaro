<?php

use App\SEOService;
use App\Controllers\BlogController;
use App\Auth;
use App\Database;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the SEO fixes (Update 59 / Section "all High"):
 *  - Product detail pages get their own /product/{id} canonical (not /shop).
 *  - /models gets its own /models canonical (not /).
 *  - /blog and other pages keep correct canonicals.
 */
final class SEOFixesTest extends TestCase
{
    public function testProductDetailGetsOwnCanonical(): void
    {
        $seo = SEOService::forProduct([
            'id'          => 42,
            'name'        => 'شامپو طبیعی',
            'description' => 'توضیح محصول',
            'image'       => 'product-42.jpg',
            'slug'        => '',
        ]);

        $this->assertSame(url('/product/42'), $seo['canonical']);
        $this->assertSame('product', $seo['og_type']);
        $this->assertStringContainsString('شامپو طبیعی', $seo['title']);
    }

    public function testProductDetailCanonicalPrefersSlug(): void
    {
        $seo = SEOService::forProduct([
            'id'          => 42,
            'name'        => 'شامپو',
            'description' => 'd',
            'image'       => '',
            'slug'        => 'shampoo-natural',
        ]);

        $this->assertSame(url('/product/shampoo-natural'), $seo['canonical']);
    }

    public function testModelsPageGetsOwnCanonical(): void
    {
        $seo = SEOService::forPage('models');

        $this->assertSame(url('/models'), $seo['canonical']);
    }

    public function testStaticPagesKeepCorrectCanonical(): void
    {
        $this->assertSame(url('/faq'), SEOService::forPage('faq')['canonical']);
        $this->assertSame(url('/academy'), SEOService::forPage('academy')['canonical']);
        $this->assertSame(url('/'), SEOService::forPage('home')['canonical']);
    }

    public function testOgImageIsAbsoluteForProduct(): void
    {
        $seo = SEOService::forProduct([
            'id'          => 7,
            'name'        => 'نرم‌کننده',
            'description' => 'd',
            'image'       => 'conditioner.jpg',
            'slug'        => '',
        ]);

        $this->assertStringStartsWith('http', $seo['og_image']);
        $this->assertStringContainsString('conditioner.jpg', $seo['og_image']);
    }

    public function testGalleryCollectsImagesWithAbsoluteUrls(): void
    {
        $filepath = 'assets/images/_seo_fix_test_tmp.jpg';
        Database::delete('media', 'source_type = ?', ['tinymce_test_tmp']);
        Database::insert('media', [
            'filepath'      => $filepath,
            'original_name' => 'test.jpg',
            'type'          => 'image',
            'mime_type'     => 'image/jpeg',
            'size'          => 100,
            'source_type'   => 'tinymce_test_tmp',
            'uploaded_by'   => 3,
        ]);

        try {
            $method = new \ReflectionMethod(BlogController::class, 'collectGalleryEntries');
            $method->setAccessible(true);
            $entries = $method->invoke(new BlogController());

            $found = array_values(array_filter(
                $entries,
                fn($e) => str_ends_with($e['url'], '/assets/images/_seo_fix_test_tmp.jpg')
            ));

            $this->assertCount(1, $found);
            $this->assertStringStartsWith('http', $found[0]['url']);
            $this->assertStringStartsWith('http', $found[0]['original']);
            $this->assertStringContainsString('/media/stream/', $found[0]['original']);
        } finally {
            Database::delete('media', 'source_type = ?', ['tinymce_test_tmp']);
        }
    }
}
