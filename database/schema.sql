-- Mobaro Beauty Salon - Database Schema
-- MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS mobaro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mobaro;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL DEFAULT '',
    family VARCHAR(100) NOT NULL DEFAULT '',
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT '',
    avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    level VARCHAR(50) DEFAULT 'bronze',
    points INT NOT NULL DEFAULT 0,
    wallet DECIMAL(15,0) NOT NULL DEFAULT 0,
    google_id VARCHAR(255) DEFAULT NULL,
    google_avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Artists (must be before services due to FK)
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    specialty VARCHAR(200) DEFAULT '',
    bio TEXT DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    instagram VARCHAR(255) DEFAULT '#',
    working_hours VARCHAR(100) DEFAULT '۹ صبح - ۸ شب',
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    price DECIMAL(15,0) NOT NULL DEFAULT 0,
    duration VARCHAR(50) DEFAULT '',
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 4.9,
    artist_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Artist-Service Relationship
CREATE TABLE IF NOT EXISTS artist_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY uk_artist_service (artist_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hair Lengths
CREATE TABLE IF NOT EXISTS hair_lengths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    min_cm INT DEFAULT 0,
    max_cm INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT DEFAULT NULL,
    artist_id INT DEFAULT NULL,
    hair_length_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time VARCHAR(20) NOT NULL,
    price DECIMAL(15,0) DEFAULT NULL,
    status ENUM('confirmed', 'pending', 'done', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE SET NULL,
    FOREIGN KEY (hair_length_id) REFERENCES hair_lengths(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_date (appointment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Hair Prices
CREATE TABLE IF NOT EXISTS service_hair_prices (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    price DECIMAL(15,0) NOT NULL DEFAULT 0,
    old_price BIGINT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    stock INT NOT NULL DEFAULT 10,
    brand VARCHAR(255) DEFAULT '',
    is_new TINYINT(1) DEFAULT 0,
    is_sale TINYINT(1) DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 4.5,
    is_active TINYINT(1) DEFAULT 1,
    video_url VARCHAR(500) DEFAULT NULL,
    video_type ENUM('upload', 'youtube', 'aparat') DEFAULT 'upload',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Gallery
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(15,0) NOT NULL DEFAULT 0,
    discount DECIMAL(15,0) DEFAULT 0,
    postal_code VARCHAR(20) DEFAULT '',
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    tracking_code VARCHAR(50) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_id VARCHAR(255) DEFAULT NULL,
    coupon_code VARCHAR(100) DEFAULT NULL,
    coupon_discount DECIMAL(15,0) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(15,0) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    teacher VARCHAR(100) DEFAULT '',
    type VARCHAR(50) DEFAULT '',
    image VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT '',
    description TEXT DEFAULT NULL,
    duration VARCHAR(20) DEFAULT '',
    price INT DEFAULT 0,
    old_price INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0.0,
    students INT DEFAULT 0,
    level VARCHAR(50) DEFAULT 'همه سطوح',
    is_free TINYINT(1) DEFAULT 0,
    slug VARCHAR(255) DEFAULT NULL UNIQUE,
    curriculum TEXT DEFAULT NULL,
    audience TEXT DEFAULT NULL,
    faqs TEXT DEFAULT NULL,
    reviews TEXT DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    video_type ENUM('upload', 'youtube', 'aparat') DEFAULT 'upload',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Lessons Completed
CREATE TABLE IF NOT EXISTS course_lessons_completed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    module_index INT NOT NULL DEFAULT 0,
    lesson_index INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user_course (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Enrollments
CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress INT NOT NULL DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_course (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions (Wallet / Points)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('wallet_deposit', 'wallet_withdraw', 'points_earn', 'points_spend') NOT NULL,
    amount DECIMAL(15,0) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    payment_id VARCHAR(255) DEFAULT NULL,
    payment_status VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    role VARCHAR(200) DEFAULT '',
    text TEXT NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 5.0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlist
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts (Rate Limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) DEFAULT 0,
    INDEX idx_identifier (identifier),
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorite Models
CREATE TABLE IF NOT EXISTS favorite_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    model_title VARCHAR(200) NOT NULL,
    model_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Addresses
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) DEFAULT 'خانه',
    address TEXT NOT NULL,
    city VARCHAR(100) DEFAULT 'تهران',
    zip_code VARCHAR(20) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Comments
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    name VARCHAR(200) NOT NULL DEFAULT '',
    email VARCHAR(200) DEFAULT '',
    text TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    likes INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_post (post_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Categories
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Brands
CREATE TABLE IF NOT EXISTS product_brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hair Models Gallery
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Educational Videos
CREATE TABLE IF NOT EXISTS tutorials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    image VARCHAR(255) DEFAULT NULL,
    duration VARCHAR(20) DEFAULT '',
    views INT DEFAULT 0,
    video_url VARCHAR(255) DEFAULT '',
    video_type ENUM('upload', 'youtube', 'aparat') DEFAULT 'upload',
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media Gallery (centralized media management)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default hair lengths
INSERT IGNORE INTO hair_lengths (id, title, min_cm, max_cm, sort_order, is_active) VALUES
(1, 'کوتاه (زیر شانه)', 0, 30, 1, 1),
(2, 'متوسط (تا شانه)', 30, 50, 2, 1),
(3, 'بلند (زیر کمر)', 50, 80, 3, 1),
(4, 'خیلی بلند (بالای کمر)', 80, 150, 4, 1);
