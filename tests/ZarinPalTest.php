<?php

use App\Services\ZarinPal;
use PHPUnit\Framework\TestCase;

class ZarinPalTest extends TestCase
{
    public function testTomanToRialConversion(): void
    {
        $this->assertSame(10000, ZarinPal::tomanToRial(1000));
        $this->assertSame(10, ZarinPal::tomanToRial(1));
        $this->assertSame(0, ZarinPal::tomanToRial(0));
        $this->assertSame(1000000, ZarinPal::tomanToRial(100000));
    }

    public function testRequestErrorMessageKnownCodes(): void
    {
        $this->assertSame('اطلاعات ارسال شده ناقص است.', ZarinPal::requestErrorMessage(-1));
        $this->assertSame('IP یا مرچنت کد صحیح نیست.', ZarinPal::requestErrorMessage(-2));
        $this->assertSame('مرچنت کد فعال نیست.', ZarinPal::requestErrorMessage(-11));
        $this->assertSame('مبلغ تراکنش از سقف مبلغ تراکنش بیشتر است.', ZarinPal::requestErrorMessage(-33));
    }

    public function testRequestErrorMessageUnknownCode(): void
    {
        $this->assertSame('خطای ناشناخته (کد: 999)', ZarinPal::requestErrorMessage(999));
    }

    public function testVerifyErrorMessageKnownCodes(): void
    {
        $this->assertSame('مبلغ با مبلغ درخواست مطابقت ندارد.', ZarinPal::verifyErrorMessage(-3));
        $this->assertSame('مرچنت کد فعال نیست.', ZarinPal::verifyErrorMessage(-11));
        $this->assertSame('تراکنش در مدت زمان مجاز به اتمام نرسیده (timeout).', ZarinPal::verifyErrorMessage(-55));
    }

    public function testVerifyErrorMessageUnknownCode(): void
    {
        $this->assertSame('خطای ناشناخته (کد: 404)', ZarinPal::verifyErrorMessage(404));
    }

    public function testRequestPaymentWithoutMerchantReturnsGuardMessage(): void
    {
        $zpl = new ZarinPal();
        if ($zpl->isConfigured()) {
            $this->markTestSkipped('مرچنت در محیط جاری تنظیم شده است.');
        }
        $result = $zpl->requestPayment(1000, 'سفارش تست', 'https://example.test/callback');
        $this->assertFalse($result['status']);
        $this->assertSame('مرچنت کد زرین‌پال در تنظیمات پرداخت تنظیم نشده است.', $result['message']);
    }

    public function testVerifyPaymentWithoutMerchantReturnsGuardMessage(): void
    {
        $zpl = new ZarinPal();
        if ($zpl->isConfigured()) {
            $this->markTestSkipped('مرچنت در محیط جاری تنظیم شده است.');
        }
        $result = $zpl->verifyPayment(1000, 'A00000000000000000000000000000000000');
        $this->assertFalse($result['status']);
        $this->assertSame('مرچنت کد زرین‌پال در تنظیمات پرداخت تنظیم نشده است.', $result['message']);
    }

    public function testSandboxModeIsDefaultInLocalEnv(): void
    {
        $zpl = new ZarinPal();
        $this->assertTrue($zpl->isSandbox());
    }

    public function testSandboxRequestIntegration(): void
    {
        if (getenv('RUN_SANDBOX_TESTS') !== '1') {
            $this->markTestSkipped('برای تست یکپارچگی سندباکس: RUN_SANDBOX_TESTS=1');
        }

        $zpl = new ZarinPal();
        $this->assertTrue($zpl->isSandbox(), 'تست سندباکس فقط با حالت Sandbox معتبر است.');
        $this->assertTrue($zpl->isConfigured(), 'مرچنت سندباکس باید تنظیم شده باشد.');

        $result = $zpl->requestPayment(
            1000,
            'تست یکپارچگی موبارو',
            'https://example.test/shop/payment/callback?order_id=1',
            null,
            '09123456789'
        );

        $this->assertArrayHasKey('status', $result);
        $this->assertNotSame('خطا در اتصال به درگاه: ', $result['message'] ?? '');
        if ($result['status']) {
            $this->assertNotEmpty($result['authority']);
            $this->assertStringContainsString('sandbox.zarinpal.com', $result['redirect_url']);
        }
    }
}
