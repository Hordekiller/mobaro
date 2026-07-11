<?php
/**
 * Migration: Add product_images table and video columns to products
 */

require_once __DIR__ . '/../app/bootstrap.php';

try {
    // 1. Create product_images table
    Database::query("
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image VARCHAR(255) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Table product_images created\n";

    // 2. Add video columns to products
    $columns = Database::fetch("SHOW COLUMNS FROM products WHERE Field = 'video_url'");
    if (!$columns) {
        Database::query("ALTER TABLE products ADD COLUMN video_url VARCHAR(500) DEFAULT NULL AFTER is_active");
        echo "✓ Column video_url added to products\n";
    } else {
        echo "• Column video_url already exists\n";
    }

    $columns = Database::fetch("SHOW COLUMNS FROM products WHERE Field = 'video_type'");
    if (!$columns) {
        Database::query("ALTER TABLE products ADD COLUMN video_type ENUM('upload', 'youtube', 'aparat') DEFAULT 'upload' AFTER video_url");
        echo "✓ Column video_type added to products\n";
    } else {
        echo "• Column video_type already exists\n";
    }

    echo "\n✅ Migration completed successfully.\n";
} catch (Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
