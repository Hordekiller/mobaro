<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'mobaro';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "=== Artist–Services Pivot Migration ===\n\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS artist_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            service_id INT NOT NULL,
            UNIQUE KEY uq_artist_service (artist_id, service_id),
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table artist_services created.\n";

    $existing = $pdo->query("SELECT COUNT(*) FROM artist_services")->fetchColumn();
    if ($existing == 0) {
        $pdo->exec("
            INSERT INTO artist_services (artist_id, service_id)
            SELECT artist_id, id FROM services WHERE artist_id IS NOT NULL
        ");
        $migrated = $pdo->query("SELECT COUNT(*) FROM artist_services")->fetchColumn();
        echo "  [OK] Migrated {$migrated} existing assignments from services.artist_id.\n";
    } else {
        echo "  [SKIP] artist_services already has {$existing} rows.\n";
    }

    echo "\n=== Migration complete! ===\n";

} catch (PDOException $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
