<?php
// Migration: Hair Length Pricing System
// Adds tables for hair length options and dynamic pricing based on hair length

// Direct PDO connection to avoid bootstrap issues
$host = 'localhost';
$dbname = 'mobaro';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function executeQuery($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetch($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchAll($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insert($pdo, $table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    executeQuery($pdo, $sql, $data);
    return $pdo->lastInsertId();
}

class HairLengthPricingMigration {
    public static function up($pdo)
    {
        // Create hair_lengths table
        executeQuery($pdo, "CREATE TABLE IF NOT EXISTS hair_lengths (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            min_cm INT DEFAULT 0,
            max_cm INT DEFAULT 0,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Create service_hair_prices table
        executeQuery($pdo, "CREATE TABLE IF NOT EXISTS service_hair_prices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_id INT NOT NULL,
            hair_length_id INT NOT NULL,
            price DECIMAL(15,0) NOT NULL DEFAULT 0,
            duration_modifier DECIMAL(3,1) DEFAULT 1.0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            FOREIGN KEY (hair_length_id) REFERENCES hair_lengths(id) ON DELETE CASCADE,
            UNIQUE KEY uk_service_hair_length (service_id, hair_length_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Add hair_length_id to appointments table
        $columnExists = fetch($pdo,
            "SELECT COUNT(*) as count FROM information_schema.columns 
             WHERE table_schema = DATABASE() AND table_name = 'appointments' AND column_name = 'hair_length_id'"
        );
        
        if ($columnExists['count'] == 0) {
            executeQuery($pdo, "ALTER TABLE appointments ADD COLUMN hair_length_id INT DEFAULT NULL AFTER artist_id");
            executeQuery($pdo, "ALTER TABLE appointments ADD FOREIGN KEY (hair_length_id) REFERENCES hair_lengths(id) ON DELETE SET NULL");
        }

        // Seed default hair lengths
        $lengths = [
            ['title' => 'کوتاه (زیر شانه)', 'min_cm' => 0, 'max_cm' => 30, 'sort_order' => 1],
            ['title' => 'متوسط (تا شانه)', 'min_cm' => 30, 'max_cm' => 50, 'sort_order' => 2],
            ['title' => 'بلند (زیر کمر)', 'min_cm' => 50, 'max_cm' => 80, 'sort_order' => 3],
            ['title' => 'خیلی بلند (بالای کمر)', 'min_cm' => 80, 'max_cm' => 150, 'sort_order' => 4]
        ];
        
        foreach ($lengths as $length) {
            $exists = fetch($pdo,
                "SELECT id FROM hair_lengths WHERE title = ?", [$length['title']]
            );
            if (!$exists) {
                insert($pdo, 'hair_lengths', $length);
            }
        }

        // Seed default prices for hair coloring and keratin services
        $coloringService = fetch($pdo, "SELECT id FROM services WHERE title = 'رنگ مو' AND category = 'رنگ'");
        $keratinService = fetch($pdo, "SELECT id FROM services WHERE title = 'کراتینه مو' AND category = 'کراتینه'");
        
        $hairLengths = fetchAll($pdo, "SELECT id FROM hair_lengths ORDER BY sort_order");
        
        if ($coloringService && $hairLengths) {
            $basePrice = 550000;
            foreach ($hairLengths as $index => $length) {
                $price = $basePrice + ($index * 150000); // Increase by 150K for each level
                $exists = fetch($pdo,
                    "SELECT id FROM service_hair_prices WHERE service_id = ? AND hair_length_id = ?",
                    [$coloringService['id'], $length['id']]
                );
                if (!$exists) {
                    insert($pdo, 'service_hair_prices', [
                        'service_id' => $coloringService['id'],
                        'hair_length_id' => $length['id'],
                        'price' => $price,
                        'duration_modifier' => 1.0 + ($index * 0.2) // 1.0, 1.2, 1.4, 1.6
                    ]);
                }
            }
        }
        
        if ($keratinService && $hairLengths) {
            $basePrice = 800000;
            foreach ($hairLengths as $index => $length) {
                $price = $basePrice + ($index * 200000); // Increase by 200K for each level
                $exists = fetch($pdo,
                    "SELECT id FROM service_hair_prices WHERE service_id = ? AND hair_length_id = ?",
                    [$keratinService['id'], $length['id']]
                );
                if (!$exists) {
                    insert($pdo, 'service_hair_prices', [
                        'service_id' => $keratinService['id'],
                        'hair_length_id' => $length['id'],
                        'price' => $price,
                        'duration_modifier' => 1.0 + ($index * 0.25) // 1.0, 1.25, 1.5, 1.75
                    ]);
                }
            }
        }

        echo "✅ Hair length pricing migration completed successfully.\n";
    }
    
    public static function down($pdo)
    {
        // Drop tables in reverse order
        executeQuery($pdo, "ALTER TABLE appointments DROP FOREIGN KEY appointments_ibfk_hair_length");
        executeQuery($pdo, "ALTER TABLE appointments DROP COLUMN hair_length_id");
        
        executeQuery($pdo, "DROP TABLE IF EXISTS service_hair_prices");
        executeQuery($pdo, "DROP TABLE IF EXISTS hair_lengths");
        
        echo "⚠️ Hair length pricing tables dropped.\n";
    }
}

// Run migration
if (php_sapi_name() === 'cli') {
    if ($argc > 1 && $argv[1] === 'down') {
        HairLengthPricingMigration::down($pdo);
    } else {
        HairLengthPricingMigration::up($pdo);
    }
} else {
    // Run up by default for web access
    HairLengthPricingMigration::up($pdo);
}