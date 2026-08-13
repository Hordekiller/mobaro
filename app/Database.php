<?php

declare(strict_types=1);

namespace App;

use App\Config;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class Database
{
    private static ?PDO $instance = null;

    private static array $allowedTables = [
        'users', 'artists', 'services', 'artist_services', 'hair_lengths',
        'appointments', 'service_hair_prices', 'products', 'product_images',
        'orders', 'order_items', 'courses', 'course_lessons_completed',
        'course_enrollments', 'transactions', 'testimonials', 'wishlist',
        'login_attempts', 'favorite_models', 'addresses', 'newsletter',
        'settings', 'blog_comments', 'reviews', 'product_categories',
        'product_brands', 'hair_models', 'tutorials', 'media',
        'blog_posts', 'contact_messages', 'coupons',
        'sms_logs', 'sms_templates', 'sms_credits', 'verification_codes',
        'blog_categories', 'payment_logs', 'faqs',
    ];

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $host = Config::get('db.host');
            $name = Config::get('db.name');
            $user = Config::get('db.user');
            $pass = Config::get('db.pass');
            $charset = Config::get('db.charset');

            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$instance->exec("SET time_zone = '+03:30'");
        }

        return self::$instance;
    }

    /**
     * Execute a prepared statement. All SQL must use '?' placeholders for user data.
     *
     * @param string $sql  SQL with '?' placeholders, NOT user-supplied values
     * @param array  $params  Bound parameters matching the '?' placeholders
     */
    /** @phpstan-ignore-line — callers must pass prepared SQL with '?' placeholders */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        if ($params !== [] && !str_contains($sql, '?') && !str_contains($sql, ':')) {
            throw new InvalidArgumentException('Query with parameters must use ? or :named placeholders');
        }
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    private static function validateTable(string $table): void
    {
        if (!in_array($table, self::$allowedTables, true)) {
            throw new InvalidArgumentException("Invalid table name: {$table}");
        }
    }

    private static function validateColumns(array $columns): void
    {
        foreach ($columns as $col) {
            if (!preg_match('/^[a-zA-Z_]\w*$/', $col)) {
                throw new InvalidArgumentException("Invalid column name: {$col}");
            }
        }
    }

    public static function insert(string $table, array $data): int
    {
        self::validateTable($table);
        $keys = array_keys($data);
        self::validateColumns($keys);

        $columns = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int) self::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        self::validateTable($table);
        $keys = array_keys($data);
        self::validateColumns($keys);
        self::validateWhere($where);

        $sets = [];
        foreach ($keys as $key) {
            $sets[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $sets);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $stmt = self::query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        self::validateTable($table);
        self::validateWhere($where);
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    private static function validateWhere(string $where): void
    {
        $clean = preg_replace('/\s+/', ' ', trim($where));

        $parts = preg_split('/\s+(?:AND|OR)\s+/i', $clean);
        if ($parts === false || empty($parts)) {
            throw new InvalidArgumentException("Invalid WHERE clause: could not parse");
        }

        $colPattern = '[a-zA-Z_][a-zA-Z0-9_.]*';
        $ops = '=|!=|<>|>=|<=|>|<|LIKE|IN';
        $safePattern = "/^{$colPattern}\s+(?:{$ops})\s+(?:\?|:[a-zA-Z_][a-zA-Z0-9_]*)$/i";

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                throw new InvalidArgumentException("Invalid WHERE clause: empty condition");
            }
            if (!preg_match($safePattern, $part)) {
                throw new InvalidArgumentException("Invalid WHERE clause: disallowed pattern in '{$part}'");
            }
        }
    }

    public static function escapeLike(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }

    public static function beginTransaction(): void
    {
        self::connection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connection()->commit();
    }

    public static function rollback(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }
}
