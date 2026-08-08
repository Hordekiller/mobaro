<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use App\Settings;

class ZarinPal
{
    private const TOMAN_TO_RIAL = 10;

    private const MSG_MERCHANT_MISSING = 'مرچنت کد زرین‌پال در تنظیمات پرداخت تنظیم نشده است.';

    private bool $sandbox;
    private string $merchantId;
    private string $baseUrl;
    private string $apiUrl;
    private array $lastRequest = [];
    private ?array $lastResponse = null;

    public function __construct()
    {
        $this->sandbox = (Settings::get('zarinpal_sandbox', '') !== ''
            ? Settings::get('zarinpal_sandbox', '0') === '1'
            : Config::get('zarinpal.sandbox', true));
        $this->merchantId = Settings::get('zarinpal_merchant_id', '') ?: (string) Config::get('zarinpal.merchant_id', '');
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/';
        $this->apiUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/rest/WebGate/'
            : 'https://www.zarinpal.com/pg/rest/WebGate/';
    }

    public static function tomanToRial(int $toman): int
    {
        return $toman * self::TOMAN_TO_RIAL;
    }

    public function isConfigured(): bool
    {
        $normalized = str_replace('-', '', $this->merchantId);

        return $this->merchantId !== '' && $normalized !== '' && str_repeat('0', strlen($normalized)) !== $normalized;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getLastRequest(): array
    {
        return $this->lastRequest;
    }

    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    public function requestPayment(int $amount, string $description, string $callbackUrl, ?string $email = null, ?string $mobile = null): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => self::MSG_MERCHANT_MISSING];
        }

        $this->lastRequest = [
            'MerchantID' => $this->merchantId,
            'Amount' => self::tomanToRial($amount),
            'Description' => $description,
            'CallbackURL' => $callbackUrl,
            'Email' => $email,
            'Mobile' => $mobile,
        ];
        $this->lastResponse = null;

        $data = array_filter($this->lastRequest, fn($v) => $v !== null && $v !== '');

        $response = $this->postJson('Request.json', $data);
        $this->lastResponse = $response;

        if (!empty($response['curl_error'])) {
            return ['status' => false, 'message' => 'خطا در اتصال به درگاه: ' . $response['curl_error']];
        }

        if (!empty($response['Status']) && (int) $response['Status'] === 100) {
            return [
                'status' => true,
                'authority' => $response['Authority'],
                'redirect_url' => $this->baseUrl . $response['Authority'],
            ];
        }

        $code = (int) ($response['Status'] ?? -99);
        return ['status' => false, 'message' => self::requestErrorMessage($code)];
    }

    public function verifyPayment(int $amount, string $authority): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => self::MSG_MERCHANT_MISSING];
        }

        $this->lastRequest = [
            'MerchantID' => $this->merchantId,
            'Amount' => self::tomanToRial($amount),
            'Authority' => $authority,
        ];
        $this->lastResponse = null;

        $data = array_filter($this->lastRequest, fn($v) => $v !== null && $v !== '');

        $response = $this->postJson('Verification.json', $data);
        $this->lastResponse = $response;

        if (!empty($response['curl_error'])) {
            return ['status' => false, 'message' => 'خطا در تایید پرداخت: ' . $response['curl_error']];
        }

        if (!empty($response['Status']) && (int) $response['Status'] === 100) {
            return [
                'status' => true,
                'ref_id' => $response['RefID'],
                'card_pan' => $response['CardPan'] ?? '',
            ];
        }

        if (!empty($response['Status']) && (int) $response['Status'] === 101) {
            return [
                'status' => true,
                'ref_id' => null,
                'card_pan' => '',
                'already_verified' => true,
            ];
        }

        $code = (int) ($response['Status'] ?? -99);
        return ['status' => false, 'message' => self::verifyErrorMessage($code)];
    }

    protected function postJson(string $endpoint, array $data): array
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mobaro');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['curl_error' => $err];
        }

        $response = json_decode((string) $result, true);

        return is_array($response) ? $response : ['raw' => (string) $result];
    }

    public static function requestErrorMessage(int $code): string
    {
        $errors = [
            -1 => 'اطلاعات ارسال شده ناقص است.',
            -2 => 'IP یا مرچنت کد صحیح نیست.',
            -3 => 'سطح تاجر باید از زیرساخت های درگاه باشد.',
            -4 => 'سطح تاجر معتبر نیست.',
            -11 => 'مرچنت کد فعال نیست.',
            -12 => 'تلاش بیش از حد در یک بازه زمانی کوتاه.',
            -21 => 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد.',
            -22 => 'تراکنش ناموفق است.',
            -33 => 'مبلغ تراکنش از سقف مبلغ تراکنش بیشتر است.',
            -54 => 'درخواست نامعتبر (آرایشگر مورد نظر وجود ندارد).',
        ];

        return $errors[$code] ?? 'خطای ناشناخته (کد: ' . $code . ')';
    }

    public static function verifyErrorMessage(int $code): string
    {
        $errors = [
            -1 => 'اطلاعات ارسال شده ناقص است.',
            -2 => 'IP یا مرچنت کد صحیح نیست.',
            -3 => 'مبلغ با مبلغ درخواست مطابقت ندارد.',
            -11 => 'مرچنت کد فعال نیست.',
            -12 => 'تلاش بیش از حد در یک بازه زمانی کوتاه.',
            -21 => 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد.',
            -22 => 'تراکنش ناموفق است.',
            -33 => 'مبلغ تراکنش از سقف مبلغ تراکنش بیشتر است.',
            -54 => 'درخواست نامعتبر است.',
            -55 => 'تراکنش در مدت زمان مجاز به اتمام نرسیده (timeout).',
            101 => 'این تراکنش قبلاً تأیید شده است.',
        ];

        return $errors[$code] ?? 'خطای ناشناخته (کد: ' . $code . ')';
    }
}
