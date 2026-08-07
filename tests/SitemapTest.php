<?php

use App\Controllers\SitemapController;
use PHPUnit\Framework\TestCase;

final class SitemapTest extends TestCase
{
    private static ?string $xml = null;

    private static string $fixtureFile = __DIR__ . '/../public/assets/uploads/videos/lesson.mp4';

    protected function setUp(): void
    {
        if (!is_file(self::$fixtureFile)) {
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
