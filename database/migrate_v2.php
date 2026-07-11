<?php
/**
 * Mobaro v2 Migration
 * Adds video_type to courses, captcha settings, and missing config
 * Run: php database/migrate_v2.php
 */

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

    echo "=== Mobaro v2 Migration ===\n\n";
    $count = 0;

    $columnExists = function (string $table, string $column) use ($pdo, $name): bool {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
        $stmt->execute([$name, $table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    };

    $addColumn = function (string $table, string $column, string $definition) use ($pdo, $columnExists, &$count): void {
        if (!$columnExists($table, $column)) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$definition}");
            echo "  [ADDED] {$table}.{$column}\n";
            $count++;
        } else {
            echo "  [OK]    {$table}.{$column} already exists\n";
        }
    };

    echo "--- courses ---\n";
    $addColumn('courses', 'video_type', "video_type ENUM('upload','youtube','aparat') DEFAULT 'upload' AFTER video_url");

    echo "\n--- settings (captcha defaults) ---\n";
    $captchaDefaults = [
        'captcha_enabled_admin' => '1',
        'captcha_enabled_booking' => '1',
        'captcha_enabled_newsletter' => '1',
        'captcha_difficulty' => 'medium',
        'captcha_question_1' => '5 + 3',
        'captcha_question_2' => '12 - 7',
        'captcha_question_3' => '9 + 4',
        'captcha_question_4' => '15 - 8',
        'captcha_question_5' => '6 + 11',
        'captcha_question_6' => '20 - 9',
        'captcha_question_7' => '3 + 14',
        'captcha_question_8' => '18 - 6',
        'captcha_question_9' => '7 + 8',
        'captcha_question_10' => '25 - 10',
    ];

    foreach ($captchaDefaults as $key => $value) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$key, $value]);
            echo "  [ADDED] {$key} = {$value}\n";
            $count++;
        } else {
            echo "  [OK]    {$key} already exists\n";
        }
    }

    echo "\n=== Migration v2 complete! {$count} changes applied. ===\n";

} catch (PDOException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
