<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use Throwable;

class PaymentLogger
{
    private static ?bool $tableExists = null;

    public static function log(array $data): bool
    {
        try {
            if (!self::tableExists()) {
                return false;
            }

            $row = [
                'user_id' => $data['user_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'gateway' => $data['gateway'] ?? 'zarinpal',
                'action' => $data['action'] ?? 'request',
                'amount' => $data['amount'] ?? 0,
                'authority' => $data['authority'] ?? null,
                'ref_id' => $data['ref_id'] ?? null,
                'status' => $data['status'] ?? null,
                'request_data' => self::encode($data['request_data'] ?? null),
                'response_data' => self::encode($data['response_data'] ?? null),
                'ip' => self::clientIp(),
            ];

            Database::insert('payment_logs', $row);
            return true;
        } catch (Throwable $e) {
            error_log('PaymentLogger::log failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function findPendingRequest(int $userId, string $authority): ?array
    {
        try {
            if (!self::tableExists()) {
                return null;
            }

            return Database::fetch(
                "SELECT * FROM payment_logs
                 WHERE user_id = ? AND authority = ? AND action = 'request' AND status = 'sent'
                 ORDER BY id DESC LIMIT 1",
                [$userId, $authority]
            );
        } catch (Throwable $e) {
            error_log('PaymentLogger::findPendingRequest failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function tableExists(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = (bool) Database::fetch("SHOW TABLES LIKE 'payment_logs'");
        }
        return self::$tableExists;
    }

    private static function encode(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    private static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim((string) $_SERVER[$key]);
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ip = explode(',', $ip)[0];
                }
                return substr($ip, 0, 45);
            }
        }
        return '';
    }
}
