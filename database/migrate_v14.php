<?php

/**
 * Migration v14: Blog SEO — Add robots column
 * Adds: robots VARCHAR(255) DEFAULT NULL to blog_posts
 * Run: php database/migrate_v14.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

echo "Running migration v14: blog robots column...\n";

try {
    $columns = Database::fetchAll("SHOW COLUMNS FROM blog_posts");
    $existing = array_column($columns, 'Field');
    if (in_array('robots', $existing, true)) {
        echo "  + robots column already exists, skipping\n";
    } else {
        Database::query("ALTER TABLE blog_posts ADD COLUMN robots VARCHAR(255) DEFAULT NULL AFTER image_alt");
        echo "  + Added robots column to blog_posts\n";
    }

    echo "Migration v14 completed successfully.\n";
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
