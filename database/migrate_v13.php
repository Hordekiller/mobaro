<?php
/**
 * Migration v13: Blog Categories
 * Creates: blog_categories table
 * Normalizes: blog_posts.category values
 * Run: php database/migrate_v13.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v13: blog categories...\n";

try {
    $exists = Database::fetch("SHOW TABLES LIKE 'blog_categories'");
    if (!$exists) {
        Database::query("CREATE TABLE blog_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(120) NOT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            post_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_name (name),
            UNIQUE KEY unique_slug (slug),
            INDEX idx_sort (sort_order),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created blog_categories table\n";
    } else {
        echo "  + blog_categories table already exists, skipping\n";
    }

    $normalizations = [
        'میکاپ و آرایش' => 'آرایش',
        'مراقبت مو' => 'مو',
        'پوست و زیبایی' => 'پوست',
    ];

    foreach ($normalizations as $old => $new) {
        $affected = Database::fetch("SELECT COUNT(*) as cnt FROM blog_posts WHERE category = ?", [$old]);
        if ($affected && $affected['cnt'] > 0) {
            Database::query("UPDATE blog_posts SET category = ? WHERE category = ?", [$new, $old]);
            echo "  + Normalized '{$old}' -> '{$new}' (" . $affected['cnt'] . " posts)\n";
        }
    }

    Database::query("UPDATE blog_posts SET category = 'عمومی' WHERE category IS NULL OR TRIM(category) = ''");
    echo "  + Empty categories set to 'عمومی'\n";

    $categories = Database::fetchAll(
        "SELECT category, COUNT(*) as cnt FROM blog_posts GROUP BY category ORDER BY cnt DESC"
    );

    $sort = 0;
    foreach ($categories as $cat) {
        $name = $cat['category'];
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name, '-')));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        if (empty($slug)) {
            $slug = 'cat-' . ($sort + 1);
        }

        $exists = Database::fetch("SELECT id FROM blog_categories WHERE name = ?", [$name]);
        if (!$exists) {
            Database::insert('blog_categories', [
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $sort,
                'is_active' => 1,
                'post_count' => $cat['cnt'],
            ]);
            echo "  + Created category: {$name} ({$cat['cnt']} posts, slug: {$slug})\n";
        }
        $sort++;
    }

    echo "\nMigration v13 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
