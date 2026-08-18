<?php

use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the homepage education + blog sections (update 56 addendum):
 *  - Education section: deterministic ordering, free badge / price / teacher, empty-state guard.
 *  - Blog section: latest published posts only, correct placement, empty-state guard.
 *  - Cache invalidation: saving courses/blog also flushes the homepage cache.
 */
final class HomepageSectionsTest extends TestCase
{
    public function testHomeControllerOrdersEducationById(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString('WHERE is_active = 1 ORDER BY id LIMIT 4', $source);
        $this->assertStringNotContainsString('ORDER BY RAND()', $source);
    }

    public function testHomeControllerFetchesPublishedPostsOnly(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString(
            "SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT 3",
            $source
        );
    }

    public function testHomeViewPassesLatestPosts(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString("'latestPosts' => \$homeData['latestPosts'],", $source);
    }

    public function testHomeIndexIncludesBlogSectionAfterTestimonials(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/home/index.php');
        $testimonials = strpos($view, 'testimonials.php');
        $blog = strpos($view, 'blog-section.php');
        $newsletter = strpos($view, 'newsletter-block.php');
        $this->assertNotFalse($testimonials);
        $this->assertNotFalse($blog);
        $this->assertNotFalse($newsletter);
        $this->assertTrue($testimonials < $blog);
        $this->assertTrue($blog < $newsletter);
    }

    public function testEducationPartialHasFreePriceTeacherAndEmptyState(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/partials/education.php');
        $this->assertStringContainsString('if (!empty($educationCourses))', $view);
        $this->assertStringContainsString("(int) \$course['is_free'] === 1", $view);
        $this->assertStringContainsString('رایگان', $view);
        $this->assertStringContainsString("\$course['teacher']", $view);
        $this->assertStringContainsString("nformat(\$course['price'])", $view);
    }

    public function testBlogSectionPartialHasEmptyStateAndEscapedOutput(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/partials/blog-section.php');
        $this->assertStringContainsString('if (!empty($latestPosts))', $view);
        $this->assertStringContainsString("e(\$post['title'])", $view);
        $this->assertStringContainsString("e(\$post['excerpt'])", $view);
        $this->assertStringContainsString("e(\$post['slug'])", $view);
        $this->assertStringContainsString('placeholder.svg', $view);
    }

    public function testAdminClearCacheTouchesHomepageForCoursesAndBlog(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/AdminController.php');
        $this->assertStringContainsString("'courses' => ['academy', 'homepage'],", $source);
        $this->assertStringContainsString("'blog' => ['blog', 'homepage'],", $source);
    }
}
