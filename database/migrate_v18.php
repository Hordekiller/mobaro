<?php

/**
 * Migration v18: Orders authority/ref_id columns + ensure payment_logs table.
 * Run: php database/migrate_v18.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

use App\Config;
use App\Database;

echo "Running migration v18: Orders payment tracking columns...\n";

try {
    // 1. orders.authority (ZarinPal Authority token)
    $cols = Database::fetchAll("SHOW COLUMNS FROM orders");
    $colNames = array_column($cols, 'Field');

    if (!in_array('authority', $colNames)) {
        Database::query("ALTER TABLE orders ADD COLUMN authority VARCHAR(255) DEFAULT NULL AFTER payment_id");
        echo "  + orders.authority\n";
    } else {
        echo "  + orders.authority (already exists)\n";
    }

    if (!in_array('ref_id', $colNames)) {
        Database::query("ALTER TABLE orders ADD COLUMN ref_id VARCHAR(255) DEFAULT NULL AFTER authority");
        echo "  + orders.ref_id\n";
    } else {
        echo "  + orders.ref_id (already exists)\n";
    }

    // 2. Ensure payment_logs audit table exists
    $exists = Database::fetch("SHOW TABLES LIKE 'payment_logs'");
    if (!$exists) {
        Database::query("CREATE TABLE payment_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            order_id INT DEFAULT NULL,
            gateway VARCHAR(50) NOT NULL DEFAULT 'zarinpal',
            action VARCHAR(50) NOT NULL DEFAULT 'verify',
            amount DECIMAL(15,0) DEFAULT 0,
            authority VARCHAR(255) DEFAULT NULL,
            ref_id VARCHAR(255) DEFAULT NULL,
            status VARCHAR(50) DEFAULT NULL,
            request_data TEXT DEFAULT NULL,
            response_data TEXT DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_order (order_id),
            INDEX idx_authority (authority)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created payment_logs table\n";
    } else {
        echo "  + payment_logs table already exists, skipping\n";
    }

    echo "\nMigration v18 complete!\n";
} catch (Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
