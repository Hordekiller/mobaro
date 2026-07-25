<?php
/**
 * Migration: Create payment_logs table for payment audit trail.
 * Run: php database/migrate_v10.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v10: Create payment_logs table...\n";

try {
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

    echo "\nMigration v10 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
