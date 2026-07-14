<?php
/**
 * Migration: Change blog_comments.is_approved DEFAULT from 1 to 0.
 * New comments now require admin approval.
 * Run: php database/migrate_v9.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v9: Change blog_comments.is_approved DEFAULT to 0...\n";

try {
    $table = Database::fetch("SHOW TABLES LIKE 'blog_comments'");
    if ($table) {
        Database::query("ALTER TABLE blog_comments ALTER COLUMN is_approved SET DEFAULT 0");
        echo "  + Changed is_approved DEFAULT from 1 to 0\n";
    } else {
        echo "  + blog_comments table not found, skipping\n";
    }

    echo "\nMigration v9 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
