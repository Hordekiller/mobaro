<?php
/**
 * Migration v12: SMS Management Tables
 * Creates: sms_logs, sms_templates, sms_credits
 * Run: php database/migrate_v12.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v12: SMS management tables...\n";

try {
    $exists = Database::fetch("SHOW TABLES LIKE 'sms_logs'");
    if (!$exists) {
        Database::query("CREATE TABLE sms_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            template_id INT DEFAULT NULL,
            type ENUM('verify', 'bulk', 'notification') NOT NULL DEFAULT 'bulk',
            status ENUM('sent', 'delivered', 'failed') NOT NULL DEFAULT 'sent',
            credits DECIMAL(10,2) DEFAULT 0,
            api_message_id VARCHAR(100) DEFAULT NULL,
            api_response TEXT DEFAULT NULL,
            sent_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone (phone),
            INDEX idx_status (status),
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created sms_logs table\n";
    } else {
        echo "  + sms_logs table already exists, skipping\n";
    }

    $exists = Database::fetch("SHOW TABLES LIKE 'sms_templates'");
    if (!$exists) {
        Database::query("CREATE TABLE sms_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            body TEXT NOT NULL,
            variables JSON DEFAULT NULL,
            sms_type ENUM('verify', 'bulk', 'notification') NOT NULL DEFAULT 'bulk',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created sms_templates table\n";
    } else {
        echo "  + sms_templates table already exists, skipping\n";
    }

    $exists = Database::fetch("SHOW TABLES LIKE 'sms_credits'");
    if (!$exists) {
        Database::query("CREATE TABLE sms_credits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            amount INT NOT NULL,
            cost DECIMAL(15,0) DEFAULT 0,
            description VARCHAR(255) DEFAULT '',
            payment_id VARCHAR(255) DEFAULT NULL,
            status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created sms_credits table\n";
    } else {
        echo "  + sms_credits table already exists, skipping\n";
    }

    $col = Database::fetch("SHOW COLUMNS FROM users LIKE 'phone_verified'");
    if (!$col) {
        Database::query("ALTER TABLE users ADD COLUMN phone_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        echo "  + Added phone_verified column to users\n";
    } else {
        echo "  + phone_verified column already exists, skipping\n";
    }

    $exists = Database::fetch("SHOW TABLES LIKE 'verification_codes'");
    if (!$exists) {
        Database::query("CREATE TABLE verification_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            code VARCHAR(10) NOT NULL,
            purpose ENUM('register', 'login', 'reset_password') NOT NULL DEFAULT 'register',
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone_purpose (phone, purpose),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  + Created verification_codes table\n";
    } else {
        echo "  + verification_codes table already exists, skipping\n";
    }

    $templates = [
        ['تایید شماره تلفن', 'کد تایید شما: #Code#', 'verify', '["Code"]'],
        ['خوشامدگویی', 'خوش آمدید #Name# عزیز. حساب شما فعال شد.', 'notification', '["Name"]'],
        ['یادآوری نوبت', 'یادآوری: نوبت شما فردا ساعت #Time# است.', 'notification', '["Time"]'],
    ];

    foreach ($templates as $tpl) {
        $exists = Database::fetch("SELECT id FROM sms_templates WHERE name = ?", [$tpl[0]]);
        if (!$exists) {
            Database::insert('sms_templates', [
                'name' => $tpl[0],
                'body' => $tpl[1],
                'sms_type' => $tpl[2],
                'variables' => $tpl[3],
                'is_active' => 1,
            ]);
            echo "  + Created template: {$tpl[0]}\n";
        }
    }

    echo "\nMigration v12 complete!\n";
} catch (\Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
