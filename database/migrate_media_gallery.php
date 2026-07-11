<?php
/**
 * Migration: Create media table + seed existing images/videos
 */

require_once __DIR__ . '/../app/bootstrap.php';

try {
    // 1. Create media table
    Database::query("
        CREATE TABLE IF NOT EXISTS media (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filepath VARCHAR(500) NOT NULL,
            original_name VARCHAR(255) NOT NULL DEFAULT '',
            type ENUM('image', 'video') NOT NULL DEFAULT 'image',
            mime_type VARCHAR(100) DEFAULT '',
            size INT DEFAULT 0,
            alt_text VARCHAR(500) DEFAULT '',
            source_type VARCHAR(50) DEFAULT '',
            source_id INT DEFAULT NULL,
            uploaded_by INT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_source (source_type, source_id),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Table media created\n";

    // 2. Seed product main images
    $products = Database::fetchAll(
        "SELECT id, image, name FROM products WHERE image IS NOT NULL AND image != ''"
    );
    $count = 0;
    foreach ($products as $p) {
        $filepath = 'assets/images/' . $p['image'];
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if (!$existing) {
            $fullPath = __DIR__ . '/../public/' . $filepath;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : 'image/jpeg';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => $p['image'],
                'type' => 'image',
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => $p['name'],
                'source_type' => 'product_image',
                'source_id' => $p['id'],
            ]);
            $count++;
        }
    }
    echo "✓ Seeded {$count} product main images\n";

    // 3. Seed product gallery images
    $galleryImages = Database::fetchAll(
        "SELECT pi.id AS gid, pi.image, p.name AS pname, p.id AS pid
         FROM product_images pi
         JOIN products p ON p.id = pi.product_id
         WHERE pi.image IS NOT NULL AND pi.image != ''"
    );
    $count = 0;
    foreach ($galleryImages as $gi) {
        $filepath = 'assets/images/' . $gi['image'];
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if (!$existing) {
            $fullPath = __DIR__ . '/../public/' . $filepath;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : 'image/jpeg';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => $gi['image'],
                'type' => 'image',
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => $gi['pname'] . ' (گالری)',
                'source_type' => 'product_gallery',
                'source_id' => $gi['pid'],
            ]);
            $count++;
        }
    }
    echo "✓ Seeded {$count} product gallery images\n";

    // 4. Seed product videos (local uploads only)
    $productVids = Database::fetchAll(
        "SELECT id, video_url, name FROM products
         WHERE video_url IS NOT NULL AND video_url != ''
         AND video_type = 'upload'
         AND video_url LIKE '/assets/uploads/videos/%'"
    );
    $count = 0;
    foreach ($productVids as $pv) {
        $filepath = ltrim($pv['video_url'], '/');
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if (!$existing) {
            $fullPath = __DIR__ . '/../public/' . $filepath;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : 'video/mp4';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => basename($pv['video_url']),
                'type' => 'video',
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => $pv['name'] . ' (ویدیو)',
                'source_type' => 'product_video',
                'source_id' => $pv['id'],
            ]);
            $count++;
        }
    }
    echo "✓ Seeded {$count} product videos\n";

    // 5. Seed course videos (local uploads only)
    $courseVids = Database::fetchAll(
        "SELECT id, video_url, title FROM courses
         WHERE video_url IS NOT NULL AND video_url != ''
         AND video_type = 'upload'
         AND video_url LIKE '/assets/uploads/videos/%'"
    );
    $count = 0;
    foreach ($courseVids as $cv) {
        $filepath = ltrim($cv['video_url'], '/');
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if (!$existing) {
            $fullPath = __DIR__ . '/../public/' . $filepath;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : 'video/mp4';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => basename($cv['video_url']),
                'type' => 'video',
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => $cv['title'] . ' (ویدیو دوره)',
                'source_type' => 'course_video',
                'source_id' => $cv['id'],
            ]);
            $count++;
        }
    }
    echo "✓ Seeded {$count} course videos\n";

    // 6. Seed tutorial videos (local uploads only)
    $tutorialCols = array_column(Database::fetchAll("SHOW COLUMNS FROM tutorials"), 'Field');
    $tutorialWhere = "WHERE video_url IS NOT NULL AND video_url != '' AND video_url LIKE '/assets/uploads/videos/%'";
    if (in_array('video_type', $tutorialCols)) {
        $tutorialWhere .= " AND video_type = 'upload'";
    }
    $tutorialVids = Database::fetchAll("SELECT id, video_url, title FROM tutorials {$tutorialWhere}");
    $count = 0;
    foreach ($tutorialVids as $tv) {
        $filepath = ltrim($tv['video_url'], '/');
        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if (!$existing) {
            $fullPath = __DIR__ . '/../public/' . $filepath;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : 'video/mp4';
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            Database::insert('media', [
                'filepath' => $filepath,
                'original_name' => basename($tv['video_url']),
                'type' => 'video',
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => $tv['title'] . ' (ویدیو آموزش)',
                'source_type' => 'tutorial_video',
                'source_id' => $tv['id'],
            ]);
            $count++;
        }
    }
    echo "✓ Seeded {$count} tutorial videos\n";

    echo "\n✅ Media gallery migration completed successfully.\n";
    echo "   Total records in media: " . (Database::fetch("SELECT COUNT(*) as cnt FROM media")['cnt'] ?? 0) . "\n";
} catch (Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
