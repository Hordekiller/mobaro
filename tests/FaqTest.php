<?php

use App\Controllers\SitemapController;
use App\StructuredData;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the global FAQ system (update 46).
 */
final class FaqTest extends TestCase
{
    public function testFaqPageSchemaStructure(): void
    {
        $schema = StructuredData::faqPage([
            ['question' => 'سؤال اول', 'answer' => 'پاسخ اول'],
            ['question' => 'سؤال دوم', 'answer' => 'پاسخ دوم'],
            ['question' => '', 'answer' => 'بدون سؤال'],
        ]);

        $this->assertSame('FAQPage', $schema['@type']);
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertCount(2, $schema['mainEntity']);
        $this->assertSame('Question', $schema['mainEntity'][0]['@type']);
        $this->assertSame('سؤال اول', $schema['mainEntity'][0]['name']);
        $this->assertSame('Answer', $schema['mainEntity'][0]['acceptedAnswer']['@type']);
        $this->assertSame('پاسخ اول', $schema['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function testFaqPageAnswerIsTruncated(): void
    {
        $schema = StructuredData::faqPage([
            ['question' => 'سؤال', 'answer' => str_repeat('پ', 400)],
        ]);

        $this->assertLessThanOrEqual(301, mb_strlen($schema['mainEntity'][0]['acceptedAnswer']['text']));
    }

    public function testFaqPageSkipsEmptyEntries(): void
    {
        $schema = StructuredData::faqPage([
            ['question' => '', 'answer' => ''],
        ]);

        $this->assertSame([], $schema['mainEntity']);
    }

    public function testSitemapIncludesFaqPage(): void
    {
        $xml = (new SitemapController())->generate();

        $this->assertStringContainsString('<loc>' . url('/faq') . '</loc>', $xml);
    }
}
