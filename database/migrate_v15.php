<?php

/**
 * Migration v15: SMS Notification Templates
 * Adds: slug column + unique index to sms_templates
 * Seeds: notification templates (booking_new, order_receipt, order_new, order_status, booking_status)
 * Removes: 'verify' sms_type, seeded verify template, orphaned sms_sender setting
 * Run: php database/migrate_v15.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

const TABLE_DEFINITIONS = [
    'sms_logs' => "CREATE TABLE IF NOT EXISTS sms_logs (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'sms_templates' => "CREATE TABLE IF NOT EXISTS sms_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        body TEXT NOT NULL,
        variables JSON DEFAULT NULL,
        sms_type ENUM('verify', 'bulk', 'notification') NOT NULL DEFAULT 'bulk',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'sms_credits' => "CREATE TABLE IF NOT EXISTS sms_credits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        amount INT NOT NULL,
        cost DECIMAL(15,0) DEFAULT 0,
        description VARCHAR(255) DEFAULT '',
        payment_id VARCHAR(255) DEFAULT NULL,
        status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'verification_codes' => "CREATE TABLE IF NOT EXISTS verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL,
        code VARCHAR(10) NOT NULL,
        purpose ENUM('register', 'login', 'reset_password') NOT NULL DEFAULT 'register',
        expires_at DATETIME NOT NULL,
        used TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_phone_purpose (phone, purpose),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

echo "Running migration v15: SMS notification templates...\n";

try {
    foreach (['sms_logs', 'sms_templates', 'sms_credits', 'verification_codes'] as $table) {
        $exists = Database::fetch("SHOW TABLES LIKE '{$table}'");
        if (!$exists) {
            Database::query(TABLE_DEFINITIONS[$table]);
            echo "  + Created missing table '{$table}'\n";
        }
    }

    $usersExist = (bool) Database::fetch("SHOW TABLES LIKE 'users'");
    if ($usersExist) {
        $col = Database::fetch("SHOW COLUMNS FROM users LIKE 'phone_verified'");
        if (!$col) {
            Database::query("ALTER TABLE users ADD COLUMN phone_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
            echo "  + Added phone_verified column to users\n";
        }
    }

    $columns = Database::fetchAll("SHOW COLUMNS FROM sms_templates");
    $existing = array_column($columns, 'Field');

    if (in_array('slug', $existing, true)) {
        echo "  + slug column already exists, skipping\n";
    } else {
        Database::query("ALTER TABLE sms_templates ADD COLUMN slug VARCHAR(120) DEFAULT NULL AFTER name");
        echo "  + Added slug column to sms_templates\n";
    }

    $updates = [
        ['name' => 'خوشامدگویی', 'slug' => 'welcome', 'body' => 'خوش آمدید {Name} عزیز. حساب شما فعال شد.'],
        ['name' => 'یادآوری نوبت', 'slug' => 'booking_reminder', 'body' => 'یادآوری: نوبت شما فردا ساعت {Time} است.'],
    ];
    foreach ($updates as $u) {
        Database::query(
            "UPDATE sms_templates SET slug = COALESCE(slug, ?), body = COALESCE(body, ?) WHERE name = ? AND slug IS NULL",
            [$u['slug'], $u['body'], $u['name']]
        );
        echo "  + Backfilled slug '{$u['slug']}' for template '{$u['name']}'\n";
    }

    Database::query("UPDATE sms_templates SET slug = CONCAT('tpl-', id) WHERE slug IS NULL");
    echo "  + Backfilled remaining slugs\n";

    Database::query("DELETE FROM sms_templates WHERE sms_type = 'verify'");
    echo "  + Removed verify templates\n";

    $enumCol = Database::fetch("SHOW COLUMNS FROM sms_templates LIKE 'sms_type'");
    $enumType = strtolower((string) ($enumCol['Type'] ?? ''));
    if (strpos($enumType, "'bulk'") !== false && strpos($enumType, "'verify'") === false) {
        echo "  + sms_type enum already excludes verify, skipping\n";
    } else {
        Database::query("ALTER TABLE sms_templates MODIFY sms_type ENUM('bulk', 'notification') NOT NULL DEFAULT 'bulk'");
        echo "  + Removed 'verify' from sms_type enum\n";
    }

    Database::query("DELETE FROM settings WHERE setting_key = 'sms_sender'");
    echo "  + Removed orphaned sms_sender setting\n";

    $indexes = Database::fetchAll("SHOW INDEX FROM sms_templates");
    $indexNames = array_column($indexes, 'Key_name');
    if (in_array('uq_sms_templates_slug', $indexNames, true)) {
        echo "  + unique slug index already exists, skipping\n";
    } else {
        Database::query("ALTER TABLE sms_templates ADD UNIQUE KEY uq_sms_templates_slug (slug)");
        echo "  + Added unique index on sms_templates.slug\n";
    }

    $templates = [
        [
            'name' => 'نوبت جدید',
            'slug' => 'booking_new',
            'body' => 'نوبت جدید ثبت شد: {Service} | تاریخ: {Date} | ساعت: {Time} | مشتری: {Name} ({Phone})',
            'variables' => '["Service","Date","Time","Name","Phone"]',
        ],
        [
            'name' => 'رسید سفارش',
            'slug' => 'order_receipt',
            'body' => 'سفارش {Code} شما با موفقیت ثبت و پرداخت شد. مبلغ: {Total}',
            'variables' => '["Code","Total"]',
        ],
        [
            'name' => 'سفارش جدید',
            'slug' => 'order_new',
            'body' => 'سفارش جدید {Code} به مبلغ {Total} توسط {Name} ثبت شد.',
            'variables' => '["Code","Total","Name"]',
        ],
        [
            'name' => 'وضعیت سفارش',
            'slug' => 'order_status',
            'body' => 'وضعیت سفارش {Code} شما: {Status}',
            'variables' => '["Code","Status"]',
        ],
        [
            'name' => 'وضعیت نوبت',
            'slug' => 'booking_status',
            'body' => 'وضعیت نوبت شما در تاریخ {Date} ساعت {Time}: {Status}',
            'variables' => '["Date","Time","Status"]',
        ],
    ];

    foreach ($templates as $tpl) {
        $exists = Database::fetch("SELECT id FROM sms_templates WHERE slug = ?", [$tpl['slug']]);
        if ($exists) {
            Database::query(
                "UPDATE sms_templates SET name = ?, body = ?, variables = ?, sms_type = 'notification', is_active = 1 WHERE slug = ?",
                [$tpl['name'], $tpl['body'], $tpl['variables'], $tpl['slug']]
            );
            echo "  + Updated template '{$tpl['slug']}'\n";
            continue;
        }
        Database::insert('sms_templates', [
            'name' => $tpl['name'],
            'slug' => $tpl['slug'],
            'body' => $tpl['body'],
            'variables' => $tpl['variables'],
            'sms_type' => 'notification',
            'is_active' => 1,
        ]);
        echo "  + Created template '{$tpl['slug']}'\n";
    }

    echo "Migration v15 completed successfully.\n";
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
