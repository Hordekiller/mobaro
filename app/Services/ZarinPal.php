<?php

class ZarinPal
{
    private bool $sandbox;
    private string $merchantId;
    private string $baseUrl;
    private string $apiUrl;

    public function __construct()
    {
        $this->sandbox = Config::get('zarinpal.sandbox', true);
        $this->merchantId = Config::get('zarinpal.merchant_id', '');
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/';
        $this->apiUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/rest/WebGate/'
            : 'https://www.zarinpal.com/pg/rest/WebGate/';
    }

    public function requestPayment(int $amount, string $description, string $callbackUrl, ?string $email = null, ?string $mobile = null): array
    {
        $data = [
            'MerchantID' => $this->merchantId,
            'Amount' => $amount,
            'Description' => $description,
            'CallbackURL' => $callbackUrl,
        ];

        if ($email) $data['Email'] = $email;
        if ($mobile) $data['Mobile'] = $mobile;

        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->apiUrl . 'Request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mobaro');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => false, 'message' => 'خطا در اتصال به درگاه: ' . $err];
        }

        $response = json_decode($result, true);

        if (!empty($response['Status']) && $response['Status'] === 100) {
            return [
                'status' => true,
                'authority' => $response['Authority'],
                'redirect_url' => $this->baseUrl . $response['Authority'],
            ];
        }

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

        $code = $response['Status'] ?? -99;
        return ['status' => false, 'message' => $errors[$code] ?? 'خطای ناشناخته (کد: ' . $code . ')'];
    }

    public function verifyPayment(int $amount, string $authority): array
    {
        $data = [
            'MerchantID' => $this->merchantId,
            'Amount' => $amount,
            'Authority' => $authority,
        ];

        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->apiUrl . 'Verification.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mobaro');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => false, 'message' => 'خطا در تایید پرداخت: ' . $err];
        }

        $response = json_decode($result, true);

        if (!empty($response['Status']) && $response['Status'] === 100) {
            return [
                'status' => true,
                'ref_id' => $response['RefID'],
                'card_pan' => $response['CardPan'] ?? '',
            ];
        }

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
        ];

        $code = $response['Status'] ?? -99;
        return ['status' => false, 'message' => $errors[$code] ?? 'خطای ناشناخته (کد: ' . $code . ')'];
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
