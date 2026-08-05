<?php

/**
 * Migration v16: Blog SEO columns on blog_posts
 * Adds (idempotently) the SEO/OG/image_alt columns that the admin blog
 * form writes, so saving a blog post no longer fails with
 * "Unknown column 'robots'" on databases created from older schemas.
 *
 * Run: php database/migrate_v16.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

echo "Running migration v16: blog_posts SEO columns...\n";

try {
    $exists = Database::fetch("SHOW TABLES LIKE 'blog_posts'");
    if (!$exists) {
        echo "  ! blog_posts table not found, skipping\n";
        exit(0);
    }

    $columns = Database::fetchAll("SHOW COLUMNS FROM blog_posts");
    $existing = array_column($columns, 'Field');

    $additions = [
        'image_alt'       => 'ADD COLUMN image_alt VARCHAR(500) DEFAULT NULL AFTER image',
        'meta_title'      => 'ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL AFTER image_alt',
        'meta_description' => 'ADD COLUMN meta_description TEXT DEFAULT NULL AFTER meta_title',
        'canonical_url'   => 'ADD COLUMN canonical_url VARCHAR(500) DEFAULT NULL AFTER meta_description',
        'og_title'        => 'ADD COLUMN og_title VARCHAR(255) DEFAULT NULL AFTER canonical_url',
        'og_description'  => 'ADD COLUMN og_description TEXT DEFAULT NULL AFTER og_title',
        'og_image'        => 'ADD COLUMN og_image VARCHAR(500) DEFAULT NULL AFTER og_description',
        'robots'          => 'ADD COLUMN robots VARCHAR(255) DEFAULT NULL AFTER og_image',
    ];

    foreach ($additions as $column => $clause) {
        if (in_array($column, $existing, true)) {
            echo "  + {$column} already exists, skipping\n";
            continue;
        }
        Database::query("ALTER TABLE blog_posts {$clause}");
        echo "  + Added {$column} to blog_posts\n";
    }

    echo "Migration v16 completed successfully.\n";
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
