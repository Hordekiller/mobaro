<?php

class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY id DESC");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }

    public static function where(string $column, mixed $value): array
    {
        $allowed = [];
        $stmt = Database::connection()->prepare("SHOW COLUMNS FROM " . static::$table);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $allowed[] = $row['Field'];
        }
        if (!in_array($column, $allowed)) {
            throw new InvalidArgumentException("Column '{$column}' not allowed in query.");
        }
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE {$column} = ? ORDER BY id DESC", [$value]);
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function update(int $id, array $data): int
    {
        return Database::update(static::$table, $data, static::$primaryKey . " = :id", ['id' => $id]);
    }

    public static function delete(int $id): int
    {
        return Database::delete(static::$table, static::$primaryKey . " = ?", [$id]);
    }

    public static function count(): int
    {
        $result = Database::fetch("SELECT COUNT(*) as cnt FROM " . static::$table);
        return (int)($result['cnt'] ?? 0);
    }

    public static function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $total = static::count();
        $items = Database::fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => max(1, ceil($total / $perPage)),
        ];
    }
}
