<?php
/**
 * Migration: Create verification_codes table + add phone_verified to users.
 * Run: php database/migrate_v11.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v11: verification_codes + phone_verified...\n";

try {
    $exists = Database::fetch("SHOW TABLES LIKE 'verification_codes'");
    if (!$exists) {
        Database::query("CREATE TABLE verification_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            code VARCHAR(10) NOT NULL,
            purpose ENUM('register', 'login', 'reset_password') NOT NULL DEFAULT 'register',
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone_purpose (phone, purpose),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created verification_codes table\n";
    } else {
        echo "  + verification_codes table already exists, skipping\n";
    }

    $col = Database::fetch("SHOW COLUMNS FROM users LIKE 'phone_verified'");
    if (!$col) {
        Database::query("ALTER TABLE users ADD COLUMN phone_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        echo "  + Added phone_verified column to users\n";
    } else {
        echo "  + phone_verified column already exists, skipping\n";
    }

    echo "\nMigration v11 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
