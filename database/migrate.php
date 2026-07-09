<?php
/**
 * Mobaro Database Migration Script
 * Run: php database/migrate.php
 */

echo "=== Mobaro Database Migration ===\n\n";

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS mobaro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE mobaro");
    echo "[OK] Database 'mobaro' ready\n";

    // Import schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        fn($s) => !empty($s) && !str_starts_with($s, '--') && !str_starts_with($s, 'CREATE DATABASE')
    );
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }
    echo "[OK] Schema imported\n";

    // Import seed data
    $seed = file_get_contents(__DIR__ . '/seed.sql');
    $seedStatements = array_filter(
        array_map('trim', explode(';', $seed)),
        fn($s) => !empty($s) && !str_starts_with($s, '--') && !str_starts_with($s, 'USE')
    );
    foreach ($seedStatements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }
    echo "[OK] Seed data imported\n";

    echo "\n=== Migration Complete! ===\n";
    echo "Admin login: 09120000000 / admin123\n";
    echo "User login:  09123456789 / user123\n";

} catch (PDOException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
