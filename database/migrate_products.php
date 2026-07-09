<?php
require_once __DIR__ . '/../app/bootstrap.php';

try {
    $db = Database::connection();

    $columns = $db->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $colNames = array_map('strtolower', $columns);

    $newColumns = [
        'brand' => "ALTER TABLE products ADD COLUMN brand VARCHAR(100) DEFAULT ''",
        'old_price' => 'ALTER TABLE products ADD COLUMN old_price DECIMAL(15,0) DEFAULT 0',
        'is_new' => 'ALTER TABLE products ADD COLUMN is_new TINYINT(1) DEFAULT 0',
        'is_sale' => 'ALTER TABLE products ADD COLUMN is_sale TINYINT(1) DEFAULT 0',
        'reviews' => 'ALTER TABLE products ADD COLUMN reviews INT DEFAULT 0',
    ];

    foreach ($newColumns as $name => $sql) {
        if (!in_array($name, $colNames)) {
            $db->exec($sql);
            echo "Added column: {$name}\n";
        }
    }

    $db->exec("UPDATE products SET brand = 'لورآل', old_price = 295000, is_sale = 1, reviews = 124 WHERE name LIKE '%شامپو%'");
    $db->exec("UPDATE products SET brand = 'لورآل', old_price = 380000, is_sale = 1, reviews = 67 WHERE name LIKE '%ماسک%'");
    $db->exec("UPDATE products SET brand = 'میبلین', reviews = 89, is_new = 1 WHERE name LIKE '%رژ لب%'");
    $db->exec("UPDATE products SET brand = 'لاروش پوزای', old_price = 220000, is_sale = 1, reviews = 234 WHERE name LIKE '%کرم%'");
    $db->exec("UPDATE products SET brand = 'فیلیپس', reviews = 45 WHERE name LIKE '%سشوار%'");

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
