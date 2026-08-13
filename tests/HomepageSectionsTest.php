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
    public function testHomeControllerOrdersEducationByIdWithDynamicLimit(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString('WHERE is_active = 1 ORDER BY id LIMIT ?', $source);
        $this->assertStringNotContainsString('ORDER BY RAND()', $source);
        $this->assertStringContainsString("Settings::get('home_education_count', 4)", $source);
    }

    public function testHomeControllerFetchesPublishedPostsOnlyWithDynamicLimit(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString(
            "SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT ?",
            $source
        );
        $this->assertStringContainsString("Settings::get('home_blog_count', 3)", $source);
    }

    public function testHomeControllerPassesShowFlags(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/HomeController.php');
        $this->assertStringContainsString("'homeShowEducation' => (int) Settings::get('home_show_education', 1) === 1,", $source);
        $this->assertStringContainsString("'homeShowBlog' => (int) Settings::get('home_show_blog', 1) === 1,", $source);
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

    public function testEducationPartialIsFullyDynamic(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/partials/education.php');
        $this->assertStringContainsString('($homeShowEducation ?? true)', $view);
        $this->assertStringContainsString('&& !empty($educationCourses))', $view);
        $this->assertStringContainsString("\$settings['home_education_title'] ?? 'آموزش‌های رایگان زیبایی'", $view);
        $this->assertStringContainsString("\$settings['home_education_readmore'] ?? 'مشاهده تمام دوره‌ها در آکادمی'", $view);
        $this->assertStringContainsString("(int) \$course['is_free'] === 1", $view);
        $this->assertStringContainsString('رایگان', $view);
        $this->assertStringContainsString("\$course['teacher']", $view);
        $this->assertStringContainsString("nformat(\$course['price'])", $view);
    }

    public function testBlogSectionPartialIsFullyDynamic(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/partials/blog-section.php');
        $this->assertStringContainsString('($homeShowBlog ?? true)', $view);
        $this->assertStringContainsString('&& !empty($latestPosts))', $view);
        $this->assertStringContainsString("\$settings['home_blog_title'] ?? 'جدیدترین مقالات مجله زیبایی'", $view);
        $this->assertStringContainsString("\$settings['home_blog_readmore'] ?? 'بیشتر بخوانید'", $view);
        $this->assertStringContainsString("\$settings['home_blog_all_text'] ?? 'مشاهده همه مطالب'", $view);
        $this->assertStringContainsString("e(\$post['title'])", $view);
        $this->assertStringContainsString("e(\$post['excerpt'])", $view);
        $this->assertStringContainsString("e(\$post['slug'])", $view);
        $this->assertStringContainsString('placeholder.svg', $view);
    }

    public function testAdminSettingsExposeHomepageSectionKeys(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/admin/index.php');
        $this->assertStringContainsString("'home_show_education'", $view);
        $this->assertStringContainsString("'home_education_count'", $view);
        $this->assertStringContainsString("'home_education_title'", $view);
        $this->assertStringContainsString("'home_education_readmore'", $view);
        $this->assertStringContainsString("'home_show_blog'", $view);
        $this->assertStringContainsString("'home_blog_count'", $view);
        $this->assertStringContainsString("'home_blog_title'", $view);
        $this->assertStringContainsString("'home_blog_readmore'", $view);
        $this->assertStringContainsString("'home_blog_all_text'", $view);
        $this->assertStringContainsString("'home_education_count', 'home_blog_count'", $view);
    }

    public function testUpgradeSqlRegistersHomepageSettings(): void
    {
        $upgrade = file_get_contents(__DIR__ . '/../database/upgrade_v46.sql');
        $install = file_get_contents(__DIR__ . '/../database/update_database.sql');
        foreach (['home_show_education', 'home_education_count', 'home_education_title', 'home_education_readmore', 'home_show_blog', 'home_blog_count', 'home_blog_title', 'home_blog_readmore', 'home_blog_all_text'] as $key) {
            $this->assertStringContainsString("('" . $key . "'", $upgrade);
            $this->assertStringContainsString("('" . $key . "'", $install);
        }
    }

    public function testAdminClearCacheTouchesHomepageForCoursesAndBlog(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/AdminController.php');
        $this->assertStringContainsString("'courses' => ['academy', 'homepage'],", $source);
        $this->assertStringContainsString("'blog' => ['blog', 'homepage'],", $source);
    }
}
