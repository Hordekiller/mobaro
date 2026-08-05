<?php

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testSanitizeConvertsPersianDigits(): void
    {
        $result = sanitize('۰۱۲۳۴۵۶۷۸۹');
        $this->assertSame('0123456789', $result);
    }

    public function testSanitizeTrimsAndEscapes(): void
    {
        $result = sanitize('  hello  ');
        $this->assertSame('hello', $result);
    }

    public function testSanitizeEscapesHtml(): void
    {
        $result = sanitize('<script>alert("xss")</script>');
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testEFunctionEscapesHtml(): void
    {
        $result = e('<b>test</b>');
        $this->assertSame('&lt;b&gt;test&lt;/b&gt;', $result);
    }

    public function testEFunctionCastsTo(): void
    {
        $this->assertSame('123', e(123));
        $this->assertSame('', e(null));
    }

    public function testPriceFormatAddsToman(): void
    {
        $result = priceFormat(150000);
        $this->assertSame('150,000 تومان', $result);
    }

    public function testPriceFormatZero(): void
    {
        $this->assertSame('0 تومان', priceFormat(0));
    }

    public function testSlugifyNormalizesText(): void
    {
        $result = slugify('Hello World');
        $this->assertSame('hello-world', $result);
    }

    public function testSlugifyRemovesSpecialChars(): void
    {
        $result = slugify('Hello! @World# $Test%');
        $this->assertSame('hello-world-test', $result);
    }

    public function testSlugifyEmptyReturnsFallback(): void
    {
        $result = slugify('!!!');
        $this->assertStringStartsWith('item-', $result);
    }

    public function testTruncateShortTextUnchanged(): void
    {
        $this->assertSame('hello', truncate('hello', 10));
    }

    public function testTruncateLongTextIsTruncated(): void
    {
        $result = truncate('hello world', 5);
        $this->assertSame('hello...', $result);
    }

    public function testFaNumConvertsDigits(): void
    {
        $result = faNum(123);
        $this->assertSame('۱۲۳', $result);
    }

    public function testFaNumZero(): void
    {
        $this->assertSame('۰', faNum(0));
    }

    public function testFaNumAcceptsString(): void
    {
        $this->assertSame('۱۲۳', faNum('123'));
        $this->assertSame('۰', faNum('0'));
    }

    public function testFaNumAcceptsFloat(): void
    {
        $this->assertSame('۰', faNum(0.0));
        $this->assertSame('۱۶۵.۳', faNum(165.3));
        $this->assertSame('۱۲۰.۵', faNum('120.50'));
    }

    public function testGregorianToJalaliBasic(): void
    {
        $result = gregorianToJalali(2024, 1, 1);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertGreaterThan(1400, $result[0]);
    }

    public function testJdateFormatsCorrectly(): void
    {
        $result = jdate('Y/m/d', mktime(0, 0, 0, 1, 1, 2024));
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/\d{2}$/', $result);
    }

    public function testGetYoutubeIdExtractsId(): void
    {
        $this->assertSame('dQw4w9WgXcQ', getYoutubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', getYoutubeId('https://youtu.be/dQw4w9WgXcQ'));
    }

    public function testGetYoutubeIdReturnsEmptyOnInvalid(): void
    {
        $this->assertSame('', getYoutubeId('https://example.com'));
    }

    public function testGetAparatHashExtractsHash(): void
    {
        $this->assertSame('abc123', getAparatHash('https://www.aparat.com/v/abc123'));
    }

    public function testGetAparatHashReturnsEmptyOnInvalid(): void
    {
        $this->assertSame('', getAparatHash('https://example.com'));
    }

    public function testFaToEnDigitsConvertsPersian(): void
    {
        $this->assertSame('0123456789', faToEnDigits('۰۱۲۳۴۵۶۷۸۹'));
    }

    public function testFaToEnDigitsConvertsArabic(): void
    {
        $this->assertSame('0123456789', faToEnDigits('٠١٢٣٤٥٦٧٨٩'));
    }

    public function testFaToEnDigitsKeepsLatin(): void
    {
        $this->assertSame('09123456789', faToEnDigits('۰۹۱۲۳۴۵۶۷۸۹'));
    }

    public function testNormalizePhoneWithZeroPrefix(): void
    {
        $this->assertSame('09130201234', normalizePhone('09130201234'));
    }

    public function testNormalizePhoneWithoutZeroPrefix(): void
    {
        $this->assertSame('09130201234', normalizePhone('9130201234'));
    }

    public function testNormalizePhoneWithCountryCode(): void
    {
        $this->assertSame('09130201234', normalizePhone('989130201234'));
        $this->assertSame('09130201234', normalizePhone('+989130201234'));
        $this->assertSame('09130201234', normalizePhone('00989130201234'));
    }

    public function testNormalizePhoneWithPersianDigitsAndSeparators(): void
    {
        $this->assertSame('09130201234', normalizePhone('۰۹۱۳ ۰۲۰ ۱۲۳۴'));
        $this->assertSame('09130201234', normalizePhone('+98 913 020 1234'));
    }

    public function testNormalizePhoneRejectsInvalid(): void
    {
        $this->assertSame('', normalizePhone('91333347128'));
        $this->assertSame('', normalizePhone('03136662122'));
        $this->assertSame('', normalizePhone('1234'));
        $this->assertSame('', normalizePhone('091302012340'));
        $this->assertSame('', normalizePhone(''));
        $this->assertSame('', normalizePhone('abc'));
    }

    public function testPhoneForSmsOutputsCountryCode(): void
    {
        $this->assertSame('989130201234', phoneForSms('09130201234'));
        $this->assertSame('989130201234', phoneForSms('9130201234'));
        $this->assertSame('989130201234', phoneForSms('+989130201234'));
    }

    public function testPhoneForSmsRejectsInvalid(): void
    {
        $this->assertSame('', phoneForSms('03136662122'));
        $this->assertSame('', phoneForSms('1234'));
    }

    public function testSmsStatusLabelMapsPersian(): void
    {
        $this->assertSame('در انتظار', smsStatusLabel('pending'));
        $this->assertSame('ارسال شد', smsStatusLabel('shipped'));
        $this->assertSame('تحویل شد', smsStatusLabel('delivered'));
        $this->assertSame('تکمیل شد', smsStatusLabel('completed'));
        $this->assertSame('لغو شد', smsStatusLabel('cancelled'));
        $this->assertSame('ناموفق', smsStatusLabel('failed'));
    }

    public function testSmsStatusLabelFallsBackToRawValue(): void
    {
        $this->assertSame('weird-status', smsStatusLabel('weird-status'));
    }

    public function testSmsStatusLabelIsCaseInsensitive(): void
    {
        $this->assertSame('تأیید شد', smsStatusLabel('CONFIRMED'));
    }
}
