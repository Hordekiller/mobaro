<?php
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v4: Coupons, Contact, Payment...\n";

// Coupons table
Database::query("CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(15,0) NOT NULL DEFAULT 0,
    min_order DECIMAL(15,0) DEFAULT 0,
    max_uses INT DEFAULT 0,
    used_count INT DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  + coupons table\n";

// Insert default coupons
$existing = Database::fetch("SELECT id FROM coupons WHERE code = 'MOBARO20'");
if (!$existing) {
    Database::insert('coupons', [
        'code' => 'MOBARO20',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'min_order' => 0,
        'max_uses' => 100,
        'expires_at' => date('Y-m-d', strtotime('+1 year')),
    ]);
    echo "  + coupon MOBARO20 (20%)\n";
}
$existing = Database::fetch("SELECT id FROM coupons WHERE code = 'WELCOME15'");
if (!$existing) {
    Database::insert('coupons', [
        'code' => 'WELCOME15',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'min_order' => 50000,
        'max_uses' => 100,
        'expires_at' => date('Y-m-d', strtotime('+1 year')),
    ]);
    echo "  + coupon WELCOME15 (15%)\n";
}

// Contact messages table
Database::query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL DEFAULT '',
    email VARCHAR(100) NOT NULL DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    subject VARCHAR(200) DEFAULT '',
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  + contact_messages table\n";

// Orders - add coupon_code, coupon_discount fields
$cols = Database::fetchAll("SHOW COLUMNS FROM orders");
$colNames = array_column($cols, 'Field');
if (!in_array('coupon_code', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL AFTER discount");
    echo "  + orders.coupon_code\n";
}
if (!in_array('coupon_discount', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN coupon_discount DECIMAL(15,0) DEFAULT 0 AFTER coupon_code");
    echo "  + orders.coupon_discount\n";
}
if (!in_array('payment_status', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status");
    echo "  + orders.payment_status\n";
}
if (!in_array('payment_method', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER payment_status");
    echo "  + orders.payment_method\n";
}

// Wallet topup payment tracking
$cols = Database::fetchAll("SHOW COLUMNS FROM transactions");
$colNames = array_column($cols, 'Field');
if (!in_array('payment_id', $colNames)) {
    Database::query("ALTER TABLE transactions ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL AFTER description");
    echo "  + transactions.payment_id\n";
}
if (!in_array('payment_status', $colNames)) {
    Database::query("ALTER TABLE transactions ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER payment_id");
    echo "  + transactions.payment_status\n";
}

echo "\nMigration v4 complete!\n";
