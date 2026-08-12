<?php

declare(strict_types=1);

namespace App;

use App\Database;

class RateLimiter
{
    private const DEFAULT_IP = '127.0.0.1';

    // The admin login limit is configurable via the "admin_login_max_attempts"
    // setting (default 5). RateLimiter::MAX_ATTEMPTS was removed in update 44;
    // callers that need a non-default limit must pass it explicitly.
    private static string $table = 'login_attempts';

    public static function init(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success TINYINT(1) DEFAULT 0,
            KEY idx_identifier (identifier),
            KEY idx_ip (ip_address),
            KEY idx_time (attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        Database::query($sql);
    }

    public static function isLocked(string $identifier, int $maxAttempts = 5, int $windowMinutes = 15): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? self::DEFAULT_IP;
        $since = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

        $result = Database::fetch(
            "SELECT COUNT(*) as cnt FROM login_attempts 
             WHERE (identifier = ? OR ip_address = ?) 
             AND attempted_at >= ? AND success = 0",
            [$identifier, $ip, $since]
        );

        return ($result['cnt'] ?? 0) >= $maxAttempts;
    }

    public static function recordAttempt(string $identifier, bool $success = false): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? self::DEFAULT_IP;

        Database::insert(self::$table, [
            'identifier' => $identifier,
            'ip_address' => $ip,
            'success' => $success ? 1 : 0,
        ]);

        if ($success) {
            self::clearAttempts($identifier);
        }
    }

    public static function clearAttempts(string $identifier): void
    {
        Database::query(
            "DELETE FROM login_attempts WHERE identifier = ?",
            [$identifier]
        );
    }

    public static function remainingAttempts(string $identifier, int $maxAttempts = 5, int $windowMinutes = 15): int
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? self::DEFAULT_IP;
        $since = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

        $result = Database::fetch(
            "SELECT COUNT(*) as cnt FROM login_attempts 
             WHERE (identifier = ? OR ip_address = ?) 
             AND attempted_at >= ? AND success = 0",
            [$identifier, $ip, $since]
        );

        $attempts = $result['cnt'] ?? 0;
        return max(0, $maxAttempts - $attempts);
    }

    public static function cleanup(): void
    {
        static $lastCleanup = 0;
        if (time() - $lastCleanup < 3600) {
            return;
        }
        $lastCleanup = time();
        Database::query(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
    }
}
