<?php

use App\Controllers\SitemapController;
use App\Database;
use App\Settings;
use PHPUnit\Framework\TestCase;

final class SitemapTest extends TestCase
{
    private static ?string $xml = null;

    private static string $fixtureFile = __DIR__ . '/../public/assets/uploads/videos/lesson.mp4';

    protected function setUp(): void
    {
        if (!is_file(self::$fixtureFile)) {
            $fixtureDir = dirname(self::$fixtureFile);
            if (!is_dir($fixtureDir)) {
                mkdir($fixtureDir, 0775, true);
            }
            file_put_contents(self::$fixtureFile, 'dummy');
        }
    }

    protected function tearDown(): void
    {
        if (is_file(self::$fixtureFile)) {
            @unlink(self::$fixtureFile);
        }
    }

    private function xml(): string
    {
        if (self::$xml === null) {
            self::$xml = (new SitemapController())->generate();
        }
        return self::$xml;
    }

    public function testGeneratedXmlIsWellFormed(): void
    {
        $xml = $this->xml();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertSame(false, simplexml_load_string($xml) === false, 'XML must be well-formed');
    }

    public function testHasImageNamespace(): void
    {
        $this->assertStringContainsString(
            'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"',
            $this->xml()
        );
    }

    public function testNoPriorityOrChangefreq(): void
    {
        $xml = $this->xml();
        $this->assertStringNotContainsString('<priority>', $xml);
        $this->assertStringNotContainsString('<changefreq>', $xml);
    }

    public function testNoEmptySlugUrls(): void
    {
        $xml = $this->xml();
        $this->assertStringNotContainsString('/blog/</loc>', $xml);
        $this->assertStringNotContainsString('/course/</loc>', $xml);
    }

    public function testNoNumericSlugBlogPosts(): void
    {
        $this->assertDoesNotMatchRegularExpression('/<loc>[^<]*\/blog\/[0-9]+<\/loc>/', $this->xml());
    }

    public function testNoJunkTestSlugs(): void
    {
        $xml = $this->xml();
        foreach (['testblog', 'final-test-1785127443', 'dsffds', 'image-upload-test'] as $slug) {
            $this->assertStringNotContainsString('/blog/' . $slug . '</loc>', $xml);
        }
    }

    public function testLastmodPresent(): void
    {
        $this->assertStringContainsString('<lastmod>', $this->xml());
    }

    public function testLastmodIsW3cDatetimeWithTimezone(): void
    {
        $this->assertMatchesRegularExpression(
            '/<lastmod>[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}<\/lastmod>/',
            $this->xml()
        );
    }

    public function testHasVideoNamespace(): void
    {
        $this->assertStringContainsString(
            'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"',
            $this->xml()
        );
    }

    public function testVideoBlockYoutubeUsesPlayerLoc(): void
    {
        $block = $this->videoBlock([
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_type' => 'youtube',
            'title' => 'دوره رنگ و مش',
            'description' => 'آموزش حرفهای',
            'image' => 'course.jpg',
            'slug' => 'rang-o-mash',
        ]);

        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $block['player_loc']);
        $this->assertArrayNotHasKey('content_loc', $block);
        $this->assertStringContainsString('/assets/images/', $block['thumbnail_loc']);
        $this->assertMatchesRegularExpression('/^https?:\/\//', $block['thumbnail_loc']);
    }

    public function testVideoBlockAparatUsesPlayerLoc(): void
    {
        $block = $this->videoBlock([
            'video_url' => 'https://www.aparat.com/v/abc123',
            'video_type' => 'aparat',
            'title' => 'دوره',
            'description' => 'توضیح',
            'image' => '',
            'slug' => 'doreh',
        ]);

        $this->assertSame('https://www.aparat.com/v/abc123', $block['player_loc']);
        $this->assertStringContainsString('logo.png', $block['thumbnail_loc']);
    }

    public function testVideoBlockUploadUsesContentLoc(): void
    {
        $block = $this->videoBlock([
            'video_url' => '/assets/uploads/videos/lesson.mp4',
            'video_type' => 'upload',
            'title' => 'دوره',
            'description' => 'توضیح',
            'image' => 'course.jpg',
            'slug' => 'doreh',
        ]);

        $this->assertArrayNotHasKey('player_loc', $block);
        $this->assertStringContainsString('/assets/uploads/videos/lesson.mp4', $block['content_loc']);
    }

    public function testVideoBlockMissingImageFallsBackToLogo(): void
    {
        $block = $this->videoBlock([
            'video_url' => 'https://www.aparat.com/v/abc123',
            'video_type' => 'aparat',
            'title' => 'دوره',
            'description' => 'توضیح',
            'image' => 'course.jpg',
            'slug' => 'doreh',
        ]);

        $this->assertSame('https://www.aparat.com/v/abc123', $block['player_loc']);
        $this->assertStringContainsString('logo.png', $block['thumbnail_loc']);
    }

    public function testVideoBlockWithoutVideoReturnsNull(): void
    {
        $this->assertNull($this->videoBlock([
            'video_url' => '',
            'video_type' => 'upload',
            'title' => 'دوره',
            'description' => 'توضیح',
            'image' => '',
            'slug' => 'doreh',
        ]));
    }

    public function testAbsoluteImageMissingFileIsExcluded(): void
    {
        $this->assertSame('', $this->absoluteImage('mask_1.jpg'));
        $this->assertSame('', $this->absoluteImage('/assets/images/gallery_1.jpg'));
    }

    public function testAbsoluteImageExistingFileIsAbsoluteUrl(): void
    {
        $url = $this->absoluteImage('product-shampoo.jpg');
        $this->assertMatchesRegularExpression('/^https?:\/\/.*\/assets\/images\/product-shampoo\.jpg$/', $url);
    }

    public function testAbsoluteImageExternalUrlUntouched(): void
    {
        $this->assertSame('https://cdn.example.com/img/photo.jpg', $this->absoluteImage('https://cdn.example.com/img/photo.jpg'));
    }

    public function testVideoBlockUploadMissingFileReturnsNull(): void
    {
        $this->assertNull($this->videoBlock([
            'video_url' => '/assets/uploads/videos/does-not-exist.mp4',
            'video_type' => 'upload',
            'title' => 'دوره',
            'description' => 'توضیح',
            'image' => '',
            'slug' => 'doreh',
        ]));
    }

    public function testVideoTitleAndDescriptionAreTruncated(): void
    {
        $longTitle = str_repeat('ع', 150);
        $longDescription = str_repeat('ب', 3000);
        $block = $this->videoBlock([
            'video_url' => '/assets/uploads/videos/lesson.mp4',
            'video_type' => 'upload',
            'title' => $longTitle,
            'description' => $longDescription,
            'image' => '',
            'slug' => 'doreh',
        ]);

        $this->assertSame(100, mb_strlen($block['title']));
        $this->assertSame(2048, mb_strlen($block['description']));
    }

    public function testHasProductImageBlocks(): void
    {
        $xml = $this->xml();
        $this->assertStringContainsString('<image:image>', $xml);
        $this->assertStringContainsString('<image:loc>', $xml);
    }

    public function testImageLocIsAbsolute(): void
    {
        $this->assertMatchesRegularExpression('/<image:loc>https?:\/\//', $this->xml());
    }

    public function testXmlHasXslStylesheet(): void
    {
        $this->assertStringContainsString('<?xml-stylesheet type="text/xsl" href="', $this->xml());
    }

    public function testShouldPersistFilesSkipsLocalhost(): void
    {
        $ctrl = new SitemapController();
        $m = new ReflectionMethod(SitemapController::class, 'shouldPersistFiles');

        // Simulate a leftover localhost/dev APP_URL build: must refuse to
        // persist the static file so it can never shadow the production one.
        foreach (['localhost', '127.0.0.1', '::1', 'mybox.local', 'site.test', '192.168.1.9', '10.0.0.2', '172.16.5.5'] as $host) {
            $this->assertFalse($m->invoke($ctrl, $host), "must NOT persist for host {$host}");
        }
    }

    public function testShouldPersistFilesAllowsPublicHost(): void
    {
        $ctrl = new SitemapController();
        $m = new ReflectionMethod(SitemapController::class, 'shouldPersistFiles');

        $this->assertTrue($m->invoke($ctrl, 'mobaro.ir'));
        $this->assertTrue($m->invoke($ctrl, '8.8.8.8'));
    }

    public function testBlogSectionIsWellFormedWithImageCapability(): void
    {
        $built = $this->buildSection('blog');
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $built['xml']);
        $this->assertStringContainsString('<urlset', $built['xml']);
        $this->assertStringContainsString('xmlns:image=', $built['xml']);
        $this->assertSame(false, simplexml_load_string($built['xml']) === false);
    }

    public function testCoursesSectionIsWellFormedWithImageCapability(): void
    {
        $built = $this->buildSection('courses');
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $built['xml']);
        $this->assertStringContainsString('<urlset', $built['xml']);
        $this->assertStringContainsString('xmlns:image=', $built['xml']);
        $this->assertSame(false, simplexml_load_string($built['xml']) === false);
    }

    public function testPagesSectionIsWellFormedUrlset(): void
    {
        $built = $this->buildSection('pages');
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $built['xml']);
        $this->assertSame(false, simplexml_load_string($built['xml']) === false);
    }

    public function testUnknownSectionReturnsWellFormedEmptyUrlset(): void
    {
        $built = $this->buildSection('does-not-exist');
        $this->assertStringContainsString('<urlset', $built['xml']);
        $this->assertStringNotContainsString('<url>', $built['xml']);
        $this->assertSame(false, simplexml_load_string($built['xml']) === false);
    }

    public function testIndexIsSitemapIndexWithChildren(): void
    {
        $xml = $this->buildIndexXml([
            ['loc' => url('/sitemap-pages.xml'), 'lastmod' => '2026-08-07T10:00:00+03:30'],
            ['loc' => url('/sitemap-blog.xml'), 'lastmod' => '2026-08-07T10:00:00+03:30'],
        ]);

        $this->assertStringContainsString('<sitemapindex', $xml);
        $this->assertStringContainsString('<sitemap>', $xml);
        $this->assertStringContainsString('/sitemap-pages.xml', $xml);
        $this->assertStringContainsString('/sitemap-blog.xml', $xml);
        $this->assertStringContainsString('<?xml-stylesheet type="text/xsl" href="', $xml);
        $this->assertSame(false, simplexml_load_string($xml) === false);
    }

    public function testNewsSectionIncludesRecentPostsAndIsWellFormed(): void
    {
        $slug = 'news-test-' . bin2hex(random_bytes(4));
        $previous = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'sitemap_news_enabled'");

        try {
            Database::query(
                "INSERT INTO blog_posts (slug, title, excerpt, content, author, is_published, published_at, created_at, updated_at)
                 VALUES (?, 'خبر تستی', 'خلاصه', 'محتوا', 'تست', 1, NOW(), NOW(), NOW())",
                [$slug]
            );
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('sitemap_news_enabled', '1')
                 ON DUPLICATE KEY UPDATE setting_value = '1'"
            );
            Settings::invalidate();

            $built = $this->buildSection('news');
            $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $built['xml']);
            $this->assertStringContainsString('xmlns:news=', $built['xml']);
            $this->assertStringContainsString(url('/blog/' . $slug), $built['xml']);
            $this->assertStringContainsString('<news:publication>', $built['xml']);
            $this->assertStringContainsString('<news:language>fa</news:language>', $built['xml']);
            $this->assertSame(false, simplexml_load_string($built['xml']) === false);
        } finally {
            Database::query('DELETE FROM blog_posts WHERE slug = ?', [$slug]);
            if ($previous) {
                Database::query("UPDATE settings SET setting_value = ? WHERE setting_key = 'sitemap_news_enabled'", [$previous['setting_value']]);
            } else {
                Database::query("DELETE FROM settings WHERE setting_key = 'sitemap_news_enabled'");
            }
            Settings::invalidate();
        }
    }

    public function testNewsSectionIsEmptyUrlsetWhenDisabled(): void
    {
        $previous = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'sitemap_news_enabled'");

        try {
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('sitemap_news_enabled', '0')
                 ON DUPLICATE KEY UPDATE setting_value = '0'"
            );
            Settings::invalidate();

            $built = $this->buildSection('news');
            $this->assertStringContainsString('<urlset', $built['xml']);
            $this->assertStringNotContainsString('<url>', $built['xml']);
            $this->assertSame(false, simplexml_load_string($built['xml']) === false);
        } finally {
            if ($previous) {
                Database::query("UPDATE settings SET setting_value = ? WHERE setting_key = 'sitemap_news_enabled'", [$previous['setting_value']]);
            } else {
                Database::query("DELETE FROM settings WHERE setting_key = 'sitemap_news_enabled'");
            }
            Settings::invalidate();
        }
    }

    /**
     * @return array{xml: string, lastmod: string}
     */
    private function buildSection(string $name): array
    {
        $controller = new SitemapController();
        $method = new ReflectionMethod(SitemapController::class, 'buildSection');
        return $method->invoke($controller, $name);
    }

    /**
     * @param array<int, array{loc: string, lastmod: string}> $entries
     */
    private function buildIndexXml(array $entries): string
    {
        $controller = new SitemapController();
        $method = new ReflectionMethod(SitemapController::class, 'buildIndexXml');
        return $method->invoke($controller, $entries);
    }

    /**
     * @param array<string, mixed> $course
     * @return array<string, string>|null
     */
    private function videoBlock(array $course): ?array
    {
        $controller = new SitemapController();
        $method = new ReflectionMethod(SitemapController::class, 'videoBlock');
        return $method->invoke($controller, $course);
    }

    private function absoluteImage(string $image): string
    {
        $controller = new SitemapController();
        $method = new ReflectionMethod(SitemapController::class, 'absoluteImage');
        return $method->invoke($controller, $image);
    }
}
