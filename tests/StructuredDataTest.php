<?php

use App\StructuredData;
use PHPUnit\Framework\TestCase;

class StructuredDataTest extends TestCase
{
    public function testBreadcrumbStructure(): void
    {
        $breadcrumb = StructuredData::breadcrumb([
            ['name' => 'خانه', 'url' => 'https://mobaro.ir/'],
            ['name' => 'فروشگاه', 'url' => 'https://mobaro.ir/shop'],
        ]);

        $this->assertSame('BreadcrumbList', $breadcrumb['@type']);
        $this->assertCount(2, $breadcrumb['itemListElement']);
        $this->assertSame(1, $breadcrumb['itemListElement'][0]['position']);
        $this->assertSame(2, $breadcrumb['itemListElement'][1]['position']);
        $this->assertSame('https://schema.org', $breadcrumb['@context']);
    }

    public function testOrganizationUsesHairSalonType(): void
    {
        $org = StructuredData::organization();

        $this->assertArrayHasKey('@graph', $org);
        $types = array_column($org['@graph'], '@type');
        $this->assertContains('HairSalon', $types);
        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);

        $salon = array_values(array_filter($org['@graph'], fn ($n) => $n['@type'] === 'HairSalon'))[0];
        $this->assertNotEmpty($salon['name']);
    }

    public function testProductSchemaWithRating(): void
    {
        $product = StructuredData::product(
            ['id' => 5, 'name' => 'کرم مرطوب‌کننده', 'price' => 450000, 'old_price' => 500000, 'stock' => 3, 'brand' => 'موبارو', 'description' => '<p>توضیح کوتاه</p>'],
            [['rating' => 5, 'user_name' => 'علی', 'comment' => 'عالی بود'], ['rating' => 4, 'name' => 'سارا', 'review' => 'خوب']],
            4.5,
            2
        );

        $this->assertSame('Product', $product['@type']);
        $this->assertSame('IRR', $product['offers']['priceCurrency']);
        $this->assertSame('4500000', $product['offers']['price']);
        $this->assertSame('4500000', $product['offers']['priceSpecification']['price']);
        $this->assertSame('https://schema.org/InStock', $product['offers']['availability']);
        $this->assertSame('4.5', $product['aggregateRating']['ratingValue']);
        $this->assertSame(2, $product['aggregateRating']['reviewCount']);
        $this->assertCount(2, $product['review']);
    }

    public function testProductOutOfStock(): void
    {
        $product = StructuredData::product(
            ['id' => 9, 'name' => 'شامپو', 'price' => 100000, 'stock' => 0],
            [],
            0.0,
            0
        );

        $this->assertSame('https://schema.org/OutOfStock', $product['offers']['availability']);
        $this->assertArrayNotHasKey('aggregateRating', $product);
        $this->assertArrayNotHasKey('review', $product);
    }

    public function testBlogPostingHasRequiredFields(): void
    {
        $post = [
            'title' => 'راهنمای رنگ مو',
            'slug' => 'hair-color-guide',
            'excerpt' => 'خلاصه',
            'author' => 'تیم موبارو',
            'published_at' => '2026-01-01 10:00:00',
            'updated_at' => '2026-02-01 10:00:00',
            'image' => '',
        ];

        $schema = StructuredData::blogPosting($post);

        $this->assertSame('BlogPosting', $schema['@type']);
        $this->assertSame('راهنمای رنگ مو', $schema['headline']);
        $this->assertSame('2026-01-01T10:00:00+00:00', $schema['datePublished']);
        $this->assertSame('2026-02-01T10:00:00+00:00', $schema['dateModified']);
        $this->assertSame('تیم موبارو', $schema['author']['name']);
    }

    public function testCourseSchema(): void
    {
        $schema = StructuredData::course([
            'id' => 2,
            'title' => 'آموزش رنگ‌آمیزی حرفه‌ای',
            'slug' => 'pro-coloring',
            'teacher' => 'مریم',
            'price' => 1200000,
            'description' => 'دوره کامل رنگ‌آمیزی',
            'image' => 'course.jpg',
            'duration' => '30 ساعت',
            'level' => 'پیشرفته',
            'rating' => 4.8,
            'students' => 12,
        ]);

        $this->assertSame('Course', $schema['@type']);
        $this->assertSame('پیشرفته', $schema['coursePrerequisites']);
        $this->assertSame('PT30H', $schema['timeRequired']);
        $this->assertSame('4.8', $schema['aggregateRating']['ratingValue']);
        $this->assertSame('12000000', $schema['offers']['price']);
        $this->assertSame(false, $schema['isAccessibleForFree']);
    }

    public function testCourseFreeSchemaEmitsZeroRialPrice(): void
    {
        $schema = StructuredData::course([
            'id' => 3,
            'title' => 'دوره رایگان',
            'slug' => 'free-course',
            'price' => 0,
            'is_free' => 1,
        ]);

        $this->assertSame('IRR', $schema['offers']['priceCurrency']);
        $this->assertSame('0', $schema['offers']['price']);
        $this->assertSame(true, $schema['isAccessibleForFree']);
    }

    public function testRenderFiltersEmptyBlocks(): void
    {
        $result = StructuredData::render([], StructuredData::breadcrumb([['name' => 'خانه', 'url' => '/']]));

        $this->assertCount(1, $result);
        $this->assertSame('BreadcrumbList', $result[0]['@type']);
    }
}
