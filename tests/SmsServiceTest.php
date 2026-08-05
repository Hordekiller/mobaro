<?php

use App\Services\SmsService;
use PHPUnit\Framework\TestCase;

class SmsServiceTest extends TestCase
{
    public function testRenderTemplateReplacesVariables(): void
    {
        $result = SmsService::renderTemplate('وضعیت سفارش {Code} شما: {Status}', [
            'Code' => 'MB-123',
            'Status' => 'ارسال شد',
        ]);
        $this->assertSame('وضعیت سفارش MB-123 شما: ارسال شد', $result);
    }

    public function testRenderTemplateIsCaseInsensitive(): void
    {
        $result = SmsService::renderTemplate('کد {code} است.', ['Code' => '12345']);
        $this->assertSame('کد 12345 است.', $result);
    }

    public function testRenderTemplateRemovesMissingVariables(): void
    {
        $result = SmsService::renderTemplate('وضعیت نوبت شما در تاریخ {Date} ساعت {Time}: {Status}', [
            'Date' => '1404-05-20',
        ]);
        $this->assertSame('وضعیت نوبت شما در تاریخ 1404-05-20 ساعت :', $result);
    }

    public function testRenderTemplateHandlesEmptyBody(): void
    {
        $this->assertSame('', SmsService::renderTemplate(''));
        $this->assertSame('', SmsService::renderTemplate('   '));
    }

    public function testRenderTemplateSupportsPersianVariableNames(): void
    {
        $result = SmsService::renderTemplate('سلام {نام} عزیز', ['نام' => 'مریم']);
        $this->assertSame('سلام مریم عزیز', $result);
    }

    public function testChunkPhonesSplitsAtHundred(): void
    {
        $phones = array_map(fn($i) => '09' . str_pad((string) (100000000 + $i), 9, '0', STR_PAD_LEFT), range(1, 250));
        $chunks = SmsService::chunkPhones($phones);

        $this->assertCount(3, $chunks);
        $this->assertCount(100, $chunks[0]);
        $this->assertCount(100, $chunks[1]);
        $this->assertCount(50, $chunks[2]);
    }

    public function testChunkPhonesKeepsSmallListIntact(): void
    {
        $phones = ['09121234567', '09131234567'];
        $this->assertSame([['09121234567', '09131234567']], SmsService::chunkPhones($phones));
    }
}
