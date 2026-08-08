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

    public function testVerifyErrorMessageCode101(): void
    {
        $this->assertSame('این تراکنش قبلاً تأیید شده است.', ZarinPal::verifyErrorMessage(101));
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

    public function testVerifyPaymentAlreadyVerified101(): void
    {
        $zpl = new FakeZarinPalVerify101();
        $result = $zpl->verifyPayment(1000, 'A00000000000000000000000000000000000');

        $this->assertTrue($result['status']);
        $this->assertTrue($result['already_verified']);
        $this->assertNull($result['ref_id']);
    }

    public function testVerifyPaymentSuccess100(): void
    {
        $zpl = new FakeZarinPalVerify100();
        $result = $zpl->verifyPayment(1000, 'A00000000000000000000000000000000000');

        $this->assertTrue($result['status']);
        $this->assertSame('123456789', $result['ref_id']);
        $this->assertSame('6219-8619-1234-5678', $result['card_pan']);
    }

    public function testVerifyPaymentError55(): void
    {
        $zpl = new FakeZarinPalVerifyError();
        $result = $zpl->verifyPayment(1000, 'A00000000000000000000000000000000000');

        $this->assertFalse($result['status']);
        $this->assertSame('تراکنش در مدت زمان مجاز به اتمام نرسیده (timeout).', $result['message']);
    }

    public function testIsConfiguredRejectsPlaceholderMerchant(): void
    {
        $zpl = new ZarinPal();
        $property = (new ReflectionClass(ZarinPal::class))->getProperty('merchantId');

        $property->setValue($zpl, '00000000-0000-0000-0000-000000000000');
        $this->assertFalse($zpl->isConfigured());

        $property->setValue($zpl, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
        $this->assertTrue($zpl->isConfigured());
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

class FakeZarinPalVerify101 extends ZarinPal
{
    public function isConfigured(): bool
    {
        return true;
    }

    protected function postJson(string $endpoint, array $data): array
    {
        return ['Status' => 101, 'RefID' => 'N0000000'];
    }
}

class FakeZarinPalVerify100 extends ZarinPal
{
    public function isConfigured(): bool
    {
        return true;
    }

    protected function postJson(string $endpoint, array $data): array
    {
        return ['Status' => 100, 'RefID' => '123456789', 'CardPan' => '6219-8619-1234-5678'];
    }
}

class FakeZarinPalVerifyError extends ZarinPal
{
    public function isConfigured(): bool
    {
        return true;
    }

    protected function postJson(string $endpoint, array $data): array
    {
        return ['Status' => -55];
    }
}
