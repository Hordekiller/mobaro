<?php

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v6: Dashboard reviews and blog comments...\n";

$reviewColumns = array_column(Database::fetchAll("SHOW COLUMNS FROM reviews"), 'Field');
if (!in_array('user_id', $reviewColumns, true)) {
    Database::query("ALTER TABLE reviews ADD COLUMN user_id INT DEFAULT NULL AFTER product_id");
    Database::query("CREATE INDEX idx_reviews_user ON reviews (user_id)");
    echo "  + reviews.user_id\n";
}

Database::query(
    "UPDATE reviews r
     JOIN users u
       ON r.user_name COLLATE utf8mb4_unicode_ci =
          TRIM(CONCAT(u.name, ' ', u.family)) COLLATE utf8mb4_unicode_ci
     SET r.user_id = u.id
     WHERE r.user_id IS NULL"
);
echo "  + linked legacy reviews to users\n";

Database::query(
    "CREATE TABLE IF NOT EXISTS blog_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        name VARCHAR(200) NOT NULL DEFAULT '',
        email VARCHAR(200) DEFAULT '',
        text TEXT NOT NULL,
        is_approved TINYINT(1) DEFAULT 1,
        likes INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_blog_comments_post (post_id),
        INDEX idx_blog_comments_user (user_id),
        INDEX idx_blog_comments_approved (is_approved),
        CONSTRAINT fk_blog_comments_post
            FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_blog_comments_user
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);
echo "  + blog_comments table\n";

echo "\nMigration v6 complete!\n";
