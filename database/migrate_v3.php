<?php
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

echo "Running migration v3: Adding missing columns...\n";

// Users - add google_id, google_avatar, is_active
$cols = Database::fetchAll("SHOW COLUMNS FROM users");
$colNames = array_column($cols, 'Field');

if (!in_array('google_id', $colNames)) {
    Database::query("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER wallet");
    echo "  + users.google_id\n";
}
if (!in_array('google_avatar', $colNames)) {
    Database::query("ALTER TABLE users ADD COLUMN google_avatar VARCHAR(255) DEFAULT NULL AFTER google_id");
    echo "  + users.google_avatar\n";
}
if (!in_array('is_active', $colNames)) {
    Database::query("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER google_avatar");
    echo "  + users.is_active\n";
}
// Fix level default
Database::query("ALTER TABLE users MODIFY level VARCHAR(50) DEFAULT 'bronze'");
echo "  ~ users.level default -> bronze\n";

// Appointments - add price
$cols = Database::fetchAll("SHOW COLUMNS FROM appointments");
$colNames = array_column($cols, 'Field');
if (!in_array('price', $colNames)) {
    Database::query("ALTER TABLE appointments ADD COLUMN price DECIMAL(15,0) DEFAULT NULL AFTER appointment_time");
    echo "  + appointments.price\n";
}

// Orders - add discount, postal_code
$cols = Database::fetchAll("SHOW COLUMNS FROM orders");
$colNames = array_column($cols, 'Field');
if (!in_array('discount', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN discount DECIMAL(15,0) DEFAULT 0 AFTER total");
    echo "  + orders.discount\n";
}
if (!in_array('postal_code', $colNames)) {
    Database::query("ALTER TABLE orders ADD COLUMN postal_code VARCHAR(20) DEFAULT '' AFTER discount");
    echo "  + orders.postal_code\n";
}

// Addresses - add phone
$cols = Database::fetchAll("SHOW COLUMNS FROM addresses");
$colNames = array_column($cols, 'Field');
if (!in_array('phone', $colNames)) {
    Database::query("ALTER TABLE addresses ADD COLUMN phone VARCHAR(20) DEFAULT '' AFTER zip_code");
    echo "  + addresses.phone\n";
}

// Course Enrollments - add created_at alias
$cols = Database::fetchAll("SHOW COLUMNS FROM course_enrollments");
$colNames = array_column($cols, 'Field');
if (!in_array('created_at', $colNames)) {
    Database::query("ALTER TABLE course_enrollments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER enrolled_at");
    echo "  + course_enrollments.created_at\n";
}

// Login attempts - add ip_address, success
$cols = Database::fetchAll("SHOW COLUMNS FROM login_attempts");
$colNames = array_column($cols, 'Field');
if (!in_array('ip_address', $colNames)) {
    Database::query("ALTER TABLE login_attempts ADD COLUMN ip_address VARCHAR(45) NOT NULL DEFAULT '' AFTER identifier");
    echo "  + login_attempts.ip_address\n";
}
if (!in_array('success', $colNames)) {
    Database::query("ALTER TABLE login_attempts ADD COLUMN success TINYINT(1) DEFAULT 0 AFTER attempted_at");
    echo "  + login_attempts.success\n";
}

echo "\nMigration v3 complete!\n";
