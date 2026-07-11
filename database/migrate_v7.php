<?php

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
}

require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v7: tracking_code unique, product stock column...\n";

$indexes = Database::fetchAll("SHOW INDEX FROM orders");
$indexNames = array_column($indexes, 'Key_name');
if (!in_array('idx_tracking_code_unique', $indexNames, true)) {
    Database::query("CREATE UNIQUE INDEX idx_tracking_code_unique ON orders (tracking_code)");
    echo "  + idx_tracking_code_unique on orders.tracking_code\n";
} else {
    echo "  ~ idx_tracking_code_unique already exists\n";
}

$orderCols = array_column(Database::fetchAll("SHOW COLUMNS FROM orders"), 'Field');
if (in_array('payment_status', $orderCols, true)) {
    Database::query("ALTER TABLE orders MODIFY payment_status VARCHAR(50) DEFAULT 'pending'");
    echo "  + orders.payment_status default fixed\n";
}

echo "\nMigration v7 complete!\n";
