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

    $captchas = [
        'captcha_1'  => '5+3',
        'captcha_2'  => '12-7',
        'captcha_3'  => '9+4',
        'captcha_4'  => '15-8',
        'captcha_5'  => '6+11',
        'captcha_6'  => '20-9',
        'captcha_7'  => '3+14',
        'captcha_8'  => '18-6',
        'captcha_9'  => '7+8',
        'captcha_10' => '25-10',
    ];

    echo "=== Captcha Migration ===\n\n";
    $count = 0;

    foreach ($captchas as $key => $value) {
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

    echo "\n=== Captcha migration complete! {$count} new captchas added. ===\n";

} catch (PDOException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
