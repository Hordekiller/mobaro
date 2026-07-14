<?php
/**
 * Migration: Remove UNIQUE constraint from users.phone
 * Allows Google OAuth users (empty phone) to register.
 * Run: php database/migrate_v8.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v8: Remove UNIQUE from users.phone...\n";

try {
    $columns = Database::fetchAll("SHOW COLUMNS FROM users LIKE 'phone'");
    if (!empty($columns)) {
        $key = Database::fetch("SHOW INDEX FROM users WHERE Key_name = 'phone'");
        if ($key) {
            Database::query("ALTER TABLE users DROP INDEX phone");
            echo "  + Removed UNIQUE constraint from users.phone\n";
        } else {
            echo "  + No UNIQUE constraint on users.phone, skipping\n";
        }
    } else {
        echo "  + users.phone column not found, skipping\n";
    }

    echo "\nMigration v8 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
