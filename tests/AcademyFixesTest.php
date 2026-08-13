<?php

use App\Controllers\AcademyController;
use App\Controllers\AdminController;
use App\SEOService;
use App\StructuredData;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the Academy update (46):
 *  - A1: JSON course fields survive the admin save path un-mangled.
 *  - A2: completeLesson resolves the module from the global lesson index.
 *  - A4: Course schema emits correct timeRequired / free / courseMode + per-course SEO.
 */
final class AcademyFixesTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testCollectPostDataKeepsCourseJsonRaw(): void
    {
        $controller = new AdminController();
        $method = new ReflectionMethod(AdminController::class, 'collectPostData');

        $curriculum = '[{"title":"ماژول ۱","duration":"۱ ساعت","lessons":[{"title":"درس \"اول\"","duration":"۱۰ دقیقه"}]}]';
        $audience = '[{"title":"مخاطب \"الف\"","desc":"توضیح"}]';
        $faqs = '[{"q":"سؤال؟","a":"پاسخ \"با\" نقل‌قول"}]';
        $reviews = '[{"name":"علی","initial":"ع","rating":5,"text":"عالی \"بود\""}]';

        $_POST = [
            'title' => 'دوره تست',
            'curriculum' => $curriculum,
            'audience' => $audience,
            'faqs' => $faqs,
            'reviews' => $reviews,
        ];

        $data = $method->invoke($controller, 'courses');

        $this->assertSame($curriculum, $data['curriculum']);
        $this->assertSame($audience, $data['audience']);
        $this->assertSame($faqs, $data['faqs']);
        $this->assertSame($reviews, $data['reviews']);
    }

    public function testResolveLessonModuleMapsGlobalIndex(): void
    {
        $controller = new AcademyController();
        $method = new ReflectionMethod(AcademyController::class, 'resolveLessonModule');

        $curriculum = [
            ['lessons' => ['a', 'b']],
            ['lessons' => ['c', 'd', 'e']],
            ['lessons' => ['f']],
        ];

        $this->assertSame(0, $method->invoke($controller, $curriculum, 0));
        $this->assertSame(0, $method->invoke($controller, $curriculum, 1));
        $this->assertSame(1, $method->invoke($controller, $curriculum, 2));
        $this->assertSame(1, $method->invoke($controller, $curriculum, 4));
        $this->assertSame(2, $method->invoke($controller, $curriculum, 5));
        $this->assertNull($method->invoke($controller, $curriculum, 6));
        $this->assertNull($method->invoke($controller, $curriculum, -1));
    }

    public function testResolveLessonModuleHandlesEmptyModules(): void
    {
        $controller = new AcademyController();
        $method = new ReflectionMethod(AcademyController::class, 'resolveLessonModule');

        $curriculum = [
            ['title' => 'خالی', 'lessons' => []],
            ['title' => 'دارای درس', 'lessons' => ['x', 'y']],
        ];

        $this->assertSame(1, $method->invoke($controller, $curriculum, 0));
        $this->assertSame(1, $method->invoke($controller, $curriculum, 1));
        $this->assertNull($method->invoke($controller, $curriculum, 2));
    }

    public function testCourseSchemaTimeRequiredParsesPersianHours(): void
    {
        $schema = StructuredData::course([
            'title' => 'دوره رنگ مو',
            'slug' => 'hair-color',
            'type' => 'online',
            'is_free' => 0,
            'duration' => '۱۲ ساعت',
            'description' => 'توضیح',
            'price' => 100000,
        ]);

        $this->assertSame('PT12H', $schema['timeRequired']);
    }

    public function testCourseSchemaTimeRequiredParsesDecimalHours(): void
    {
        $schema = StructuredData::course([
            'title' => 'تست', 'slug' => 'x', 'type' => 'online', 'is_free' => 0,
            'duration' => '۱.۵ ساعت', 'description' => '', 'price' => 0,
        ]);

        $this->assertSame('PT1H30M', $schema['timeRequired']);
    }

    public function testCourseSchemaTimeRequiredParsesMinutes(): void
    {
        $schema = StructuredData::course([
            'title' => 'تست', 'slug' => 'x', 'type' => 'online', 'is_free' => 0,
            'duration' => '۴۵ دقیقه', 'description' => '', 'price' => 0,
        ]);

        $this->assertSame('PT45M', $schema['timeRequired']);
    }

    public function testCourseSchemaOmitsInvalidTimeRequired(): void
    {
        $schema = StructuredData::course([
            'title' => 'تست', 'slug' => 'x', 'type' => 'online', 'is_free' => 0,
            'duration' => 'غیر قابل شمارش', 'description' => '', 'price' => 0,
        ]);

        $this->assertArrayNotHasKey('timeRequired', $schema);
    }

    public function testCourseSchemaFreeCourse(): void
    {
        $schema = StructuredData::course([
            'title' => 'دوره رایگان', 'slug' => 'free', 'type' => 'online', 'is_free' => 1,
            'duration' => '', 'description' => '', 'price' => 0,
        ]);

        $this->assertTrue($schema['isAccessibleForFree']);
        $this->assertSame('online', $schema['hasCourseInstance']['courseMode']);
        $this->assertTrue($schema['hasCourseInstance']['isAccessibleForFree']);
    }

    public function testCourseSchemaOfflineCourseMode(): void
    {
        $schema = StructuredData::course([
            'title' => 'دوره حضوری', 'slug' => 'offline', 'type' => 'offline', 'is_free' => 0,
            'duration' => '', 'description' => '', 'price' => 500000,
        ]);

        $this->assertFalse($schema['isAccessibleForFree']);
        $this->assertSame('onsite', $schema['hasCourseInstance']['courseMode']);
        $this->assertSame('5000000', $schema['offers']['price']);
    }

    public function testSeoServiceForCourseUsesCourseData(): void
    {
        $seo = SEOService::forCourse([
            'title' => 'دوره تست',
            'slug' => 'test-course',
            'image' => 'course-test.jpg',
            'description' => '<p>توضیح کوتاه دوره</p>',
        ]);

        $this->assertStringStartsWith('دوره تست', $seo['title']);
        $this->assertStringEndsWith('/course/test-course', $seo['canonical']);
        $this->assertSame('توضیح کوتاه دوره', $seo['description']);
        $this->assertStringContainsString('/assets/images/course-test.jpg', $seo['og_image']);
    }

    public function testSeoServiceForCourseFallsBackToBrandName(): void
    {
        $seo = SEOService::forCourse([
            'title' => '',
            'slug' => 'no-title',
            'description' => '',
        ]);

        $this->assertNotSame('', $seo['title']);
        $this->assertStringEndsWith('/course/no-title', $seo['canonical']);
    }

    public function testAdminSettingsPageExposesAcademySupportKeys(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/admin/index.php');
        $this->assertStringContainsString('academy_support_days', $view);
        $this->assertStringContainsString('academy_support_guarantee', $view);
        $this->assertStringContainsString('academy_support_feature_1', $view);
        $this->assertStringContainsString('academy_support_feature_3', $view);
    }

    public function testUpgradeSqlRegistersAcademySupportKeys(): void
    {
        $upgrade = file_get_contents(__DIR__ . '/../database/upgrade_v46.sql');
        $install = file_get_contents(__DIR__ . '/../database/update_database.sql');

        foreach (['academy_support_days', 'academy_support_guarantee', 'academy_support_feature_1', 'academy_support_feature_2', 'academy_support_feature_3'] as $key) {
            $this->assertStringContainsString($key, $upgrade);
            $this->assertStringContainsString($key, $install);
        }
    }

    public function testDetailViewReadsSupportSettings(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/academy/detail.php');
        $this->assertStringContainsString("'academy_support_days'", $view);
        $this->assertStringContainsString("'academy_support_guarantee'", $view);
        $this->assertStringContainsString("'academy_support_feature_'", $view);
    }

    public function testAcademyIndexHasNoHardcodedFakeCourseCard(): void
    {
        $view = file_get_contents(__DIR__ . '/../app/views/academy/index.php');
        $this->assertStringNotContainsString('تکنیک‌های حرفه‌ای رنگ مو', $view);
        $this->assertStringNotContainsString('۳۴۲ دانشجو', $view);
    }

    public function testUpgradeSqlNormalizesFreeCourses(): void
    {
        $upgrade = file_get_contents(__DIR__ . '/../database/upgrade_v46.sql');
        $install = file_get_contents(__DIR__ . '/../database/update_database.sql');
        $this->assertStringContainsString('UPDATE courses SET is_free = 1 WHERE price <= 0', $upgrade);
        $this->assertStringContainsString('UPDATE courses SET is_free = 1 WHERE price <= 0', $install);
    }

    public function testAddCourseToCartBlocksZeroPriceCourses(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/ShopController.php');
        $this->assertStringContainsString("(int) \$course['price'] <= 0", $source);
    }

    public function testAdminSaveNormalizesCourseFreeState(): void
    {
        $source = file_get_contents(__DIR__ . '/../app/Controllers/AdminController.php');
        $this->assertStringContainsString('$isFree = $postedFree || (int) ($data[\'price\'] ?? 0) <= 0;', $source);
    }
}
