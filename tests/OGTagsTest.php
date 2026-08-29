<?php

use App\SEOService;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for Open Graph tag fixes (update 58 / Section 4):
 *  - og:type is article for blog posts and website for every other page.
 *  - og:url / og:site_name / og:type are emitted from the shared header.
 */
final class OGTagsTest extends TestCase
{
    public function testBlogSeoMarksOgTypeArticle(): void
    {
        $seo = SEOService::forBlogPost([
            'title'            => 'راهنمای مراقبت از مو',
            'slug'             => 'hair-care-guide',
            'meta_title'       => 'راهنما | موبارو',
            'meta_description' => 'متن توضیح',
            'excerpt'          => 'خلاصه',
            'image'            => '',
            'canonical_url'    => '',
            'og_title'         => '',
            'og_description'   => '',
            'og_image'         => '',
            'robots'           => 'index,follow',
        ]);
        $this->assertArrayHasKey('og_title', $seo);
        $this->assertArrayHasKey('og_desc', $seo);
        $this->assertArrayHasKey('og_image', $seo);

        $this->assertSame('article', $seo['og_type']);
    }

    public function testCourseAndPageSeoMarkOgTypeWebsite(): void
    {
        $course = SEOService::forCourse([
            'title'       => 'دوره آرایش',
            'description' => 'توضیح',
            'image'       => '',
            'id'          => 1,
            'slug'        => null,
        ]);
        $this->assertSame('website', $course['og_type']);

        $page = SEOService::forPage('home');
        $this->assertSame('website', $page['og_type']);
    }

    public function testHeaderEmitsOgUrlSiteNameAndDynamicType(): void
    {
        $settings = ['brand_name' => 'موبارو', 'site_name' => 'موبارو'];
        $seo = ['og_type' => 'website', 'canonical' => url('/about')];
        $before = $_SERVER['REQUEST_URI'] ?? null;
        $_SERVER['REQUEST_URI'] = '/about';

        ob_start();
        require __DIR__ . '/../app/views/layouts/header.php';
        $html = ob_get_clean();

        if ($before !== null) {
            $_SERVER['REQUEST_URI'] = $before;
        }

        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="' . url('/about') . '">', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="موبارو">', $html);
        $this->assertStringContainsString('<meta property="og:locale" content="fa_IR">', $html);
    }
}
