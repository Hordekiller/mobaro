<?php

class SmsService
{
    private string $apiKey;
    private string $sender;
    private string $baseUrl = 'https://api.kavenegar.com/v1';

    public function __construct()
    {
        $this->apiKey = Config::get('sms.api_key', '');
        $this->sender = Config::get('sms.sender', '');
    }

    public function sendVerify(string $phone, string $code, string $token = ''): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'کلید API پیامک تنظیم نشده است.'];
        }

        $template = 'verify';
        $params = [
            'receptor' => $phone,
            'message' => $code,
            'sender' => $this->sender,
            'type' => 'text',
        ];

        $url = $this->baseUrl . '/' . $this->apiKey . '/sms/send.json';
        return $this->request($url, $params);
    }

    public function sendLookup(string $phone, string $token, string $pattern): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'کلید API پیامک تنظیم نشده است.'];
        }

        $params = [
            'receptor' => $phone,
            'token' => $token,
            'template' => $pattern,
            'type' => 'text',
        ];

        $url = $this->baseUrl . '/' . $this->apiKey . '/verify/lookup.json';
        return $this->request($url, $params);
    }

    public function sendBulk(string $phone, string $message): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'کلید API پیامک تنظیم نشده است.'];
        }

        $params = [
            'receptor' => $phone,
            'message' => $message,
            'sender' => $this->sender,
            'type' => 'text',
        ];

        $url = $this->baseUrl . '/' . $this->apiKey . '/sms/send.json';
        return $this->request($url, $params);
    }

    private function request(string $url, array $params): array
    {
        $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mobaro');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => false, 'message' => 'خطا در اتصال به سرویس پیامک: ' . $err];
        }

        $response = json_decode($result, true);

        if (!empty($response['return']['status']) && $response['return']['status'] === 200) {
            return ['status' => true, 'data' => $response['entries'] ?? []];
        }

        $errorCode = $response['return']['status'] ?? -1;
        $errorMessage = $response['return']['message'] ?? 'خطای ناشناخته';
        return ['status' => false, 'message' => $errorMessage, 'code' => $errorCode];
    }

    public function isConfigured(): bool
    {
        return Config::get('sms.enabled', false) && !empty($this->apiKey);
    }
}
