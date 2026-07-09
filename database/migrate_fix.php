<?php
/**
 * Mobaro Database Fix Migration
 * Adds missing columns and tables to bring the DB in sync with schema.sql
 * Run: php database/migrate_fix.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'mobaro';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "=== Mobaro Database Fix Migration ===\n\n";

    $count = 0;

    // Helper to check if a column exists
    $columnExists = function (string $table, string $column) use ($pdo, $name): bool {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
        $stmt->execute([$name, $table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    };

    // Helper: add column if missing
    $addColumn = function (string $table, string $column, string $definition) use ($pdo, $columnExists, &$count): void {
        if (!$columnExists($table, $column)) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$definition}");
            echo "  [ADDED] {$table}.{$column}\n";
            $count++;
        } else {
            echo "  [OK]    {$table}.{$column} already exists\n";
        }
    };

    // Helper: create table if missing
    $createTable = function (string $sql) use ($pdo, &$count): void {
        try {
            $pdo->exec($sql);
            preg_match('/CREATE TABLE IF NOT EXISTS (\S+)/i', $sql, $m);
            $tableName = $m[1] ?? 'unknown';
            echo "  [CREATED] Table {$tableName}\n";
            $count++;
        } catch (PDOException $e) {
            if ($e->getCode() == '42S21') {
                // Table already exists, ok
            } else {
                throw $e;
            }
        }
    };

    // ── 1. artists: instagram, working_hours ──
    echo "\n--- artists ---\n";
    $addColumn('artists', 'instagram', "instagram VARCHAR(255) DEFAULT '#'");
    $addColumn('artists', 'working_hours', "working_hours VARCHAR(100) DEFAULT '۹ صبح - ۸ شب'");

    // ── 2. products: stock, rating ──
    echo "\n--- products ---\n";
    $addColumn('products', 'stock', 'stock INT NOT NULL DEFAULT 10');
    $addColumn('products', 'rating', 'rating DECIMAL(2,1) DEFAULT 4.5');

    // ── 3. testimonials: avatar ──
    echo "\n--- testimonials ---\n";
    $addColumn('testimonials', 'avatar', 'avatar VARCHAR(255) DEFAULT NULL');

    // ── 4. tutorials: video_url (actual DB has `url`, code expects `video_url`) ──
    echo "\n--- tutorials ---\n";
    $addColumn('tutorials', 'video_url', "video_url VARCHAR(255) DEFAULT ''");

    // ── 5. appointments: notes ──
    echo "\n--- appointments ---\n";
    $addColumn('appointments', 'notes', 'notes TEXT DEFAULT NULL');

    // ── 6. courses: description ──
    echo "\n--- courses ---\n";
    $addColumn('courses', 'description', 'description TEXT DEFAULT NULL');

    // ── 7. reviews table ──
    echo "\n--- reviews table ---\n";
    $createTable("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_name VARCHAR(255) NOT NULL,
        rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
        text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ── 8. favorite_models table ──
    echo "\n--- favorite_models table ---\n";
    $createTable("CREATE TABLE IF NOT EXISTS favorite_models (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        model_title VARCHAR(200) NOT NULL,
        model_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ── 9. services: ensure artist_id column exists ──
    echo "\n--- services ---\n";
    $addColumn('services', 'artist_id', 'artist_id INT DEFAULT NULL');

    // ── 10. orders: ensure address column exists ──
    echo "\n--- orders ---\n";
    $addColumn('orders', 'address', 'address TEXT DEFAULT NULL');

    // ── 11. settings: ensure updated_at exists ──
    echo "\n--- settings ---\n";
    $addColumn('settings', 'updated_at', "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

    echo "\n=== Migration complete! {$count} changes applied. ===\n";

} catch (PDOException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
