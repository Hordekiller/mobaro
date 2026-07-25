<?php

class GoogleAuth
{
    private static string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private static string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private static string $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

    public static function getClientId(): string
    {
        return getenv('GOOGLE_CLIENT_ID') ?: '';
    }

    public static function getClientSecret(): string
    {
        return getenv('GOOGLE_CLIENT_SECRET') ?: '';
    }

    public static function getRedirectUri(): string
    {
        return getenv('GOOGLE_REDIRECT_URI') ?: (Config::get('app.url') . '/auth/google/callback');
    }

    public static function isConfigured(): bool
    {
        return !empty(self::getClientId()) && !empty(self::getClientSecret());
    }

    public static function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => self::getClientId(),
            'redirect_uri' => self::getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);
        return self::authUrl . '?' . $params;
    }

    public static function exchangeCode(string $code): ?array
    {
        $response = self::post(self::$tokenUrl, [
            'code' => $code,
            'client_id' => self::getClientId(),
            'client_secret' => self::getClientSecret(),
            'redirect_uri' => self::getRedirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$response || isset($response['error'])) {
            return null;
        }

        return $response;
    }

    public static function getUserInfo(string $accessToken): ?array
    {
        $ch = curl_init(self::$userInfoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode($body, true);
        if (!$data || !isset($data['id'])) {
            return null;
        }

        return [
            'google_id' => $data['id'],
            'email' => $data['email'] ?? '',
            'name' => $data['given_name'] ?? $data['name'] ?? '',
            'family' => $data['family_name'] ?? '',
            'avatar' => $data['picture'] ?? '',
        ];
    }

    private static function post(string $url, array $data): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return json_decode($body, true);
    }

    public static function findOrCreateUser(array $googleUser): array
    {
        $existing = Database::fetch(
            "SELECT * FROM users WHERE google_id = ? OR email = ?",
            [$googleUser['google_id'], $googleUser['email']]
        );

        if ($existing) {
            Database::update('users', [
                'google_id' => $googleUser['google_id'],
                'google_avatar' => $googleUser['avatar'],
            ], 'id = :id', ['id' => $existing['id']]);

            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$existing['id']]);
            return $user;
        }

        $phone = '';
        $userId = Database::insert('users', [
            'name' => $googleUser['name'] ?: 'کاربر',
            'family' => $googleUser['family'] ?: 'گوگل',
            'phone' => $phone,
            'email' => $googleUser['email'],
            'password' => Auth::hash(bin2hex(random_bytes(16))),
            'google_id' => $googleUser['google_id'],
            'google_avatar' => $googleUser['avatar'],
            'role' => 'user',
            'level' => 'bronze',
            'points' => 50,
            'wallet' => 0,
        ]);

        Database::insert('transactions', [
            'user_id' => $userId,
            'type' => 'points_earn',
            'amount' => 50,
            'description' => 'امتیاز ثبت‌نام با گوگل',
        ]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        return $user;
    }
}
