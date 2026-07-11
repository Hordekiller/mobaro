<?php
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

    echo "=== Product Categories & Brands Migration ===\n\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table product_categories created.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table product_brands created.\n";

    $catCount = $pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
    if ($catCount == 0) {
        $pdo->exec("
            INSERT IGNORE INTO product_categories (name)
            SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category
        ");
        $seeded = $pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
        echo "  [OK] Seeded {$seeded} categories from existing products.\n";
    } else {
        echo "  [SKIP] product_categories already has {$catCount} rows.\n";
    }

    $brandCount = $pdo->query("SELECT COUNT(*) FROM product_brands")->fetchColumn();
    if ($brandCount == 0) {
        $pdo->exec("
            INSERT IGNORE INTO product_brands (name)
            SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand
        ");
        $seeded = $pdo->query("SELECT COUNT(*) FROM product_brands")->fetchColumn();
        echo "  [OK] Seeded {$seeded} brands from existing products.\n";
    } else {
        echo "  [SKIP] product_brands already has {$brandCount} rows.\n";
    }

    echo "\n=== Migration complete! ===\n";

} catch (PDOException $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
