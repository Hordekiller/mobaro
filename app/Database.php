<?php

class Database
{
    private static ?PDO $instance = null;

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
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
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

    public static function cachedFetch(string $key, string $sql, array $params = [], int $ttl = 0, array|string $tags = []): ?array
    {
        $cacheKey = 'db:' . $key;
        $defaultTtl = $ttl ?: Config::get('cache.ttl.default', 3600);
        return Cache::remember($cacheKey, $defaultTtl, function () use ($sql, $params) {
            return self::fetch($sql, $params);
        }, $tags);
    }

    public static function cachedFetchAll(string $key, string $sql, array $params = [], int $ttl = 0, array|string $tags = []): array
    {
        $cacheKey = 'db:' . $key;
        $defaultTtl = $ttl ?: Config::get('cache.ttl.default', 3600);
        return Cache::remember($cacheKey, $defaultTtl, function () use ($sql, $params) {
            return self::fetchAll($sql, $params);
        }, $tags);
    }

    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int) self::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $sets);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
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
