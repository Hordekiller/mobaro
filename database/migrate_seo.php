<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

echo "Running SEO migration: ensuring seo_meta table + default rows...\n";

try {
    $tables = Database::fetchAll("SHOW TABLES");
    $tableNames = array_column($tables, array_keys($tables[0])[0]);

    if (in_array('seo_meta', $tableNames, true)) {
        echo "  + seo_meta table exists\n";

        $columns = Database::fetchAll("SHOW COLUMNS FROM seo_meta");
        $existing = array_column($columns, 'Field');
        if (!in_array('robots', $existing, true)) {
            Database::query("ALTER TABLE seo_meta ADD COLUMN robots VARCHAR(255) DEFAULT NULL AFTER og_image");
            echo "  + Added robots column to seo_meta\n";
        } else {
            echo "  + robots column already exists\n";
        }
    } else {
        Database::query("
            CREATE TABLE seo_meta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_slug VARCHAR(100) NOT NULL UNIQUE,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description TEXT DEFAULT NULL,
                canonical_url VARCHAR(500) DEFAULT NULL,
                og_title VARCHAR(255) DEFAULT NULL,
                og_description TEXT DEFAULT NULL,
                og_image VARCHAR(500) DEFAULT NULL,
                robots VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "  + Created seo_meta table\n";
    }

    $existingSlugs = array_column(Database::fetchAll("SELECT page_slug FROM seo_meta"), 'page_slug');
    $defaultSlugs = ['home', 'shop', 'blog', 'contact', 'about', 'academy'];
    foreach ($defaultSlugs as $slug) {
        if (!in_array($slug, $existingSlugs, true)) {
            Database::query("INSERT INTO seo_meta (page_slug) VALUES (?)", [$slug]);
            echo "  + Added default seo_meta row for '{$slug}'\n";
        }
    }

    echo "SEO migration completed successfully.\n";
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
